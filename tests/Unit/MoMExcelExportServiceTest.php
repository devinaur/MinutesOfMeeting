<?php

namespace Tests\Unit;

use App\Services\MoMExcelExportService;
use Tests\TestCase;

class MoMExcelExportServiceTest extends TestCase
{
    public function test_service_generates_spreadsheet_from_array(): void
    {
        $service = new MoMExcelExportService();

        $data = [
            'meeting_info' => [
                'date' => '2026-11-09',
                'time' => '10:00 WIB',
                'location' => 'Board Room',
                'chairman' => 'Devina',
            ],
            'attendees' => [
                ['name' => 'Devina', 'company' => 'PT Yusen Logistics Interlink Indonesia', 'division' => 'IT'],
            ],
            'agenda_items' => [
                [
                    'no' => 1,
                    'topic' => 'Project Kickoff',
                    'discussion' => 'Project started successfully.',
                    'action_items' => [],
                ],
            ],
        ];

        $spreadsheet = $service->generate($data);
        $this->assertNotNull($spreadsheet);
        $sheet = $spreadsheet->getActiveSheet();
        $this->assertEquals('09-Nov-2026', $sheet->getCell('D5')->getValue());
        $this->assertEquals('10:00 WIB', $sheet->getCell('J5')->getValue());
        $this->assertEquals('Board Room', $sheet->getCell('D6')->getValue());
        $this->assertEquals('Devina', $sheet->getCell('D13')->getValue());
    }

    public function test_department_templates_and_active_variant_resolution(): void
    {
        $service = new MoMExcelExportService();

        // SSM has 2 templates (weekly docx & project xlsx)
        $ssmTemplates = $service->getDepartmentTemplates('ssm');
        $this->assertCount(2, $ssmTemplates);
        $this->assertArrayHasKey('weekly', $ssmTemplates);
        $this->assertArrayHasKey('project', $ssmTemplates);

        $weeklyConfig = $service->getActiveTemplateConfig('ssm', 'weekly');
        $this->assertEquals('docx', $weeklyConfig['format']);

        $projectConfig = $service->getActiveTemplateConfig('ssm', 'project');
        $this->assertEquals('xlsx', $projectConfig['format']);

        // BU Sales has 1 template
        $buTemplates = $service->getDepartmentTemplates('business_unit');
        $this->assertCount(1, $buTemplates);
        $buConfig = $service->getActiveTemplateConfig('business_unit');
        $this->assertEquals('xlsx', $buConfig['format']);
    }
}
