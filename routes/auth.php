<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\VerificationController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\Auth\ConfirmPasswordController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    // Customer registration
    Route::get('register', [RegisterController::class, 'showRegistrationForm'])
                ->name('register');

    Route::post('register', [RegisterController::class, 'register']);

    // Vendor registration
    Route::get('register/vendor', [RegisterController::class, 'showVendorRegistrationForm'])
                ->name('register.vendor');

    Route::post('register/vendor', [RegisterController::class, 'registerVendor']);

    // Rider registration
    Route::get('register/rider', [RegisterController::class, 'showRiderRegistrationForm'])
                ->name('register.rider');

    Route::post('register/rider', [RegisterController::class, 'registerRider']);

    Route::get('login', [LoginController::class, 'showLoginForm'])
                ->name('login');

    Route::post('login', [LoginController::class, 'login']);

    Route::get('forgot-password', [ForgotPasswordController::class, 'showLinkRequestForm'])
                ->name('password.request');

    Route::post('forgot-password', [ForgotPasswordController::class, 'sendResetLinkEmail'])
                ->name('password.email');

    Route::get('reset-password/{token}', [ResetPasswordController::class, 'showResetForm'])
                ->name('password.reset');

    Route::put('password', [ResetPasswordController::class, 'reset'])
                ->name('password.update');
});

Route::middleware('auth')->group(function () {
    Route::get('verify-email', [VerificationController::class, 'show'])
                ->name('verification.notice');

    Route::get('verify-email/{id}/{hash}', [VerificationController::class, 'verify'])
                ->middleware(['signed', 'throttle:6,1'])
                ->name('verification.verify');

    Route::post('email/verification-notification', [VerificationController::class, 'resend'])
                ->middleware('throttle:6,1')
                ->name('verification.send');

    Route::get('confirm-password', [ConfirmPasswordController::class, 'showConfirmForm'])
                ->name('password.confirm');

    Route::post('confirm-password', [ConfirmPasswordController::class, 'confirm']);

    Route::post('logout', [LoginController::class, 'logout'])
                ->name('logout');
});