<?php

declare(strict_types=1);

return [
    'default' => 'business_unit',

    'departments' => [
        'business_unit' => [
            'name' => 'BU Sales',
            'label' => 'BU Sales',
            'description' => 'Template resmi Minutes of Meeting BU Sales PT Yusen Logistics.',
            'templates' => [
                'standard' => [
                    'label' => 'Standar (Excel)',
                    'template' => 'YIID MoM.xlsx',
                    'format' => 'xlsx',
                    'exporter' => \App\Services\MoM\DefaultMoMExporter::class,
                ],
            ],
        ],

        'it' => [
            'name' => 'IT',
            'label' => 'IT',
            'description' => 'Template resmi Minutes of Meeting IT (Word).',
            'templates' => [
                'standard' => [
                    'label' => 'Standar (Word)',
                    'template' => 'IT MoM.docx',
                    'format' => 'docx',
                ],
            ],
        ],

        'ssm' => [
            'name' => 'SSM',
            'label' => 'SSM',
            'description' => 'Template resmi Strategic Sales Management (SSM).',
            'templates' => [
                'weekly' => [
                    'label' => 'Weekly Progress (Word)',
                    'template' => 'IT MoM.docx',
                    'format' => 'docx',
                ],
                'project' => [
                    'label' => 'Project Review (Excel)',
                    'template' => 'YIID MoM.xlsx',
                    'format' => 'xlsx',
                    'exporter' => \App\Services\MoM\DefaultMoMExporter::class,
                ],
            ],
        ],
    ],
];
