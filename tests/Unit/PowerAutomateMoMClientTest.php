<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Services\MoM\MoMPromptBuilder;
use App\Services\MoM\PowerAutomateMoMClient;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class PowerAutomateMoMClientTest extends TestCase
{
    public function test_prompt_builder_generates_correct_instructions(): void
    {
        $builder = new MoMPromptBuilder();

        $itPrompt = $builder->build('it', 'standard', 'id');
        $this->assertStringContainsString('STRUKTUR SPESIFIK DEPARTEMEN IT & SYSTEMS', $itPrompt);
        $this->assertStringContainsString('Conclusion', $itPrompt);

        $buPrompt = $builder->build('business_unit', 'standard', 'id');
        $this->assertStringContainsString('STRUKTUR SPESIFIK DEPARTEMEN BUSINESS UNIT', $buPrompt);

        $ssmPrompt = $builder->build('ssm', 'weekly', 'en');
        $this->assertStringContainsString('STRUKTUR SPESIFIK STRATEGIC SALES MANAGEMENT', $ssmPrompt);
        $this->assertStringContainsString('Output ALL content', $ssmPrompt);
    }

    public function test_client_handles_text_flow_success(): void
    {
        config([
            'services.power_automate.text_url' => 'https://mock.azure.com/text-flow',
            'services.power_automate.api_key' => 'secret-test-key',
        ]);

        $builder = new MoMPromptBuilder();
        $client = new PowerAutomateMoMClient($builder);

        $this->assertTrue($client->isConfigured('text'));

        Http::fake([
            'https://mock.azure.com/text-flow' => Http::response([
                'success' => true,
                'data' => [
                    'meeting_info' => ['date' => '19-11-2026'],
                    'agenda_items' => [['topic' => 'Test Topic']],
                ],
            ], 200),
        ]);

        $result = $client->processText('Catatan rapat...', 'it', 'standard', 'id');
        $this->assertTrue($result['success']);
        $this->assertEquals('19-11-2026', $result['data']['meeting_info']['date']);
    }

    public function test_client_handles_error_with_rescued_transcript(): void
    {
        config([
            'services.power_automate.audio_url' => 'https://mock.azure.com/audio-flow',
        ]);

        $builder = new MoMPromptBuilder();
        $client = new PowerAutomateMoMClient($builder);

        $this->assertTrue($client->isConfigured('audio'));

        Http::fake([
            'https://mock.azure.com/audio-flow' => Http::response([
                'success' => false,
                'error_stage' => 'TEXT_TO_JSON',
                'message' => 'Token limit exceeded',
                'raw_transcript' => 'Rescued transcript text',
            ], 422),
        ]);

        // Create temp test file
        $tempPath = tempnam(sys_get_temp_dir(), 'test_audio_');
        file_put_contents($tempPath, 'dummy audio data');

        $result = $client->processAudio(
            filePath: $tempPath,
            fileName: 'test.mp3',
            mimeType: 'audio/mp3',
            department: 'it',
            templateVariant: 'standard',
            language: 'id'
        );

        unlink($tempPath);

        $this->assertFalse($result['success']);
        $this->assertEquals('TEXT_TO_JSON', $result['error_stage']);
        $this->assertEquals('Rescued transcript text', $result['raw_transcript']);
    }
}
