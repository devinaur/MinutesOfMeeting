<?php

declare(strict_types=1);

namespace App\Services;

use App\Services\MoM\DefaultMoMExporter;
use App\Services\MoM\MoMExporterInterface;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

class MoMExcelExportService
{
    protected string $defaultTemplatePath;

    public function __construct()
    {
        $this->defaultTemplatePath = base_path('YIID MoM.xlsx');
    }

    public function generate(array $data, string $departmentKey = 'business_unit', ?string $templateVariant = null): Spreadsheet
    {
        $activeConfig = $this->getActiveTemplateConfig($departmentKey, $templateVariant);
        $templatePath = $this->resolveTemplatePath($activeConfig['template'] ?? null);

        $exporterClass = $activeConfig['exporter'] ?? DefaultMoMExporter::class;
        if (! class_exists($exporterClass)) {
            $exporterClass = DefaultMoMExporter::class;
        }

        /** @var MoMExporterInterface $exporter */
        $exporter = app($exporterClass);

        return $exporter->export($data, $templatePath);
    }

    public function getAvailableDepartments(): array
    {
        $departments = config('mom_templates.departments');

        if (! is_array($departments) || empty($departments)) {
            return [
                'business_unit' => [
                    'name' => 'BU Sales',
                    'label' => 'BU Sales',
                    'templates' => [
                        'standard' => [
                            'label' => 'Standar (Excel)',
                            'template' => 'YIID MoM.xlsx',
                            'format' => 'xlsx',
                            'exporter' => DefaultMoMExporter::class,
                        ],
                    ],
                ],
            ];
        }

        return $departments;
    }

    public function getDepartmentConfig(string $departmentKey): array
    {
        $all = $this->getAvailableDepartments();

        if (isset($all[$departmentKey])) {
            return $all[$departmentKey];
        }

        $defaultKey = (string) config('mom_templates.default', 'business_unit');

        return $all[$defaultKey] ?? reset($all) ?: [];
    }

    public function getDepartmentTemplates(string $departmentKey): array
    {
        $dept = $this->getDepartmentConfig($departmentKey);

        if (isset($dept['templates']) && is_array($dept['templates']) && ! empty($dept['templates'])) {
            return $dept['templates'];
        }

        if (isset($dept['template'])) {
            return [
                'standard' => [
                    'label' => $dept['label'] ?? 'Standar',
                    'template' => $dept['template'],
                    'format' => $dept['format'] ?? 'xlsx',
                    'exporter' => $dept['exporter'] ?? DefaultMoMExporter::class,
                ],
            ];
        }

        return [
            'standard' => [
                'label' => 'Standar',
                'template' => 'YIID MoM.xlsx',
                'format' => 'xlsx',
                'exporter' => DefaultMoMExporter::class,
            ],
        ];
    }

    public function getActiveTemplateConfig(string $departmentKey, ?string $templateVariant = null): array
    {
        $templates = $this->getDepartmentTemplates($departmentKey);

        if ($templateVariant && isset($templates[$templateVariant])) {
            return $templates[$templateVariant];
        }

        return reset($templates) ?: [];
    }

    protected function resolveTemplatePath(?string $configuredPath): string
    {
        if ($configuredPath) {
            $fullPath = base_path($configuredPath);
            if (file_exists($fullPath)) {
                return $fullPath;
            }
        }

        if (file_exists($this->defaultTemplatePath)) {
            return $this->defaultTemplatePath;
        }

        throw new \RuntimeException("Master template MoM file not found at: {$this->defaultTemplatePath}");
    }
}
