<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AdminController;
use App\Http\Controllers\Api\CourseController;
use App\Http\Controllers\Api\StudentController;
use App\Http\Controllers\Api\EnrollmentController;
use App\Http\Controllers\Api\ReportsController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\AcademicCouncilController;
use App\Http\Controllers\Api\ManagementController;
use App\Http\Controllers\Api\JobPostingController;
use App\Http\Controllers\Api\CareerController;
use App\Http\Controllers\Api\FacilityController;
use App\Http\Controllers\Api\SettingController;

Route::middleware('api')->group(function () {
    // Public routes
    Route::post('admins/login', [AdminController::class, 'login']);
    Route::get('public/settings/group/{group}', [SettingController::class, 'getPublicByGroup']);
    Route::get('public/academic-council', [AcademicCouncilController::class, 'publicList']);
    Route::get('public/management', [ManagementController::class, 'publicList']);
    Route::get('public/facilities', [FacilityController::class, 'publicList']);
    
    // Protected routes
    Route::middleware(['auth.api'])->group(function () {
        // Admin routes
        Route::apiResource('admins', AdminController::class);
        
        // Course routes
        Route::apiResource('courses', CourseController::class);
        
        // Student routes
        Route::apiResource('students', StudentController::class);
        Route::post('students/{id}/upload-resume', [StudentController::class, 'uploadResume']);
        Route::get('students/{id}/download-resume', [StudentController::class, 'downloadResume']);
        Route::delete('students/{id}/resume', [StudentController::class, 'deleteResume']);
        
        // Enrollment routes
        Route::apiResource('enrollments', EnrollmentController::class);
        Route::get('enrollments/student/{student_id}', [EnrollmentController::class, 'getByStudent']);
        Route::get('enrollments/course/{course_id}', [EnrollmentController::class, 'getByCourse']);
        
        // Reports routes
        Route::get('reports/dashboard', [ReportsController::class, 'dashboard']);
        Route::get('reports/enrollments', [ReportsController::class, 'enrollmentStats']);
        Route::get('reports/courses', [ReportsController::class, 'courseStats']);
        Route::get('reports/students', [ReportsController::class, 'studentStats']);
        Route::get('reports/revenue', [ReportsController::class, 'revenueStats']);
        Route::get('reports/export', [ReportsController::class, 'exportData']);
        
        // Notification routes
        Route::get('notifications', [NotificationController::class, 'index']);
        Route::post('notifications', [NotificationController::class, 'store']);
        Route::patch('notifications/{id}/read', [NotificationController::class, 'markAsRead']);
        Route::patch('notifications/read-all', [NotificationController::class, 'markAllAsRead']);
        Route::get('notifications/unread-count', [NotificationController::class, 'unreadCount']);
        
        // Academic Council routes
        Route::apiResource('academic-council', AcademicCouncilController::class);
        Route::post('academic-council/{id}/upload-image', [AcademicCouncilController::class, 'uploadImage']);
        Route::get('academic-council/download/export', [AcademicCouncilController::class, 'download']);
        Route::get('academic-council/download/pdf', [AcademicCouncilController::class, 'downloadPdf']);
        Route::get('academic-council/download/ppt', [AcademicCouncilController::class, 'downloadPpt']);
        
        // Management routes
        Route::apiResource('management', ManagementController::class);
        Route::post('management/{id}/upload-image', [ManagementController::class, 'uploadImage']);
        Route::get('management/download/export', [ManagementController::class, 'download']);
        Route::get('management/download/pdf', [ManagementController::class, 'downloadPdf']);
        Route::get('management/download/ppt', [ManagementController::class, 'downloadPpt']);
        
        // Job Posting routes
        Route::apiResource('job-postings', JobPostingController::class);
        
        // Career routes
        Route::apiResource('careers', CareerController::class);
        
        // Facility routes
        Route::apiResource('facilities', FacilityController::class);
        
        // Settings routes
        Route::apiResource('settings', SettingController::class);
        Route::get('settings/key/{key}', [SettingController::class, 'getByKey']);
        Route::get('settings/group/{group}', [SettingController::class, 'getByGroup']);
    });
});
