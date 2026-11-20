<?php

declare(strict_types=1);

namespace App\Services\MoM;

use Illuminate\Support\Facades\Http;

class PowerAutomateMoMClient
{
    protected MoMPromptBuilder $promptBuilder;

    public function __construct(MoMPromptBuilder $promptBuilder)
    {
        $this->promptBuilder = $promptBuilder;
    }

    public function isConfigured(string $mode = 'text'): bool
    {
        $url = ($mode === 'audio')
            ? config('services.power_automate.audio_url')
            : config('services.power_automate.text_url');

        return ! empty($url);
    }

    /**
     * Kirim input teks ke Flow 1: Power Automate Text Processor.
     *
     * @return array{success: bool, data?: array, message?: string, error_stage?: string, raw_transcript?: string}
     */
    public function processText(
        string $text,
        string $department,
        string $templateVariant,
        string $language
    ): array {
        $url = config('services.power_automate.text_url');
        if (empty($url)) {
            throw new \RuntimeException('URL Power Automate Text belum dikonfigurasi di file .env.');
        }

        $prompt = $this->promptBuilder->build($department, $templateVariant, $language);

        $payload = [
            'language'         => $language,
            'department'       => $department,
            'template_variant' => $templateVariant,
            'text_content'     => $text,
            'system_prompt'    => $prompt,
        ];

        return $this->sendRequest($url, $payload, timeout: 45, errorStageDefault: 'TEXT_TO_JSON');
    }

    /**
     * Kirim input audio rekaman suara ke Flow 2: Power Automate Audio Processor.
     *
     * @return array{success: bool, data?: array, message?: string, error_stage?: string, raw_transcript?: string}
     */
    public function processAudio(
        string $filePath,
        string $fileName,
        string $mimeType,
        string $department,
        string $templateVariant,
        string $language
    ): array {
        $url = config('services.power_automate.audio_url');
        if (empty($url)) {
            throw new \RuntimeException('URL Power Automate Audio belum dikonfigurasi di file .env.');
        }

        $audioBytes = file_get_contents($filePath);
        if ($audioBytes === false) {
            throw new \RuntimeException("Gagal membaca berkas audio dari: {$filePath}");
        }

        $prompt = $this->promptBuilder->build($department, $templateVariant, $language);

        $payload = [
            'language'         => $language,
            'department'       => $department,
            'template_variant' => $templateVariant,
            'audio_base64'     => base64_encode($audioBytes),
            'audio_mime_type'  => $mimeType,
            'audio_file_name'  => $fileName,
            'system_prompt'    => $prompt,
        ];

        return $this->sendRequest($url, $payload, timeout: 180, errorStageDefault: 'VOICE_TO_TEXT');
    }

    /**
     * Kirim HTTP POST JSON ke Webhook Power Automate.
     */
    protected function sendRequest(string $url, array $payload, int $timeout, string $errorStageDefault): array
    {
        $headers = [
            'Content-Type' => 'application/json',
            'Accept'       => 'application/json',
        ];

        $apiKey = config('services.power_automate.api_key');
        if (! empty($apiKey)) {
            $headers['x-api-key'] = $apiKey;
        }

        $response = Http::withHeaders($headers)
            ->timeout($timeout)
            ->post($url, $payload);

        $body = $response->json();

        // Handle error responses (HTTP status non-2xx)
        if (! $response->successful()) {
            return [
                'success'        => false,
                'error_stage'    => $body['error_stage'] ?? $errorStageDefault,
                'message'        => $body['message'] ?? 'Power Automate merespon dengan status error: ' . $response->status(),
                'raw_transcript' => $body['raw_transcript'] ?? null,
            ];
        }

        // Jika Power Automate mengembalikan wrapper { "success": false, ... }
        if (is_array($body) && isset($body['success']) && ! $body['success']) {
            return [
                'success'        => false,
                'error_stage'    => $body['error_stage'] ?? $errorStageDefault,
                'message'        => $body['message'] ?? 'Proses di Power Automate gagal dieksekusi.',
                'raw_transcript' => $body['raw_transcript'] ?? null,
            ];
        }

        // Ambil data inti MoM
        $data = $body['data'] ?? $body;

        return [
            'success' => true,
            'data'    => $data,
        ];
    }
}
