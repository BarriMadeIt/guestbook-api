<?php

use App\Http\Controllers\LoginController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\ReplyController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// Public routes
Route::post('/login', LoginController::class);
Route::post('/register', RegisterController::class);

Route::middleware('auth:sanctum')->group(function () {
    Route::prefix('messages')->group(function () {
        Route::post('/add', [MessageController::class, 'store']);
        Route::get('/list', [MessageController::class, 'list']);
        Route::get('/list-all', [MessageController::class, 'listAll'])->middleware('admin');
        Route::get('/show/{id}', [MessageController::class, 'show']);
        Route::put('/update/{id}', [MessageController::class, 'update']);
        Route::delete('/archive/{id}', [MessageController::class, 'archive']);
    });

    Route::middleware('message.required')->group(function () {
        Route::prefix('replies')->group(function () {
            Route::post('/add', [ReplyController::class, 'store']);
            Route::get('/list', [ReplyController::class, 'list']);
            Route::put('/update/{replyId}', [ReplyController::class, 'update']);
            Route::delete('/archive/{replyId}', [ReplyController::class, 'archive']);
        });
    });
});
