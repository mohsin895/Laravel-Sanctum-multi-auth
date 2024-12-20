<?php

use App\Http\Middleware\Admin;
use App\Http\Middleware\User;
use App\Http\Middleware\UserMiddleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


Route::post('v1/registration', [\App\Http\Controllers\Backend\Api\AuthController::class, 'signup']);
Route::post('v1/verify/otp', [\App\Http\Controllers\Backend\Api\AuthController::class, 'verifyOtp']);
Route::post('v1/verify/email', [\App\Http\Controllers\Backend\Api\AuthController::class, 'verifyEmail']);
Route::post('v1/login', [\App\Http\Controllers\Backend\Api\AuthController::class,'login']);
Route::post('v1/forget/password', [App\Http\Controllers\Backend\Api\AuthController::class,'forgetPassword']);
Route::post('v1/otp/verify/forget/password', [App\Http\Controllers\Backend\Api\AuthController::class,'verifyOtpForgetPass']);
Route::post('v1/reset/password', [App\Http\Controllers\Backend\Api\AuthController::class,'resetPass']);


Route::middleware('auth:sanctum', Admin::class)->group(function () {
});

Route::middleware( UserMiddleware::class)->group(function () {
    Route::group(['prefix'=>'v1'],function(){
        Route::group(['prefix'=>'user'],function(){
            Route::get('/',[\App\Http\Controllers\Api\User\UserController::class,'info']);
            Route::post('/update/info',[\App\Http\Controllers\Api\User\UserController::class,'updateInfo']);
            Route::get('/logout',[\App\Http\Controllers\Api\User\UserController::class,'logout']);

            Route::group(['prefix'=>'product'],function(){
                Route::get('/get/list', [App\Http\Controllers\Api\User\ProductController::class, 'index']);
                Route::post('/insert/update', [App\Http\Controllers\Api\User\ProductController::class, 'dataInfoAddOrUpdate']);
                Route::delete('/delete/{id}', [App\Http\Controllers\Api\User\ProductController::class, 'dataInfoDelete']);
                Route::get('/info/{dataId}', [App\Http\Controllers\Api\User\ProductController::class, 'dataInfo']);
                Route::post('/selling/status/{dataId}', [App\Http\Controllers\Api\User\ProductController::class, 'updateSellingStatus']);
               
                Route::group(['prefix'=>'rating'],function(){
               
                    Route::post('/insert/update', [App\Http\Controllers\Api\User\RatingController::class, 'dataInfoAddOrUpdate']);

                });

                Route::group(['prefix'=>'error'],function(){
               
                    Route::post('/insert/update', [App\Http\Controllers\Api\User\ErrorController::class, 'dataInfoAddOrUpdate']);

                });
                Route::group(['prefix'=>'buyer'],function(){
               
                    Route::post('/insert/update', [App\Http\Controllers\Api\User\BuyerController::class, 'dataInfoAddOrUpdate']);

                });
           
            });
           
           
        });
       
    });
});

Route::fallback(function () {
    return response()->json([
        'message' => 'Route not found. Please check the URL and try again.'
    ], 404);
});


