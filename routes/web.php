<?php

use App\Http\Controllers\Admin\DepartmentController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\ApprovalProcessController;
use App\Http\Controllers\ApprovalStepController;
use App\Http\Controllers\ApprovalsController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\LogoutController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DocumentCommentController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\Settings\PasswordController;
use App\Http\Controllers\Settings\ProfileController;
use App\Http\Controllers\Settings\SettingsController;
use App\Http\Controllers\Settings\SignatureController;
use App\Http\Middleware\EnsureUserCanManageUsers;
use App\Http\Middleware\EnsureUserIsAdmin;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store'])->name('login.store');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', LogoutController::class)->name('logout');

    Route::get('/settings', [SettingsController::class, 'index'])->name('settings.index');
    Route::post('/settings/profile', [ProfileController::class, 'update'])->name('settings.profile');
    Route::post('/settings/password', [PasswordController::class, 'update'])->name('settings.password');
    Route::post('/settings/signature', [SignatureController::class, 'update'])->name('settings.signature');

    Route::prefix('admin')->name('admin.')->group(function () {
        Route::middleware(EnsureUserCanManageUsers::class)->group(function () {
            Route::post('users/{user}/restore', [UserController::class, 'restore'])->name('users.restore');
            Route::resource('users', UserController::class)->except(['show']);
        });

        Route::middleware(EnsureUserIsAdmin::class)->group(function () {
            Route::get('departments', [DepartmentController::class, 'index'])->name('departments.index');
            Route::get('departments/{department}/edit', [DepartmentController::class, 'edit'])->name('departments.edit');
            Route::put('departments/{department}', [DepartmentController::class, 'update'])->name('departments.update');
        });
    });

    Route::get('/', [DocumentController::class, 'index'])->name('documents.index');
    Route::get('/categories/{category}', [DocumentController::class, 'category'])->name('categories.show');
    Route::post('/categories', [CategoryController::class, 'store'])->name('categories.store');
    Route::put('/categories/{category}', [CategoryController::class, 'update'])->name('categories.update');
    Route::delete('/categories/{category}', [CategoryController::class, 'destroy'])->name('categories.destroy');

    Route::post('/documents', [DocumentController::class, 'store'])->name('documents.store');
    Route::post('/documents/{document}/approval-process', [ApprovalProcessController::class, 'store'])
        ->name('documents.approval-process.store');
    Route::put('/documents/{document}', [DocumentController::class, 'update'])->name('documents.update');
    Route::post('/documents/{document}/comments', [DocumentCommentController::class, 'store'])
        ->name('documents.comments.store');
    Route::get('/documents/{document}/preview', [DocumentController::class, 'preview'])->name('documents.preview');
    Route::get('/documents/{document}/download', [DocumentController::class, 'downloadLatest'])->name('documents.download');
    Route::get('/documents/{document}/versions/{document_version}/download', [DocumentController::class, 'downloadVersion'])
        ->name('documents.versions.download');
    Route::get('/documents/{document}', [DocumentController::class, 'show'])->name('documents.show');
    Route::post('/documents/{document}/reopen-draft', [DocumentController::class, 'reopenAsDraft'])
        ->name('documents.reopen-draft');
    Route::delete('/documents/{document}', [DocumentController::class, 'destroy'])->name('documents.destroy');

    Route::get('/approvals', [ApprovalsController::class, 'index'])->name('approvals.index');
    Route::post('/approval-steps/{approval_step}/approve', [ApprovalStepController::class, 'approve'])
        ->name('approval-steps.approve');
    Route::post('/approval-steps/{approval_step}/reject', [ApprovalStepController::class, 'reject'])
        ->name('approval-steps.reject');
});
