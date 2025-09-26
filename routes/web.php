<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\StaffController;
use App\Http\Controllers\NotificationController;

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
        Route::get('/users/{userId}/current-password', [AdminController::class, 'getCurrentPassword'])->name('get-current-password');
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
        Route::put('/update-branch/{branchId}', [AdminController::class, 'updateBranch'])->name('update-branch');
        Route::delete('/branches/{branchId}', [AdminController::class, 'deleteBranch'])->name('delete-branch');
        Route::get('/analytics', [AdminController::class, 'analytics'])->name('analytics');
        
        // Detailed Views
        Route::get('/all-visitors', [AdminController::class, 'allVisitors'])->name('all-visitors');
        Route::get('/all-interactions', [AdminController::class, 'allInteractions'])->name('all-interactions');
        Route::get('/today-interactions', [AdminController::class, 'todayInteractions'])->name('today-interactions');
        
        // Tag Management
        Route::get('/manage-tags', [AdminController::class, 'manageTags'])->name('manage-tags');
        Route::post('/create-tag', [AdminController::class, 'createTag'])->name('create-tag');
        Route::put('/update-tag/{tagId}', [AdminController::class, 'updateTag'])->name('update-tag');
        Route::delete('/delete-tag/{tagId}', [AdminController::class, 'deleteTag'])->name('delete-tag');
        Route::patch('/toggle-tag-status/{tagId}', [AdminController::class, 'toggleTagStatus'])->name('toggle-tag-status');
        
        // Advanced Filtering
        Route::get('/filter-visitors', [AdminController::class, 'filterVisitors'])->name('filter-visitors');
        Route::get('/filter-interactions', [AdminController::class, 'filterInteractions'])->name('filter-interactions');
        
        // Course Management Routes
        Route::get('/manage-courses', [AdminController::class, 'manageCourses'])->name('manage-courses');
        Route::post('/create-course', [AdminController::class, 'createCourse'])->name('create-course');
        Route::put('/update-course/{courseId}', [AdminController::class, 'updateCourse'])->name('update-course');
        Route::delete('/delete-course/{courseId}', [AdminController::class, 'deleteCourse'])->name('delete-course');
        
        // Student Selection Routes
        Route::get('/student-selection', [AdminController::class, 'showStudentSelection'])->name('student-selection');
        
        // System Reset Routes
        Route::get('/reset-stats', [AdminController::class, 'getResetStats'])->name('get-reset-stats');
        
        // File Management Routes
        Route::get('/file-management', [AdminController::class, 'fileManagement'])->name('file-management');
        Route::post('/transfer-files-to-drive', [AdminController::class, 'transferFilesToDrive'])->name('transfer-files-to-drive');
        Route::post('/bulk-transfer-files', [AdminController::class, 'bulkTransferFiles'])->name('bulk-transfer-files');
        Route::get('/file-management/status', [AdminController::class, 'getFileManagementStatus'])->name('file-management-status');
        Route::post('/reset-visitor-data', [AdminController::class, 'resetVisitorData'])->name('reset-visitor-data');
    });


    // Staff Routes (Combined Front Desk + Employee functionality)
    Route::prefix('staff')->name('staff.')->middleware('role:staff')->group(function () {
        Route::get('/dashboard', [StaffController::class, 'dashboard'])->name('dashboard');
        Route::get('/visitor-search', [StaffController::class, 'showVisitorSearch'])->name('visitor-search');
        Route::get('/assigned-to-me', [StaffController::class, 'showAssignedToMe'])->name('assigned-to-me');
        Route::post('/search-visitor', [StaffController::class, 'searchVisitor'])->name('search-visitor');
        Route::post('/advanced-search', [StaffController::class, 'advancedSearch'])->name('advanced-search');
        Route::get('/visitor-form', [StaffController::class, 'showVisitorForm'])->name('visitor-form');
        Route::post('/check-mobile', [StaffController::class, 'checkMobile'])->name('check-mobile');
        Route::post('/add-address', [StaffController::class, 'addAddress'])->name('add-address');
        Route::post('/store-visitor', [StaffController::class, 'storeVisitor'])->name('store-visitor');
        Route::post('/update-remark/{interactionId}', [StaffController::class, 'updateRemark'])->name('update-remark');
        Route::post('/mark-completed/{interactionId}', [StaffController::class, 'markAsCompleted'])->name('mark-completed');
        Route::get('/interactions/{interactionId}/remarks', [StaffController::class, 'getInteractionRemarks'])->name('get-interaction-remarks');
        Route::get('/interaction-details/{interactionId}', [StaffController::class, 'getInteractionDetails'])->name('interaction-details');
        Route::get('/visitor-profile/{visitorId}', [StaffController::class, 'showVisitorProfile'])->name('visitor-profile');
        
        // Session Management Routes
        Route::post('/complete-session/{sessionId}', [StaffController::class, 'completeSession'])->name('complete-session');
        Route::get('/session/{sessionId}/modal', [StaffController::class, 'showCompleteSessionModal'])->name('session-modal');
        
        // Assignment Routes
        Route::post('/assign-interaction/{interactionId}', [StaffController::class, 'assignInteraction'])->name('assign-interaction');
        
        // Phone Number Management Routes (NEW FEATURE)
        Route::post('/visitor/{visitorId}/add-phone', [StaffController::class, 'addPhoneNumber'])->name('add-phone-number');
        Route::delete('/visitor/{visitorId}/remove-phone/{phoneId}', [StaffController::class, 'removePhoneNumber'])->name('remove-phone-number');
        
        // File Upload Routes (GOOGLE DRIVE INTEGRATION)
        Route::post('/upload-attachment', [StaffController::class, 'uploadAttachment'])->name('upload-attachment');
        
        // Student Selection Routes
        Route::get('/student-selection', [StaffController::class, 'showStudentSelection'])->name('student-selection');
        
        // Smart refresh API
        Route::get('/check-assigned-changes', [StaffController::class, 'checkAssignedChanges'])->name('check-assigned-changes');
        
        // Password Change Routes (NEW FEATURE)
        Route::get('/change-password', [StaffController::class, 'showChangePasswordForm'])->name('change-password');
        Route::post('/change-password', [StaffController::class, 'changePassword'])->name('change-password.store');
        
        // Notification Routes (Available to staff)
        Route::prefix('notifications')->name('notifications.')->group(function () {
            // Route::get('/stream', [NotificationController::class, 'stream'])->name('stream'); // Disabled for now
            Route::post('/send', [NotificationController::class, 'sendNotification'])->name('send');
            Route::get('/get', [NotificationController::class, 'getNotifications'])->name('get');
        });
    });

    // Simple test route to check if routes work
    Route::get('/test-api', function () {
        $user = auth()->user();
        return response()->json([
            'success' => true,
            'message' => 'API is working',
            'user' => $user ? $user->name : 'Not logged in',
            'timestamp' => now()->toISOString()
        ]);
    })->name('test-api');

});

// Google OAuth Routes (No restrictions for testing)
Route::get('/auth/google', [App\Http\Controllers\Auth\GoogleAuthController::class, 'redirectToGoogle'])->name('google.auth');
Route::get('/auth/google/callback', [App\Http\Controllers\Auth\GoogleAuthController::class, 'handleGoogleCallback'])->name('google.callback');

// File Access Route (for uploaded files)
Route::get('/storage/uploads/{year}/{month}/{filename}', function ($year, $month, $filename) {
    $filePath = storage_path("app/public/uploads/{$year}/{$month}/{$filename}");
    
    if (!file_exists($filePath)) {
        abort(404);
    }
    
    return response()->file($filePath);
})->name('file.access');