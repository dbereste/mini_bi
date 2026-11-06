<?php

use Illuminate\Support\Facades\Route;
use Modules\Dashboard\Overview\Http\Controllers\OverviewController;

// Page View
Route::get('/', [OverviewController::class, 'index'])->name('overview');

// CSV export of the affiliate performance table
Route::get('/overview/export', [OverviewController::class, 'export'])->name('overview.export');
