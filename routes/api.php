<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AddressController;

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
