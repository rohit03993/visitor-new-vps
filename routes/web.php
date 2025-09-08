<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\FrontDeskController;
use App\Http\Controllers\EmployeeController;

// Redirect root to login
Route::get('/', function () {
    return redirect()->route('login');
});

// Authentication Routes
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Protected Routes
Route::middleware('auth')->group(function () {
    
    // Admin Routes
    Route::prefix('admin')->name('admin.')->middleware('role:admin')->group(function () {
        Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');
        Route::get('/search-mobile', [AdminController::class, 'showSearchForm'])->name('search-mobile');
        Route::post('/search-mobile', [AdminController::class, 'searchByMobile']);
        Route::get('/visitor-profile/{visitorId}', [AdminController::class, 'exportVisitorProfile'])->name('visitor-profile');
        Route::get('/manage-users', [AdminController::class, 'manageUsers'])->name('manage-users');
        Route::post('/create-user', [AdminController::class, 'createUser'])->name('create-user');
        Route::put('/users/{userId}', [AdminController::class, 'updateUser'])->name('update-user');
        Route::get('/users/{userId}/branch-permissions', [AdminController::class, 'getBranchPermissions'])->name('get-branch-permissions');
        Route::post('/users/{userId}/branch-permissions', [AdminController::class, 'saveBranchPermissions'])->name('save-branch-permissions');
        Route::get('/users/{userId}/deactivate-stats', [AdminController::class, 'getUserDeactivateStats'])->name('get-user-deactivate-stats');
        Route::put('/users/{userId}/deactivate', [AdminController::class, 'deactivateUser'])->name('deactivate-user');
        Route::put('/users/{userId}/reactivate', [AdminController::class, 'reactivateUser'])->name('reactivate-user');
        Route::get('/manage-locations', [AdminController::class, 'manageLocations'])->name('manage-locations');
        Route::post('/create-location', [AdminController::class, 'createLocation'])->name('create-location');
        Route::delete('/locations/{addressId}', [AdminController::class, 'deleteLocation'])->name('delete-location');
        Route::get('/manage-branches', [AdminController::class, 'manageBranches'])->name('manage-branches');
        Route::post('/create-branch', [AdminController::class, 'createBranch'])->name('create-branch');
        Route::delete('/branches/{branchId}', [AdminController::class, 'deleteBranch'])->name('delete-branch');
        Route::get('/analytics', [AdminController::class, 'analytics'])->name('analytics');
    });

    // Front Desk Routes
    Route::prefix('frontdesk')->name('frontdesk.')->middleware('role:frontdesk')->group(function () {
        Route::get('/dashboard', [FrontDeskController::class, 'showGoogleSearch'])->name('dashboard'); // Google search is now default
        Route::get('/old-dashboard', [FrontDeskController::class, 'dashboard'])->name('old-dashboard'); // Keep old dashboard accessible
        Route::get('/google-search', [FrontDeskController::class, 'showGoogleSearch'])->name('google-search');
        Route::post('/search-visitor', [FrontDeskController::class, 'searchVisitor'])->name('search-visitor');
        Route::get('/visitor-form', [FrontDeskController::class, 'showVisitorForm'])->name('visitor-form');
        Route::post('/check-mobile', [FrontDeskController::class, 'checkMobile'])->name('check-mobile');
        Route::post('/add-address', [FrontDeskController::class, 'addAddress'])->name('add-address');
        Route::post('/store-visitor', [FrontDeskController::class, 'storeVisitor'])->name('store-visitor');
        Route::get('/search-visitors', [FrontDeskController::class, 'showSearchForm'])->name('search-visitors');
        Route::post('/search-visitors', [FrontDeskController::class, 'searchVisitors']);
        Route::get('/interactions/{interactionId}/remarks', [FrontDeskController::class, 'getInteractionRemarks'])->name('get-interaction-remarks');
        Route::get('/download/today-excel', [FrontDeskController::class, 'downloadTodayExcel'])->name('download-today-excel');
    });

    // Employee Routes
    Route::prefix('employee')->name('employee.')->middleware('role:employee')->group(function () {
        Route::get('/dashboard', [EmployeeController::class, 'dashboard'])->name('dashboard');
        Route::post('/update-remark/{interactionId}', [EmployeeController::class, 'updateRemark'])->name('update-remark');
        Route::get('/visitor-history/{visitorId}', [EmployeeController::class, 'getVisitorHistory'])->name('visitor-history');
        
        // New visitor entry functionality
        Route::get('/visitor-search', [EmployeeController::class, 'showVisitorSearch'])->name('visitor-search');
        Route::post('/search-visitor', [EmployeeController::class, 'searchVisitor'])->name('search-visitor');
        Route::get('/visitor-form', [EmployeeController::class, 'showVisitorForm'])->name('visitor-form');
        Route::post('/check-mobile', [EmployeeController::class, 'checkMobile'])->name('check-mobile');
        Route::post('/add-address', [EmployeeController::class, 'addAddress'])->name('add-address');
        Route::post('/store-visitor', [EmployeeController::class, 'storeVisitor'])->name('store-visitor');
    });
});
