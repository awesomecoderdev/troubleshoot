<?php

use App\Http\Controllers\Api\V1\AddressController;
use App\Http\Controllers\Api\V1\Auth\CustomerController;
use App\Http\Controllers\Api\V1\Auth\HandymanController;
use App\Http\Controllers\Api\V1\Auth\ProviderController;
use App\Http\Controllers\Api\V1\Auth\UserController;
use App\Http\Controllers\Api\V1\CampaignController;
use App\Http\Controllers\Api\V1\CampaignZoneController;
use App\Http\Controllers\Api\V1\CategoryController;
use App\Http\Controllers\Api\V1\CsrfTokenController;
use App\Http\Controllers\Api\V1\ProviderServiceController;
use App\Http\Controllers\Api\V1\ServiceController;
use App\Http\Controllers\Api\V1\ZoneController;
use App\Http\Controllers\ProviderHandymanController;
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



// V1 Base Route.
Route::any('/', function (Request $request) {
    return response()->json([
        "success" => true,
        "status" => 200,
        "message" => "Troubleshoot API Version V0.1"
    ]);
});

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

            // address
            Route::get('/address', [AddressController::class, 'index'])->name("address");
            Route::post('/address/update', [AddressController::class, 'update'])->name("address.update");

            // booking
            Route::get('/address', [AddressController::class, 'index'])->name("address");
            Route::post('/address/update', [AddressController::class, 'update'])->name("address.update");
        });
    });

    // Handyman Routes
    Route::group(['prefix' => 'handyman', 'as' => 'handyman.', "controller" => HandymanController::class], function () {
        // guest route
        Route::middleware(['handyman:false'])->group(function () {
            Route::post('/login', 'login')->name("login");
            Route::post('/register', 'register')->middleware("provider")->name("register");
            Route::post('/update', 'update')->middleware("provider")->name("update");
            Route::post('/delete', 'delete')->middleware("provider")->name("delete");
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

            //handyman
            Route::get('/handyman', [ProviderHandymanController::class, 'handyman'])->name("handyman");
        });
    });
});

// Services routes
Route::group(["as" => "service.", 'prefix' => 'service', "controller" => ServiceController::class], function () {
    Route::get('/', 'index')->name("index");
    Route::get('/{service}', 'show')->name("show");
    Route::post('/review/{service}', 'review')->middleware("customer")->name("review");
});

// Campaigns routes
Route::group(["as" => "campaign.", 'prefix' => 'campaign', "controller" => CampaignController::class], function () {
    Route::get('/', 'index')->name("index");
    Route::get('/{campaign}', 'show')->name("show");
    // Route::get('/service/{campaign}', 'service')->name("service");
});

// Categories routes
Route::group(["as" => "categories.", "controller" => CategoryController::class], function () {
    Route::resource('categories', CategoryController::class)->only(['index', 'show']);
    Route::get('/subcategories/{category}', 'subcategories')->name("subcategories");
});

// Zone routes
Route::group(["as" => "zone.", "controller" => ZoneController::class], function () {
    Route::get('/zone', 'zone')->name("zone");
});
