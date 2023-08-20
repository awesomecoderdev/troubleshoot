<?php

namespace App\Http\Controllers\Api\V1;


use App\Models\Booking;
use App\Models\Handyman;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Response as HTTP;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;

class ProviderBookingController extends Controller
{

    /**
     * Retrieve booking info.
     */
    public function booking(Request $request)
    {
        try {
            // Get the user's id from token header and get his provider bookings
            // status
            $provider = $request->user("providers");
            $bookings = Booking::with([
                "provider",
                "category",
                "handyman",
                "service",
                "zone",
                "campaign",
                "coupon",
                "customer",
                "schedules"
            ])->where("provider_id", $provider->id)->get();

            return Response::json([
                'success'   => true,
                'status'    => HTTP::HTTP_OK,
                'message'   => "Successfully authorized.",
                'data'   => [
                    "bookings" => $bookings
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
     * Retrieve update info.
     */
    public function change(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'booking_id' => 'required|integer|exists:bookings,id',
            "status" => 'required|in:accepted,rejected'
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
            $provider = $request->user("providers");
            $booking = Booking::where("id", $request->booking_id)->where("provider_id", $provider->id)->firstOrFail();

            $booking->status = $request->status;
            $booking->save();

            return Response::json([
                'success'   => true,
                'status'    => HTTP::HTTP_OK,
                'message'   => "Successfully updated.",
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

    /**
     * Retrieve update info.
     */
    public function handover(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'booking_id' => 'required|integer|exists:bookings,id',
            "handyman_id" => 'required|exists:handymen,id'
        ]);

        // "available", "unavailable"
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
            $provider = $request->user("providers");
            // $booking = Booking::where("id", $request->booking_id)->where("status", "accepted")->where("handyman_id", 0)->where("provider_id", $provider->id)->firstOrFail();
            $booking = Booking::where("id", $request->booking_id)->where("provider_id", $provider->id)->firstOrFail();
            $handyman = Handyman::where("id", $request->handyman_id)->where("provider_id", $provider->id)->first();


            if (!$handyman) {
                return Response::json([
                    'success'   => false,
                    'status'    => HTTP::HTTP_NOT_FOUND,
                    'message'   => "Handyman Not Found.",
                ],  HTTP::HTTP_NOT_FOUND); // HTTP::HTTP_OK
            }

            if ($handyman->status == "unavailable") {
                return Response::json([
                    'success'   => false,
                    'status'    => HTTP::HTTP_FORBIDDEN,
                    'message'   => "Handyman Unavailable.",
                ],  HTTP::HTTP_FORBIDDEN); // HTTP::HTTP_OK
            }


            if ($booking->status != "accepted") {
                return Response::json([
                    'success'   => false,
                    'status'    => HTTP::HTTP_FORBIDDEN,
                    'message'   => "Booking status must be accepted.",
                ],  HTTP::HTTP_FORBIDDEN); // HTTP::HTTP_OK
            }

            if ($booking->handyman_id != 0) {
                return Response::json([
                    'success'   => false,
                    'status'    => HTTP::HTTP_FORBIDDEN,
                    'message'   => "This booking is already handover to $handyman->name.",
                ],  HTTP::HTTP_FORBIDDEN); // HTTP::HTTP_OK
            }

            $booking->handyman_id = $handyman->id;
            $booking->status = "progressing";
            $booking->save();

            $handyman->status = "unavailable";
            $handyman->save();

            return Response::json([
                'success'   => true,
                'status'    => HTTP::HTTP_OK,
                'message'   => "Successfully updated.",
                "data"      => [
                    "booking" => $booking
                ]
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

    /**
     * Retrieve update info.
     */
    public function details(Request $request)
    {
        try {
            // Get the user's id from token header and get his provider bookings
            $provider = $request->user("providers");
            $booking = Booking::with([
                "provider",
                "category",
                "handyman",
                "service",
                "zone",
                "campaign",
                "coupon",
                "customer",
            ])->where("id", $request->booking)->where("provider_id", $provider->id)->firstOrFail();

            return Response::json([
                'success'   => true,
                'status'    => HTTP::HTTP_OK,
                'message'   => "Successfully Authorized.",
                "data"      => [
                    "booking" => $booking
                ]
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
