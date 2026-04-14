<?php

use Contensio\Cms\Http\Controllers\Install\InstallController;
use Illuminate\Support\Facades\Route;

Route::prefix('install')
    ->name('cms.install.')
    ->middleware('web')
    ->group(function () {
        Route::get('/', fn () => redirect()->route('cms.install.requirements'))->name('index');
        Route::get('/requirements', [InstallController::class, 'requirements'])->name('requirements');
        Route::get('/database',     [InstallController::class, 'database'])->name('database');
        Route::post('/database',    [InstallController::class, 'storeDatabase'])->name('database.store');
        Route::post('/database/test', [InstallController::class, 'testDatabase'])->name('database.test');
        Route::get('/website',      [InstallController::class, 'website'])->name('website');
        Route::post('/website',     [InstallController::class, 'storeWebsite'])->name('website.store');
        Route::get('/account',      [InstallController::class, 'account'])->name('account');
        Route::post('/account',     [InstallController::class, 'storeAccount'])->name('account.store');
        Route::get('/complete',     [InstallController::class, 'complete'])->name('complete');
    });
