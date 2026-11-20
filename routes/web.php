<?php

use App\Http\Controllers\MoMExportController;
use App\Livewire\MoM\MoMGenerator;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->group(function () {
    Route::get('/', MoMGenerator::class)->name('home');
    Route::get('mom', MoMGenerator::class)->name('mom.index');
    Route::post('mom/export-excel', [MoMExportController::class, 'export'])->name('mom.export-excel');
});

require __DIR__ . '/auth.php';
