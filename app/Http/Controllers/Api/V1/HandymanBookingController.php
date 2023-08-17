<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Service;
use App\Models\Handyman;
use Illuminate\Support\Arr;
use Illuminate\Http\Request;
use App\Events\RegisteredHandyman;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Response as HTTP;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;
use App\Http\Requests\StoreHandymanRequest;
use App\Http\Requests\UpdateHandymanRequest;
use App\Http\Resources\Api\V1\HandymanResource;
use App\Http\Requests\Api\V1\HandymanLoginRequest;
use App\Models\Booking;

class HandymanBookingController extends Controller
{
    /**
     * Retrieve handymans info.
     */
    public function service(Request $request)
    {
        $handyman = $request->user("handymans");

        try {
            $bookings = Booking::where('handyman_id', $handyman->id)->paginate($request->input("per_page", 10))->onEachSide(-1);
            return Response::json([
                'success'   => true,
                'status'    => HTTP::HTTP_OK,
                'message'   => "Successfully authorized.",
                'data'      => [
                    'bookings'  => $bookings,
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
