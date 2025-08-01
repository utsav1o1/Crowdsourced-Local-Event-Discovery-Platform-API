<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\EventAttendanceController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\ReviewController;

Route::middleware('auth:sanctum')->group(function () {
    Route::get('events', [EventController::class, 'index']);
    Route::get('events/nearby', [EventController::class, 'nearby']);
    Route::post('events', [EventController::class, 'store']);
    Route::get('events/{id}', [EventController::class, 'show']);
    Route::put('events/{id}', [EventController::class, 'update']);
    Route::patch('events/{id}', [EventController::class, 'update']);
    Route::delete('events/{id}', [EventController::class, 'destroy']);
    Route::prefix('events/{event}')->group(function () {
        Route::post('add-attendee', [EventAttendanceController::class, 'addAttendee']);
        Route::post('mark-attendance', [EventAttendanceController::class, 'markAttendanceByQR']);
        Route::post('reviews', [ReviewController::class, 'store']);
    });
});
Route::prefix('events/{event}')->group(function () {
    Route::get('reviews', [ReviewController::class, 'index']);
});
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:sanctum');
