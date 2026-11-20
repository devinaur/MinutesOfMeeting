<?php

declare(strict_types=1);

namespace App\Services\MoM;

use PhpOffice\PhpSpreadsheet\Spreadsheet;

interface MoMExporterInterface
{
    /**
     * Generate file spreadsheet MoM terisi dari data JSON terstruktur
     * menggunakan file template yang diberikan.
     *
     * @param array<string, mixed> $data
     * @param string $templatePath
     */
    public function export(array $data, string $templatePath): Spreadsheet;
}
