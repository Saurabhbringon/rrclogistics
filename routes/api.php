<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\IpAddressController;
use App\Http\Controllers\Api\VehicleController;
use App\Http\Controllers\Api\DistanceController;
use App\Http\Controllers\Api\MileageRateController;

// Authentication routes
Route::post('/login', [AuthController::class, 'login']);
Route::post('/verify-otp', [AuthController::class, 'verifyOtp']);
Route::post('/resend-otp', [AuthController::class, 'resendOtp']);
Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
Route::post('/reset-password', [AuthController::class, 'resetPassword']);
Route::post('/change-password', [AuthController::class, 'changePassword']);
Route::get('/check-ip', [AuthController::class, 'checkCurrentIp']);

// User CRUD routes
Route::post('/users', [UserController::class, 'store']);
Route::get('/users', [UserController::class, 'index']);
Route::get('/users/{id}', [UserController::class, 'show']);
Route::put('/users/{id}', [UserController::class, 'update']);
Route::patch('/users/{id}/status', [UserController::class, 'updateStatus']);
Route::delete('/users/{id}', [UserController::class, 'destroy']);

// IP Address CRUD routes
Route::post('/ip-addresses', [IpAddressController::class, 'store']);
Route::get('/ip-addresses', [IpAddressController::class, 'index']);
Route::get('/ip-addresses/active', [IpAddressController::class, 'getActiveIps']);
Route::get('/ip-addresses/inactive', [IpAddressController::class, 'getInactiveIps']);
Route::get('/ip-addresses/status/summary', [IpAddressController::class, 'getIpsByStatus']);
Route::get('/ip-addresses/{id}', [IpAddressController::class, 'show']);
Route::put('/ip-addresses/{id}', [IpAddressController::class, 'update']);
Route::patch('/ip-addresses/{id}/status', [IpAddressController::class, 'updateStatus']);
Route::patch('/ip-addresses/security-status/update-all', [IpAddressController::class, 'updateAllSecurityStatus']);
Route::delete('/ip-addresses/{id}', [IpAddressController::class, 'destroy']);

// Vehicle CRUD routes
Route::post('/vehicles', [VehicleController::class, 'store']);
Route::post('/vehicles/upload-vehicle-data-excel', [VehicleController::class, 'uploadVehicleDataExcel']);
Route::post('/vehicles/milage', [VehicleController::class, 'milage']);
Route::get('/vehicles/download-template', [VehicleController::class, 'downloadTemplate']);
Route::get('/vehicles', [VehicleController::class, 'index']);
Route::get('/vehicles/active', [VehicleController::class, 'getActiveVehicles']);
Route::get('/vehicles/inactive', [VehicleController::class, 'getInactiveVehicles']);
Route::get('/vehicles/search', [VehicleController::class, 'search']);
Route::get('/vehicles/{id}', [VehicleController::class, 'show']);
Route::put('/vehicles/{id}', [VehicleController::class, 'update']);
Route::patch('/vehicles/{id}/status', [VehicleController::class, 'updateStatus']);
Route::delete('/vehicles/{id}', [VehicleController::class, 'destroy']);
Route::post('/vehicles/getmilage', [VehicleController::class, 'milage']);

// Distance CRUD routes
Route::post('/distances', [DistanceController::class, 'store']);
Route::post('/distances/upload-distance-data-excel', [DistanceController::class, 'uploadDistanceDataExcel']);
Route::get('/distances/download-template', [DistanceController::class, 'downloadTemplate']);
Route::post('/distances/get-route-distance', [DistanceController::class, 'getRouteDistance']);
Route::get('/distances', [DistanceController::class, 'index']);
Route::get('/distances/search', [DistanceController::class, 'search']);
Route::get('/distances/{id}', [DistanceController::class, 'show']);
Route::put('/distances/{id}', [DistanceController::class, 'update']);
Route::delete('/distances/{id}', [DistanceController::class, 'destroy']);

// Mileage Rate CRUD routes
Route::post('/mileage-rates', [MileageRateController::class, 'store']);
Route::get('/mileage-rates', [MileageRateController::class, 'index']);
Route::post('/mileage-rates/get-rate', [MileageRateController::class, 'getRateForLoad']);
Route::get('/mileage-rates/{id}', [MileageRateController::class, 'show']);
Route::put('/mileage-rates/{id}', [MileageRateController::class, 'update']);
Route::delete('/mileage-rates/{id}', [MileageRateController::class, 'destroy']);