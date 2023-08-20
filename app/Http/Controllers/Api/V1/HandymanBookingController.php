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

    /**
     * Retrieve booking info.
     */
    public function booking(Request $request)
    {

        try {
            // Get the user's id from token header and get his provider bookings
            // status
            $params = Arr::only($request->input(), ["query", "zone_id", "per_page"]);
            $handyman = $request->user("handymans");
            $handyman->load("provider");
            $bookings = Booking::with([
                "provider",
                "category",
                "handyman",
                "service",
                "zone",
                "campaign",
                "coupon",
                "customer",
            ])->where("handyman_id", $handyman->id)->where("provider_id", $handyman?->provider?->id)->paginate($request->input("per_page", 10))->onEachSide(-1)->appends($params);

            return Response::json([
                'success'   => true,
                'status'    => HTTP::HTTP_OK,
                'message'   => "Successfully authorized.",
                'data'   => [
                    "bookings" => $bookings,
                ]
            ],  HTTP::HTTP_OK); // HTTP::HTTP_OK
        } catch (\Exception $e) {
            //throw $e;
            return Response::json([
                'success'   => false,
                'status'    => HTTP::HTTP_FORBIDDEN,
                'message'   => "Something went wrong. Try after sometimes.",
                'err' => $e->getMessage(),
            ],  HTTP::HTTP_FORBIDDEN); // HTTP::HTTP_OK
        }
    }


    /**
     * Retrieve update info.
     */
    public function request(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'booking_id' => 'required|integer|exists:bookings,id',
        ]);

        if ($validator->fails()) {
            return Response::json([
                'success'   => false,
                'status'    => HTTP::HTTP_UNPROCESSABLE_ENTITY,
                'message'   => "Validation failed.",
                'errors' => $validator->errors()
            ],  HTTP::HTTP_UNPROCESSABLE_ENTITY); // HTTP::HTTP_OK
        }

        try {
            // Get the user's id from token header and get his provider bookings
            $handyman = $request->user("handymans");
            $handyman->load("provider");
            $booking = Booking::where("id", $request->booking_id)->where("provider_id", $handyman?->provider?->id)->firstOrFail();

            if ($booking->status != "progressing") {
                return Response::json([
                    'success'   => false,
                    'status'    => HTTP::HTTP_FORBIDDEN,
                    'message'   => "Your requested booking is $booking->status.",
                ],  HTTP::HTTP_FORBIDDEN); // HTTP::HTTP_OK
            }

            $booking->status = "progressed";
            $booking->save();

            return Response::json([
                'success'   => true,
                'status'    => HTTP::HTTP_OK,
                'message'   => "Successfully sent a request to customer.",
                // "data"      => [
                //     "booking" => $booking
                // ]
            ],  HTTP::HTTP_OK); // HTTP::HTTP_OK
        } catch (\Exception $e) {
            throw $e;
            // return Response::json([
            //     'success'   => false,
            //     'status'    => HTTP::HTTP_FORBIDDEN,
            //     'message'   => "Something went wrong. Try after sometimes.",
            //     'err' => $e->getMessage(),
            // ],  HTTP::HTTP_FORBIDDEN); // HTTP::HTTP_OK
        }
    }
}
