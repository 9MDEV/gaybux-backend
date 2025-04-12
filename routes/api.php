<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\RobuxController;
use App\Http\Controllers\Api\TopupController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\AdminController;
use App\Http\Controllers\Api\GroupContoller;
use App\Models\User;
use App\Models\Group;
use Illuminate\Support\Facades\Artisan;
use App\Models\Setting;
use Bavix\Wallet\Models\Transaction;
Route::post('/login', [AuthController::class, 'login'])->middleware(['guest','throttle:5,1']);
Route::post('/register', [AuthController::class, 'register'])->middleware(['guest','throttle:5,1']);


Route::post('/buy', [RobuxController::class, 'buy'])->middleware(['auth:sanctum','throttle:100,1']);

Route::post('/topup', [TopupController::class, 'topup'])->middleware(['auth:sanctum','throttle:5,1']);

Route::get('/buy', [RobuxController::class, 'getSetting'])->middleware(['auth:sanctum','throttle:50,1']);

Route::get('/topup', [TopupController::class, 'getSetting'])->middleware(['auth:sanctum','throttle:5,1']);

Route::get('/groups', [GroupContoller::class, 'index'])->middleware(['auth:sanctum','throttle:50,1']);


Route::prefix('/transactions')->middleware(['auth:sanctum', 'throttle:30,1'])->group(function () {
    Route::get('/buy', [RobuxController::class, 'show']);
    Route::get('/buy/total', [RobuxController::class, 'total']);           // เฉพาะของ user
    Route::get('/buy/all', [RobuxController::class, 'index']);      // ทั้งหมด (admin)
    Route::get('/topup', [TopupController::class, 'show']);           // เฉพาะของ user
    Route::get('/topup/total', [TopupController::class, 'total']);           // เฉพาะของ user
    Route::get('/topup/all', [TopupController::class, 'index']);      // ทั้งหมด (admin)
});


Route::prefix('/admin')->middleware(['auth:sanctum', 'throttle:30,1'])->group(function () {
    Route::get('/otp', [AdminController::class, 'genOTP']);
    Route::get('/settings', [AdminController::class, 'getSetting']);
    Route::put('/settings', [AdminController::class, 'updateSetting']);
});

Route::apiResource('users', UserController::class)->middleware(['auth:sanctum']);

Route::get('/d/{amount}', function (Request $request,$amount) {
    $user = $request->user();
    $user->depositFloat($amount);
    return response()->json([
        'id'       => $user->id,
        'username' => $user->username,
        'email'    => $user->email,
        'roles'    => $user->getRoleNames(),
        'balance' => $user->balanceFloatNum,
    ]);
})->middleware(['auth:sanctum','throttle:60,1']);



Route::get('/user', function (Request $request) {
    $user = $request->user();
    return response()->json([
        'id'       => $user->id,
        'username' => $user->username,
        'email'    => $user->email,
        'roles'    => $user->getRoleNames(),
        'balance' => $user->balanceFloatNum,
    ]);
})->middleware(['auth:sanctum','throttle:60,1']);


