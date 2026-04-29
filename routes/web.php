<?php

use App\Http\Controllers\Auth\GoogleAuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LogsController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SourceAccountController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\TelegramWebhookController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return auth()->check()
        ? redirect()->route('dashboard')
        : Inertia::render('Auth/Login', ['canResetPassword' => false, 'status' => session('status')]);
});

Route::get('/auth/google/redirect', [GoogleAuthController::class, 'redirect'])->name('google.redirect');
Route::get('/auth/google/callback', [GoogleAuthController::class, 'callback'])->name('google.callback');

Route::post('/webhooks/telegram/{token}', TelegramWebhookController::class)
    ->middleware('throttle:120,1')
    ->name('webhooks.telegram');

Route::middleware(['auth', 'allowed'])->group(function () {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');

    Route::get('/tasks', [TaskController::class, 'index'])->name('tasks.index');
    Route::get('/tasks/create', [TaskController::class, 'create'])->name('tasks.create');
    Route::post('/tasks', [TaskController::class, 'store'])->name('tasks.store');
    Route::get('/tasks/{task}', [TaskController::class, 'show'])->name('tasks.show');
    Route::patch('/tasks/{task}', [TaskController::class, 'update'])->name('tasks.update');
    Route::delete('/tasks/{task}', [TaskController::class, 'destroy'])->name('tasks.destroy');
    Route::post('/tasks/{task}/comments', [TaskController::class, 'addComment'])->name('tasks.comments.store');
    Route::post('/tasks/{task}/reanalyze', [TaskController::class, 'reanalyze'])->name('tasks.reanalyze');
    Route::post('/tasks/{task}/push-to-monday', [TaskController::class, 'pushToMonday'])->name('tasks.push-monday');
    Route::post('/tasks/{task}/push-to-wrike', [TaskController::class, 'pushToWrike'])->name('tasks.push-wrike');

    Route::get('/sources', [SourceAccountController::class, 'index'])->name('sources.index');
    Route::post('/sources', [SourceAccountController::class, 'store'])->name('sources.store');
    Route::get('/sources/gmail/connect', [SourceAccountController::class, 'connectGmail'])->name('sources.gmail.connect');
    Route::get('/sources/gmail/callback', [SourceAccountController::class, 'connectGmailCallback'])->name('sources.gmail.callback');
    Route::post('/sources/{type}/connect-token', [SourceAccountController::class, 'connectToken'])
        ->whereIn('type', ['slack', 'telegram', 'monday', 'wrike'])
        ->name('sources.connect-token');
    Route::get('/sources/{source}', [SourceAccountController::class, 'show'])->name('sources.show');
    Route::patch('/sources/{source}', [SourceAccountController::class, 'update'])->name('sources.update');
    Route::delete('/sources/{source}', [SourceAccountController::class, 'destroy'])->name('sources.destroy');
    Route::post('/sources/{source}/sync', [SourceAccountController::class, 'sync'])->name('sources.sync');
    Route::post('/sources/{source}/toggle', [SourceAccountController::class, 'toggle'])->name('sources.toggle');

    Route::get('/logs/ai', [LogsController::class, 'ai'])->name('logs.ai');
    Route::get('/logs/sync', [LogsController::class, 'sync'])->name('logs.sync');

    Route::get('/settings', fn () => Inertia::render('Settings'))->name('settings');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
