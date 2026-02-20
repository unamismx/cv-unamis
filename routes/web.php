<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CvController;
use App\Http\Controllers\CvTaxonomyController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\SignatureController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/dashboard');
Route::get('/cvs/verify', [CvController::class, 'verifySeal']);
Route::get('/firma/captura/{token}', [SignatureController::class, 'showCaptureForm'])->name('signature.capture.form');
Route::post('/firma/captura/{token}', [SignatureController::class, 'storeCapture'])->name('signature.capture.store');

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::get('/auth/google/redirect', [AuthController::class, 'redirectToGoogle'])->name('auth.google.redirect');
    Route::get('/auth/google/callback', [AuthController::class, 'handleGoogleCallback'])->name('auth.google.callback');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::post('/dashboard/signature/send-link', [SignatureController::class, 'sendLink']);

    Route::get('/dashboard', [DashboardController::class, 'index']);
    Route::get('/cvs/me', [CvController::class, 'edit']);
    Route::get('/cvs/me/pdf/{locale}', [CvController::class, 'downloadPdf']);
    Route::get('/cvs/published', [CvController::class, 'publishedIndex']);
    Route::get('/cvs/published/{cv}/pdf/{locale}', [CvController::class, 'downloadPublishedPdf']);
    Route::delete('/cvs/published/{cv}', [CvController::class, 'destroyPublished']);
    Route::put('/cvs/me', [CvController::class, 'update']);
    Route::post('/cvs/me/import', [CvController::class, 'importWord']);
    Route::post('/cvs/me/versions/{version}/restore', [CvController::class, 'restoreVersion']);
    Route::post('/cvs/me/publish', [CvController::class, 'publish']);

    Route::middleware('cv.taxonomy.admin')->group(function () {
        Route::get('/admin/cv-taxonomies', [CvTaxonomyController::class, 'index']);
        Route::post('/admin/cv-taxonomies', [CvTaxonomyController::class, 'store']);
        Route::patch('/admin/cv-taxonomies/{term}', [CvTaxonomyController::class, 'update']);
        Route::delete('/admin/cv-taxonomies/{term}', [CvTaxonomyController::class, 'destroy']);
    });
});
