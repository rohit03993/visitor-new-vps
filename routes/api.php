<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AddressController;
use App\Http\Controllers\NotificationController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Address API routes for auto-suggestions and auto-creation
Route::get('/addresses/search', [AddressController::class, 'search'])->name('api.addresses.search');
Route::post('/addresses/store', [AddressController::class, 'store'])->name('api.addresses.store');

// Push Notification API routes - ENABLED FOR UNIFIED SYSTEM
Route::middleware(['web'])->group(function () {
    // Route::post('/notifications/subscribe', [App\Http\Controllers\PushNotificationController::class, 'subscribe'])->name('api.notifications.subscribe');
    // Route::post('/notifications/unsubscribe', [NotificationController::class, 'unsubscribe'])->name('api.notifications.unsubscribe');
    Route::get('/notifications/status', [App\Http\Controllers\PushNotificationController::class, 'getStatus'])->name('api.notifications.status'); // ENABLED FOR UNIFIED SYSTEM
    // Route::post('/notifications/send-push', [App\Http\Controllers\PushNotificationController::class, 'sendPushNotification'])->name('api.notifications.send-push');
    Route::post('/notifications/store-fcm-token', [App\Http\Controllers\PushNotificationController::class, 'storeFCMToken'])->name('api.notifications.store-fcm-token'); // ENABLED FOR UNIFIED SYSTEM
});
