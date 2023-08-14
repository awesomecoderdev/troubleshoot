<?php

namespace App\Models\Api\V1;

use App\Models\Booking;
use Illuminate\Http\Request;
use Illuminate\Http\Response as HTTP;
use Illuminate\Support\Facades\Response;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Validator;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ProviderBookingController extends Model
{
    use HasFactory;

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
                "coupon"
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
}
