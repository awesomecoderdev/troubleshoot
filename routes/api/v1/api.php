<?php

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


Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::any('/', function (Request $request) {
    return response()->json([
        "status" => "Hello world",
        "request" => $request->all(),
        "csrf" => $request->session()->token()
    ]);
});
