<?php

use App\Http\Controllers\DonationController;
use App\Http\Controllers\FrontendController;
use Illuminate\Support\Facades\Route;

Route::get('/', [FrontendController::class, 'index'])->name('frontend.index');

// Donation routes
Route::post('/donation/checkout', [DonationController::class, 'createCheckoutSession'])->name('donation.checkout');
Route::get('/donation/success', [DonationController::class, 'success'])->name('donation.success');
Route::get('/donation/cancel', [DonationController::class, 'cancel'])->name('donation.cancel');
Route::post('/webhook/stripe', [DonationController::class, 'handleWebhook'])->name('webhook.stripe');
