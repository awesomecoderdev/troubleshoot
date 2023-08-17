<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Booking;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreBookingRequest;
use App\Models\Service;
use Illuminate\Http\Response as HTTP;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;

class CustomerBookingController extends Controller
{
    /**
     * Retrieve booking info.
     */
    public function booking(Request $request)
    {
        try {
            // Get the user's id from token header and get his provider bookings
            // status
            $customer = $request->user("customers");
            $bookings = Booking::with([
                "provider",
                "category",
                "handyman",
                "service",
                "zone",
                "campaign",
                "coupon",
                "customer",
            ])->where("customer_id", $customer->id)->get();

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
            "status" => 'required|in:cancelled,completed'
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
            // Get the user's id from token header and get his customer bookings
            $customer = $request->user("customers");
            $booking = Booking::where("id", $request->booking_id)->where("status", "pending")->where("customer_id", $customer->id)->firstOrFail();

            $booking->status = $request->status;
            $booking->save();

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
     * Register Booking
     */
    public function register(StoreBookingRequest $request)
    {
        try {
            $customer = $request->user("customers");
            $customer->load("address");
            $service = Service::where("id", $request->service_id)->where("status", true)->firstOrFail();

            // need to calculate total tax etc
            $total_amount = intval($service->price);
            $total_tax = intval($service->tax);
            $total_discount = intval($service->discount);
            $additional_charge = intval(0);

            // new booking
            $booking = new Booking();
            $booking->customer_id = $customer->id;
            $booking->address_id = $customer->address->id;
            $booking->service_id = $service->id;
            $booking->provider_id = $service->provider_id;
            $booking->title = $request->title;
            $booking->hint = $request->hint;

            $booking->coupon_id = $request->input("coupon_id", 0);
            $booking->campaign_id = $request->input("campaign_id", 0);
            // $booking->handyman_id = $request->input("handyman_id", 0);
            $booking->category_id = $service->category_id;
            $booking->zone_id = $service->zone_id;
            $booking->payment_method = $request->payment_method;
            // $booking->status = $request->status;
            // $booking->is_paid = $request->is_paid;
            // $booking->is_paid = $request->is_paid;
            $booking->total_amount = $total_amount;
            $booking->total_tax = $total_tax;
            $booking->total_discount = $total_discount;
            $booking->additional_charge = $additional_charge;
            $booking->is_rated = false; // that mean booking is not given rating
            $booking->save();

            return Response::json([
                'success'   => true,
                'status'    => HTTP::HTTP_CREATED,
                'message'   => "Booking successfully created.",
                // "data"      => [
                // "customer" => $customer,
                // "booking" => $booking,
                // "service" => $service
                // ]
            ],  HTTP::HTTP_CREATED); // HTTP::HTTP_OK
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
