<?php

namespace App\Http\Controllers\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Response as HTTP;
use Illuminate\Support\Facades\Response;
use App\Http\Resources\Api\V1\HandymanResource;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreScheduleRequest;
use App\Http\Requests\UpdateScheduleRequest;
use App\Models\Schedule;

class ProviderHandymanController extends Controller
{
    /**
     * Retrieve handymans info.
     */
    public function handyman(Request $request)
    {
        try {
            return Response::json([
                'success'   => true,
                'status'    => HTTP::HTTP_OK,
                'message'   => "Successfully authorized.",
                'data'   => [
                    "handyman" => $request->user("providers")->handyman
                ]
            ],  HTTP::HTTP_OK); // HTTP::HTTP_OK
        } catch (\Exception $e) {
            //throw $e;
            return Response::json([
                'success'   => false,
                'status'    => HTTP::HTTP_FORBIDDEN,
                'message'   => "Something went wrong. Try after sometimes.",
                // 'err' => $e->getMessage(),
            ],  HTTP::HTTP_FORBIDDEN); // HTTP::HTTP_OK
        }
    }
}
