<?php

use App\Http\Controllers\Api\V1\Auth\CustomerController;
use App\Http\Controllers\Api\V1\Auth\HandymanController;
use App\Http\Controllers\Api\V1\Auth\UserController;
use App\Http\Controllers\Api\V1\CsrfTokenController;
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

// Refresh the csrf token.
Route::any('/refresh-csrf', [CsrfTokenController::class, 'refresh'])->name('csrf.refresh');


// Guest routes
Route::group(['prefix' => 'auth', "middleware" => "guest"], function () {

    // Users Routes
    // Route::group(['prefix' => 'user', 'as' => 'user', "controller" => UserController::class], function () {
    //     Route::post('/register', 'register')->name("register");
    // });

    // Customer Routes
    Route::group(['prefix' => 'customer', 'as' => 'customer', "controller" => CustomerController::class], function () {
        // guest route
        Route::middleware(['customer:false'])->group(function () {
            Route::post('/login', 'login')->name("login");
            Route::post('/register', 'register')->name("register");
        });

        // authorization route
        Route::middleware(['customer'])->group(function () {
            Route::get('/', 'customer')->name("customer");
        });
    });

    // Handyman Routes
    Route::group(['prefix' => 'handyman', 'as' => 'handyman', "controller" => HandymanController::class], function () {
        Route::post('/register', 'register')->name("register");
    });
});




// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });

Route::any('/', function (Request $request) {
    return response()->json([
        "status" => "Hello world",
        "request" => $request->all(),
        "csrf" => $request->session()->token()
    ]);
});
