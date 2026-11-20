<?php

declare(strict_types=1);

namespace App\Services\MoM;

use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class DefaultMoMExporter implements MoMExporterInterface
{
    /**
     * Generate file spreadsheet MoM terisi dari data JSON terstruktur
     * menggunakan file template yang diberikan.
     *
     * @param array<string, mixed> $data
     * @param string $templatePath
     */
    public function export(array $data, string $templatePath): Spreadsheet
    {
        if (! file_exists($templatePath)) {
            throw new \RuntimeException("Template MoM file not found at: {$templatePath}");
        }

        $reader = IOFactory::createReader('Xlsx');
        $spreadsheet = $reader->load($templatePath);
        $sheet = $spreadsheet->getActiveSheet();

        // 1. Bersihkan coretan notes di kolom samping (kolom O) jika ada
        for ($r = 1; $r <= 110; $r++) {
            $sheet->setCellValue("O{$r}", null);
        }

        // 2. Isi Metadata Meeting (Header)
        $meetingInfo = $data['meeting_info'] ?? [];
        $this->fillMeetingInfo($sheet, $meetingInfo);

        // 3. Isi Daftar Peserta (Attendees)
        $attendees = $data['attendees'] ?? [];
        $this->fillAttendees($sheet, $attendees);

        // 4. Isi Agenda & Pembahasan
        $agendaItems = $data['agenda_items'] ?? [];
        $this->fillAgendaItems($sheet, $agendaItems);

        // 5. Update Sheet Title sesuai tanggal rapat
        $dateStr = $meetingInfo['date'] ?? null;
        if ($dateStr) {
            try {
                $sheetTitle = Carbon::parse($dateStr)->format('d M Y');
                $sheet->setTitle(substr($sheetTitle, 0, 31));
            } catch (\Throwable $e) {
                // Ignore jika parsing tanggal gagal
            }
        }

        return $spreadsheet;
    }

    /**
     * Isi informasi waktu, tempat, dan chairman.
     */
    protected function fillMeetingInfo(Worksheet $sheet, array $info): void
    {
        // Tanggal di sel D5
        if (! empty($info['date'])) {
            try {
                $formattedDate = Carbon::parse($info['date'])->format('d-M-Y');
                $sheet->setCellValue('D5', $formattedDate);
            } catch (\Throwable $e) {
                $sheet->setCellValue('D5', (string) $info['date']);
            }
        }

        // Jam di sel J5
        if (! empty($info['time'])) {
            $sheet->setCellValue('J5', (string) $info['time']);
        }

        // Lokasi di sel D6
        if (! empty($info['location'])) {
            $sheet->setCellValue('D6', (string) $info['location']);
        }

        // Chairman di sel D13
        if (! empty($info['chairman'])) {
            $sheet->setCellValue('D13', (string) $info['chairman']);
        }
    }

    /**
     * Isi daftar peserta meeting pada baris 8 sampai 12.
     */
    protected function fillAttendees(Worksheet $sheet, array $attendees): void
    {
        $startRow = 8;
        $maxDefaultRows = 5; // Template menyediakan row 8 - 12 (5 baris)

        // Kosongkan baris template default terlebih dahulu
        for ($i = 0; $i < $maxDefaultRows; $i++) {
            $currentRow = $startRow + $i;
            $sheet->setCellValue("D{$currentRow}", '');
            $sheet->setCellValue("G{$currentRow}", '');
            $sheet->setCellValue("J{$currentRow}", '');
        }

        // Isi data peserta
        foreach ($attendees as $index => $attendee) {
            $currentRow = $startRow + $index;

            // Jika peserta melebihi 5, sisipkan baris baru sebelum row chairman
            if ($index >= $maxDefaultRows) {
                $sheet->insertNewRowBefore($currentRow, 1);
                $sheet->mergeCells("D{$currentRow}:F{$currentRow}");
                $sheet->mergeCells("G{$currentRow}:I{$currentRow}");
                $sheet->mergeCells("J{$currentRow}:L{$currentRow}");
                
                $this->applyAttendeeRowStyle($sheet, $currentRow);
            }

            $name = $attendee['name'] ?? '';
            $company = $attendee['company'] ?? 'PT Yusen Logistics Interlink Indonesia';
            $division = $attendee['division'] ?? '';

            $sheet->setCellValue("D{$currentRow}", $name);
            $sheet->setCellValue("G{$currentRow}", $company);
            $sheet->setCellValue("J{$currentRow}", $division);

            $this->applyAttendeeRowStyle($sheet, $currentRow);
        }
    }

    /**
     * Isi daftar agenda, pembahasan, dan action items mulai dari row 16.
     */
    protected function fillAgendaItems(Worksheet $sheet, array $agendaItems): void
    {
        $highestRow = $sheet->getHighestRow();
        $agendaStartRow = 16;

        // Cari baris tepat setelah sel yang bertuliskan "Agenda"
        for ($r = 14; $r <= $highestRow; $r++) {
            $val = (string) $sheet->getCell("C{$r}")->getValue();
            if (stripos($val, 'agenda') !== false) {
                $agendaStartRow = $r + 1;
                break;
            }
        }

        // Bersihkan sisa baris dan border dari template lama di bawah header agenda
        for ($r = $agendaStartRow; $r <= max($agendaStartRow + 60, $highestRow); $r++) {
            $sheet->setCellValue("B{$r}", null);
            $sheet->setCellValue("C{$r}", null);
            foreach ($sheet->getMergeCells() as $mergeRange) {
                if (preg_match('/^[B-L]' . $r . ':[B-L]' . $r . '$/', $mergeRange)) {
                    $sheet->unmergeCells($mergeRange);
                }
            }
            $sheet->getStyle("B{$r}:L{$r}")->getBorders()->getOutline()->setBorderStyle(Border::BORDER_NONE);
            $sheet->getStyle("B{$r}:L{$r}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_NONE);
            $sheet->getRowDimension($r)->setRowHeight(-1);
        }

        $currentRow = $agendaStartRow;

        foreach ($agendaItems as $item) {
            $no = $item['no'] ?? ($currentRow - $agendaStartRow + 1);
            $topic = trim((string) ($item['topic'] ?? ''));
            $discussion = trim((string) ($item['discussion'] ?? ''));
            $actionItems = $item['action_items'] ?? [];

            // Susun teks konten yang rapi
            $contentLines = [];
            if (! empty($topic)) {
                $contentLines[] = "• " . strtoupper($topic);
            }
            if (! empty($discussion)) {
                $contentLines[] = "";
                $contentLines[] = $discussion;
            }

            if (! empty($actionItems)) {
                $contentLines[] = "";
                $contentLines[] = "Action Items / Tindak Lanjut:";
                foreach ($actionItems as $act) {
                    $task = $act['task'] ?? '';
                    $pic = $act['pic'] ?? '';
                    $deadline = $act['deadline'] ?? '';

                    $line = "  - {$task}";
                    $meta = [];
                    if ($pic !== '' && strtolower($pic) !== 'null' && strtolower($pic) !== 'none' && $pic !== '-') {
                        $meta[] = "PIC: {$pic}";
                    }
                    if ($deadline !== '' && strtolower($deadline) !== 'null' && strtolower($deadline) !== 'none' && $deadline !== '-') {
                        $meta[] = "Due: {$deadline}";
                    }
                    if (! empty($meta)) {
                        $line .= ' [' . implode(' | ', $meta) . ']';
                    }
                    $contentLines[] = $line;
                }
            }

            $fullContent = implode("\n", $contentLines);

            // Merge kolom C sampai L untuk teks agenda
            $mergeRange = "C{$currentRow}:L{$currentRow}";
            $sheet->mergeCells($mergeRange);

            // Set nomor dan isi
            $sheet->setCellValue("B{$currentRow}", $no);
            $sheet->setCellValue("C{$currentRow}", $fullContent);

            // Alignment & Text Wrap
            $sheet->getStyle("B{$currentRow}")->getAlignment()
                ->setHorizontal(Alignment::HORIZONTAL_CENTER)
                ->setVertical(Alignment::VERTICAL_TOP);

            $sheet->getStyle("C{$currentRow}")->getAlignment()
                ->setHorizontal(Alignment::HORIZONTAL_LEFT)
                ->setVertical(Alignment::VERTICAL_TOP)
                ->setWrapText(true);

            // Border
            $this->applyBorder($sheet, "B{$currentRow}");
            $this->applyBorderRange($sheet, $currentRow, 'C', 'L');

            // Kalkulasi tinggi baris manual
            $totalVisualLines = 0;
            foreach ($contentLines as $line) {
                if (trim($line) === '') {
                    $totalVisualLines += 0.6;
                } else {
                    $totalVisualLines += max(1, (int) ceil(mb_strlen($line) / 90));
                }
            }

            $calculatedHeight = max(35.0, ($totalVisualLines * 18.0) + 12.0);
            $sheet->getRowDimension($currentRow)->setRowHeight($calculatedHeight);

            $currentRow++;
        }
    }

    protected function applyBorder(Worksheet $sheet, string $cellCoordinate): void
    {
        $sheet->getStyle($cellCoordinate)->getBorders()->getOutline()->setBorderStyle(Border::BORDER_THIN);
    }

    protected function applyBorderRange(Worksheet $sheet, int $row, string $startCol, string $endCol): void
    {
        $range = "{$startCol}{$row}:{$endCol}{$row}";
        $sheet->getStyle($range)->getBorders()->getOutline()->setBorderStyle(Border::BORDER_THIN);
    }

    protected function applyAttendeeRowStyle(Worksheet $sheet, int $row): void
    {
        foreach (['D', 'G', 'J'] as $col) {
            $sheet->getStyle("{$col}{$row}")->getAlignment()
                ->setVertical(Alignment::VERTICAL_CENTER)
                ->setWrapText(true);
        }

        $sheet->getStyle("D{$row}:F{$row}")->getBorders()->getOutline()->setBorderStyle(Border::BORDER_THIN);
        $sheet->getStyle("G{$row}:I{$row}")->getBorders()->getOutline()->setBorderStyle(Border::BORDER_THIN);
        $sheet->getStyle("J{$row}:L{$row}")->getBorders()->getOutline()->setBorderStyle(Border::BORDER_THIN);
    }
}
