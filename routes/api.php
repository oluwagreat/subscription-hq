<?php

use App\Http\Controllers\PlanController;
use App\Http\Controllers\SubscriptionController;
use App\Http\Controllers\TransactionController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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

//Route::get('plans/{plan_code}', [PlanController::class, 'single']);
//Route::post('/subscriptions', [SubscriptionController::class, 'store']);

Route::resource('/plans',PlanController::class);
Route::resource('/subscriptions',SubscriptionController::class);
Route::resource('/transactions',TransactionController::class);