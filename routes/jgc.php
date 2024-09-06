<?php

use App\Http\Controllers\AuthControllerJGC;
use App\Http\Controllers\QuestionsController;
use Illuminate\Support\Facades\Route;



Route::group(['prefix' => 'jgc'], function () {

    Route::get('/test', function () {
        return response()->json(['status' => true, 'message' => "JGC API is up and running", 'data' => [], 'errors' => [],], 200);
    });
    Route::group(['prefix' => 'auth'], function () {

        Route::post('register', [AuthControllerJGC::class, 'register']);

        Route::post('login', [AuthControllerJGC::class, 'login']);
    });

    Route::group(['prefix' => 'questions'], function () {  //
        Route::post('retrieve', [QuestionsController::class, 'getQuestions'])->middleware('auth:sanctum');
        Route::post('submit', [QuestionsController::class, 'submitAnswers'])->middleware('auth:sanctum');
        Route::post('upload', [QuestionsController::class, 'upload']);
    });


    Route::get('users/list', [AuthControllerJGC::class, 'usersList']); //->middleware('auth:sanctum')

   
    
});