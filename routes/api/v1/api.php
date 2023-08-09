<?php

use App\Http\Controllers\Api\V1\Auth\CustomerController;
use App\Http\Controllers\Api\V1\Auth\HandymanController;
use App\Http\Controllers\Api\V1\Auth\ProviderController;
use App\Http\Controllers\Api\V1\Auth\UserController;
use App\Http\Controllers\Api\V1\CsrfTokenController;
use App\Http\Controllers\Api\V1\ProviderServiceController;
use App\Http\Controllers\Api\V1\ServiceController;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;

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
    Route::group(['prefix' => 'customer', 'as' => 'customer.', "controller" => CustomerController::class], function () {
        // guest route
        Route::middleware(['customer:false'])->group(function () {
            Route::post('/login', 'login')->name("login");
            Route::post('/register', 'register')->name("register");
        });

        // authorization route
        Route::middleware(['customer'])->group(function () {
            Route::get('/', 'customer')->name("customer");
            Route::post('/logout', 'logout')->name("logout");
            Route::post('/update', 'update')->name("update");
            Route::post('/delete', 'delete')->name("delete");

            // otp routes
            Route::post('/check-otp', 'validateOtp');
            Route::post('/re-otp', 'regenerateOTP');
        });
    });

    // Handyman Routes
    Route::group(['prefix' => 'handyman', 'as' => 'handyman.', "controller" => HandymanController::class], function () {
        // guest route
        Route::middleware(['handyman:false'])->group(function () {
            Route::post('/login', 'login')->name("login");
            Route::post('/register', 'register')->name("register");
        });

        // authorization route
        Route::middleware(['handyman'])->group(function () {
            Route::get('/', 'handyman')->name("handyman");
            Route::post('/logout', 'logout')->name("logout");
        });
    });

    // Provider Routes
    Route::group(['prefix' => 'provider', 'as' => 'provider.', "controller" => ProviderController::class], function () {
        // guest route
        Route::middleware(['provider:false'])->group(function () {
            Route::post('/login', 'login')->name("login");
            Route::post('/register', 'register')->name("register");
        });

        // authorization route
        Route::middleware(['provider'])->group(function () {
            Route::get('/', 'provider')->name("provider");
            Route::post('/logout', 'logout')->name("logout");

            // provider services crud route
            Route::resource('service', ProviderServiceController::class)->except(['create', 'edit']);
        });
    });
});


// Services routes
Route::group(["as" => "service."], function () {
    Route::get('/services', [ServiceController::class, 'index'])->name("index");
});


Route::any('/', function (Request $request) {
    return response()->json([
        "status" => "Hello world",
        "request" => $request->all(),
        "csrf" => $request->session()->token()
    ]);
});
