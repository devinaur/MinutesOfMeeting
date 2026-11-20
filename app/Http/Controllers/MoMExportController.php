<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\MoMExcelExportService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Symfony\Component\HttpFoundation\StreamedResponse;

class MoMExportController extends Controller
{
    /**
     * Export data JSON MoM ke file Excel (.xlsx) resmi PT Yusen Logistics.
     */
    public function export(Request $request, MoMExcelExportService $exportService): StreamedResponse
    {
        $payload = $request->input('mom_data');

        if (is_string($payload)) {
            $data = json_decode($payload, true) ?: [];
        } else {
            $data = (array) ($payload ?? []);
        }

        if (empty($data)) {
            abort(400, 'Data MoM tidak boleh kosong.');
        }

        $dept = (string) ($data['department'] ?? $request->input('department', 'business_unit'));
        $spreadsheet = $exportService->generate($data, $dept);

        // Buat nama file rapi, e.g. YIID_MoM_BUSINESSUNIT_2026-11-04.xlsx
        $dateStr = $data['meeting_info']['date'] ?? Carbon::now()->format('Y-m-d');
        $cleanDate = preg_replace('/[^A-Za-z0-9_-]/', '', (string) $dateStr);
        $deptCode = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $dept));
        $fileName = "YIID_MoM_{$deptCode}_{$cleanDate}.xlsx";

        return new StreamedResponse(
            function () use ($spreadsheet) {
                $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
                $writer->setPreCalculateFormulas(true);
                $writer->save('php://output');
            },
            200,
            [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'Content-Disposition' => "attachment; filename=\"{$fileName}\"",
                'Cache-Control' => 'max-age=0',
            ]
        );
    }
}
