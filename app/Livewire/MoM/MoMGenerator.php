<?php

declare(strict_types=1);

namespace App\Livewire\MoM;

use App\Services\MoMExcelExportService;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Http;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Symfony\Component\HttpFoundation\StreamedResponse;

#[Layout('layouts.app')]
#[Title('Minutes of Meeting (MoM) Generator - PT Yusen Logistics')]
class MoMGenerator extends Component
{
    use WithFileUploads;

    public string $lang = 'id';
    public string $department = 'business_unit';
    public string $templateVariant = 'standard';
    public string $inputMode = 'text';
    public string $rawText = '';
    public $audioFile;

    public bool $isProcessing = false;
    public ?string $errorMessage = null;
    public ?string $successMessage = null;

    public array $meetingInfo = [
        'date' => '',
        'time' => '',
        'location' => '',
        'chairman' => '',
    ];

    public array $attendees = [];
    public array $agendaItems = [];
    public array $itDetails = [
        'title' => '',
        'sub_topics' => [],
        'conclusion' => '',
    ];
    public bool $hasGenerated = false;

    public function mount(): void
    {
        $this->meetingInfo['date'] = Carbon::now()->format('Y-m-d');
        $this->meetingInfo['time'] = Carbon::now()->format('H:i') . ' WIB';
        $this->meetingInfo['location'] = 'Meeting Room 1';
        $this->meetingInfo['chairman'] = '';

        $templates = $this->availableTemplates;
        $this->templateVariant = array_key_first($templates) ?? 'standard';
    }

    public function updatedDepartment(string $value): void
    {
        $templates = app(MoMExcelExportService::class)->getDepartmentTemplates($value);
        $this->templateVariant = array_key_first($templates) ?? 'standard';
    }

    public function toggleLang(): void
    {
        $this->lang = ($this->lang === 'id' ? 'en' : 'id');
        $this->errorMessage = null;
        $this->successMessage = null;
    }

    public function resetForm(): void
    {
        $this->rawText = '';
        $this->audioFile = null;
        $this->attendees = [];
        $this->agendaItems = [];
        $this->itDetails = [
            'title' => '',
            'sub_topics' => [],
            'conclusion' => '',
        ];
        $this->hasGenerated = false;
        $this->errorMessage = null;
        $this->successMessage = null;
        $this->mount();
    }

    public function fillSampleText(): void
    {
        $this->inputMode = 'text';
        if ($this->lang === 'en') {
            $this->rawText = "Meeting Nov 4, 2026 at 2:00 PM in Room 1.\nAttendees: Devina (IT), Budi (Operation), Ryan (DHL).\nChairman: Devina.\n\nTopics:\n1. Excessive liquid nitrogen (N2 Liquid) consumption review.\n2. Courier logistics management fee & issues with drivers not picking up physical Proof of Delivery (POD).\n\nAction items:\n- Budi to review inventory and establish driver SOP before Nov 10\n- Devina to coordinate invoice reconciliation with finance ASAP";
        } else {
            $this->rawText = "meeting tgl 4 nov 2026 jam 14.00 di room meeting 1.\nhadir: devina (IT), budi (operation), ryan (DHL). chairmannya devina.\n\nbahasan:\n1. evaluasi penggunaan nitrogen cair (n2 liquid) boros banget bulan ini.\n2. biaya pengiriman jasa kurir skrg ada management fee, tp pod sering ga diambil driver. nanti laporannya hrs ada pod + invoice.\n\naction items:\n- budi cek stok n2 liquid dan buat sop driver sblm tgl 10 nov\n- devina koordinasi invoice sama finance secepatnya";
        }
        $this->errorMessage = null;
    }

    public function setMode(string $mode): void
    {
        $this->inputMode = $mode;
        $this->errorMessage = null;
    }

    public function processMoM(): void
    {
        $this->errorMessage = null;
        $this->successMessage = null;

        if ($this->inputMode === 'text') {
            $this->validate([
                'rawText' => 'required|min:10',
            ], [
                'rawText.required' => 'Silakan masukkan catatan atau transkrip meeting terlebih dahulu.',
                'rawText.min' => 'Catatan meeting minimal 10 karakter.',
            ]);
        } else {
            $this->validate([
                'audioFile' => 'required|file|max:51200',
            ], [
                'audioFile.required' => 'Silakan pilih berkas audio terlebih dahulu.',
                'audioFile.max' => 'Ukuran berkas audio maksimal 50 MB.',
            ]);
        }

        $this->isProcessing = true;
        $user = auth()->user();
        $userTag = $user ? "[{$user->name} ({$user->username})]" : '[Guest]';
        logger()->info("MoM Generation requested by {$userTag} - Mode: {$this->inputMode}, Dept: {$this->department}, Variant: {$this->templateVariant}");

        try {
            $paClient = app(\App\Services\MoM\PowerAutomateMoMClient::class);
            $driver = config('services.power_automate.driver', 'auto');
            $usePowerAutomate = ($driver === 'power_automate') || ($driver === 'auto' && $paClient->isConfigured($this->inputMode));

            if ($usePowerAutomate) {
                logger()->info("Processing MoM via Power Automate Flow ({$this->inputMode})");
                if ($this->inputMode === 'audio' && $this->audioFile) {
                    $result = $paClient->processAudio(
                        filePath: $this->audioFile->getRealPath(),
                        fileName: $this->audioFile->getClientOriginalName(),
                        mimeType: $this->audioFile->getMimeType() ?: 'audio/mp3',
                        department: $this->department,
                        templateVariant: $this->templateVariant,
                        language: $this->lang
                    );
                } else {
                    $result = $paClient->processText(
                        text: $this->rawText,
                        department: $this->department,
                        templateVariant: $this->templateVariant,
                        language: $this->lang
                    );
                }

                if (! ($result['success'] ?? false)) {
                    $errorStage = $result['error_stage'] ?? 'UNKNOWN';
                    $msg = $result['message'] ?? 'Proses di Power Automate gagal dieksekusi.';

                    logger()->warning("Power Automate returned error at stage [{$errorStage}] for {$userTag}: {$msg}");

                    if ($errorStage === 'TEXT_TO_JSON' && ! empty($result['raw_transcript'])) {
                        $this->inputMode = 'text';
                        $this->rawText = (string) $result['raw_transcript'];
                        $this->errorMessage = ($this->lang === 'id'
                            ? "Rangkuman AI mengalami kendala, namun transkrip rekaman suara berhasil diselamatkan ke tab Catatan Teks. Silakan tinjau dan klik proses ulang."
                            : "AI summarization failed, but the raw audio transcript was rescued into the Notes tab. Please review and re-process.");
                        return;
                    }

                    $this->errorMessage = $msg;
                    return;
                }

                $data = $result['data'] ?? [];
            } else {
                $serviceUrl = rtrim(config('services.mom_ai.url', config('services.ocr.url', 'http://127.0.0.1:8001')), '/') . '/extract-mom';

                $payload = [
                    'language' => $this->lang,
                    'department' => $this->department,
                    'template_variant' => $this->templateVariant,
                ];

                if ($this->inputMode === 'audio' && $this->audioFile) {
                    $filePath = $this->audioFile->getRealPath();
                    $fileName = $this->audioFile->getClientOriginalName();
                    $mimeType = $this->audioFile->getMimeType() ?: 'audio/mp3';

                    $response = Http::timeout(120)
                        ->attach('file', file_get_contents($filePath), $fileName, ['Content-Type' => $mimeType])
                        ->post($serviceUrl, $payload);
                } else {
                    $response = Http::asForm()->timeout(60)->post($serviceUrl, array_merge($payload, [
                        'text' => $this->rawText,
                    ]));
                }

                if (! $response->successful()) {
                    throw new \RuntimeException(
                        'AI Service gagal merespon: ' . ($response->json('detail') ?? $response->body())
                    );
                }

                $result = $response->json();
                $data = $result['data'] ?? [];
            }

            if (isset($data['is_meeting']) && ! $data['is_meeting']) {
                $this->errorMessage = ($this->lang === 'id'
                    ? 'Konten yang dimasukkan tidak terdeteksi sebagai catatan atau rekaman rapat kerja.'
                    : 'The provided content was not recognized as a business meeting or notes.');
                $this->hasGenerated = false;
                return;
            }

            $info = $this->cleanNullValue($data['meeting_info'] ?? []);
            $this->meetingInfo['date'] = $info['date'] ?? $this->meetingInfo['date'];
            $this->meetingInfo['time'] = $info['time'] ?? $this->meetingInfo['time'];
            $this->meetingInfo['location'] = $info['location'] ?? $this->meetingInfo['location'];
            $this->meetingInfo['chairman'] = $info['chairman'] ?? $this->meetingInfo['chairman'];

            $this->attendees = $this->cleanNullValue($data['attendees'] ?? []);
            
            $agenda = $this->cleanNullValue($data['agenda_items'] ?? []);
            $subTopics = $this->cleanNullValue($data['it_details']['sub_topics'] ?? []);

            if ($this->currentFormat === 'docx' && ! empty($subTopics)) {
                $syncedAgenda = [];
                foreach ($subTopics as $idx => $st) {
                    $syncedAgenda[] = [
                        'no' => $idx + 1,
                        'topic' => $st['title'] ?? ($agenda[$idx]['topic'] ?? 'Topik ' . ($idx + 1)),
                        'discussion' => $st['discussion'] ?? ($agenda[$idx]['discussion'] ?? ''),
                        'next_action' => $st['next_action'] ?? ($agenda[$idx]['next_action'] ?? ''),
                        'action_items' => [],
                    ];
                }
                $this->agendaItems = $syncedAgenda;
            } else {
                foreach ($agenda as &$item) {
                    $item['next_action'] = $item['next_action'] ?? '';
                }
                $this->agendaItems = $agenda;
            }

            if (! empty($data['it_details'])) {
                $this->itDetails = $this->cleanNullValue($data['it_details']);
            }

            $this->hasGenerated = true;
            $this->successMessage = ($this->lang === 'id' 
                ? 'Notulensi rapat berhasil dirapikan! Siap diunduh'
                : 'Meeting minutes structured successfully! Ready to download');
        } catch (\Throwable $e) {
            logger()->error("MoM Generation failed for {$userTag}: " . $e->getMessage(), [
                'exception' => $e,
            ]);
            $this->errorMessage = ($this->lang === 'id' 
                ? 'Terjadi kesalahan saat memproses MoM.'
                : 'An error occurred while processing MoM.');
        } finally {
            $this->isProcessing = false;
        }
    }

    public function logout(): void
    {
        $user = auth()->user();
        if ($user) {
            logger()->info("User [{$user->name} ({$user->username})] logged out.");
        }
        \Illuminate\Support\Facades\Auth::logout();
        session()->invalidate();
        session()->regenerateToken();
        $this->redirect(route('login'), navigate: true);
    }

    public function addAttendee(): void
    {
        $this->attendees[] = [
            'name' => '',
            'company' => 'PT Yusen Logistics Interlink Indonesia',
            'division' => '',
        ];
    }

    public function removeAttendee(int $index): void
    {
        unset($this->attendees[$index]);
        $this->attendees = array_values($this->attendees);
    }

    public function addAgendaItem(): void
    {
        $nextNo = count($this->agendaItems) + 1;
        $this->agendaItems[] = [
            'no' => $nextNo,
            'topic' => '',
            'discussion' => '',
            'next_action' => '',
            'action_items' => [],
        ];
    }

    public function removeAgendaItem(int $index): void
    {
        unset($this->agendaItems[$index]);
        $this->agendaItems = array_values($this->agendaItems);
        foreach ($this->agendaItems as $i => &$item) {
            $item['no'] = $i + 1;
        }
    }

    public function addActionItem(int $agendaIndex): void
    {
        if (isset($this->agendaItems[$agendaIndex])) {
            $this->agendaItems[$agendaIndex]['action_items'][] = [
                'task' => '',
                'pic' => '',
                'deadline' => '',
            ];
        }
    }

    public function removeActionItem(int $agendaIndex, int $actionIndex): void
    {
        if (isset($this->agendaItems[$agendaIndex]['action_items'][$actionIndex])) {
            unset($this->agendaItems[$agendaIndex]['action_items'][$actionIndex]);
            $this->agendaItems[$agendaIndex]['action_items'] = array_values($this->agendaItems[$agendaIndex]['action_items']);
        }
    }

    public function getDepartmentsProperty(): array
    {
        return app(MoMExcelExportService::class)->getAvailableDepartments();
    }

    public function getAvailableTemplatesProperty(): array
    {
        return app(MoMExcelExportService::class)->getDepartmentTemplates($this->department);
    }

    public function getCurrentTemplateConfigProperty(): array
    {
        return app(MoMExcelExportService::class)->getActiveTemplateConfig($this->department, $this->templateVariant);
    }

    public function getCurrentFormatProperty(): string
    {
        return $this->currentTemplateConfig['format'] ?? 'xlsx';
    }

    public function downloadMoM(MoMExcelExportService $exportService): StreamedResponse
    {
        if ($this->currentFormat === 'docx') {
            return $this->downloadWord();
        }

        return $this->exportExcel($exportService);
    }

    public function downloadWord(): StreamedResponse
    {
        $syncedSubTopics = [];
        foreach ($this->agendaItems as $item) {
            $syncedSubTopics[] = [
                'title' => $item['topic'] ?? '',
                'discussion' => $item['discussion'] ?? '',
                'next_action' => $item['next_action'] ?? '',
            ];
        }
        $this->itDetails['sub_topics'] = $syncedSubTopics;

        $payload = [
            'meeting_info' => $this->meetingInfo,
            'attendees' => $this->attendees,
            'agenda_items' => $this->agendaItems,
            'it_details' => $this->itDetails,
            'department' => $this->department,
            'template_variant' => $this->templateVariant,
            'template_file' => $this->currentTemplateConfig['template'] ?? null,
        ];

        $serviceUrl = rtrim(config('services.mom_ai.url', config('services.ocr.url', 'http://127.0.0.1:8001')), '/') . '/export-word';
        $response = Http::timeout(30)->post($serviceUrl, $payload);

        if (! $response->successful()) {
            throw new \RuntimeException('Gagal mengekspor berkas Word: ' . ($response->json('detail') ?? $response->body()));
        }

        $dateStr = $this->meetingInfo['date'] ?: Carbon::now()->format('Y-m-d');
        $cleanDate = preg_replace('/[^A-Za-z0-9_-]/', '', (string) $dateStr);
        $deptCode = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $this->department));
        $fileName = "YIID_MoM_{$deptCode}_{$cleanDate}.docx";

        $docxBytes = $response->body();

        return response()->streamDownload(
            function () use ($docxBytes) {
                echo $docxBytes;
            },
            $fileName,
            [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            ]
        );
    }

    public function exportExcel(MoMExcelExportService $exportService): StreamedResponse
    {
        if ($this->currentFormat === 'docx') {
            return $this->downloadWord();
        }

        $payload = [
            'meeting_info' => $this->meetingInfo,
            'attendees' => $this->attendees,
            'agenda_items' => $this->agendaItems,
            'department' => $this->department,
        ];

        $spreadsheet = $exportService->generate($payload, $this->department, $this->templateVariant);

        $dateStr = $this->meetingInfo['date'] ?: Carbon::now()->format('Y-m-d');
        $cleanDate = preg_replace('/[^A-Za-z0-9_-]/', '', (string) $dateStr);
        $deptCode = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $this->department));
        $fileName = "YIID_MoM_{$deptCode}_{$cleanDate}.xlsx";

        return response()->streamDownload(
            function () use ($spreadsheet) {
                $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
                $writer->setPreCalculateFormulas(true);
                $writer->save('php://output');
            },
            $fileName,
            [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            ]
        );
    }

    private function cleanNullValue(mixed $value): mixed
    {
        if (is_array($value)) {
            return array_map([$this, 'cleanNullValue'], $value);
        }
        if ($value === null) {
            return '';
        }
        if (is_string($value)) {
            $trimmed = trim($value);
            if (in_array(strtolower($trimmed), ['null', 'none', '-'], true)) {
                return '';
            }
            return $value;
        }
        return $value;
    }

    public function render(): View
    {
        return view('livewire.mom.mom-generator');
    }
}
