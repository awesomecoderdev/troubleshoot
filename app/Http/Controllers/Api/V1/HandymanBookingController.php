<?php

namespace App\Http\Controllers\Api\V1;

use Carbon\Carbon;
use App\Models\Booking;
use App\Models\Service;
use App\Models\Handyman;
use App\Models\Schedule;
use Illuminate\Support\Arr;
use Illuminate\Http\Request;
use App\Events\RegisteredHandyman;
use Illuminate\Support\Facades\DB;
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
     * Retrieve update info.
     */
    public function details(Request $request)
    {
        try {
            // Get the user's id from token header and get his provider bookings
            $handyman = $request->user("handymans");
            // $booking = Booking::where("id", $request->input("booking", 0))->where('handyman_id', $handyman->id)->firstOrFail();
            $booking = Booking::with([
                "provider",
                "category",
                // "subcategory",
                "handyman",
                "service",
                "zone",
                "campaign",
                "coupon",
                "customer",
                "schedules"
            ])->where("id", $request->booking)->where('handyman_id', $handyman->id)->firstOrFail();
            return Response::json([
                'success'   => true,
                'status'    => HTTP::HTTP_OK,
                'message'   => "Successfully authorized.",
                'data'      => [
                    'bookings'  => $booking,
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
                // "subcategory",
                "handyman",
                "service",
                "zone",
                "campaign",
                "coupon",
                "customer",
                "schedules"
            ])->where("handyman_id", $handyman->id)
                ->where("provider_id", $handyman?->provider?->id)
                ->when($request->status != null && in_array($request->status, ["pending", "accepted", "rejected", "progressing", "progressed", "cancelled", "completed"]), function ($query) use ($request) {
                    return $query->where("status", strtolower($request->status));
                })->orderBy("id", "DESC")->paginate($request->input("per_page", 10))->onEachSide(-1)->appends($params);

            $completed = Booking::select("id")->where("status", "completed")->where("handyman_id", $handyman->id)->where("provider_id", $handyman?->provider?->id)->get();
            return Response::json([
                'success'   => true,
                'status'    => HTTP::HTTP_OK,
                'message'   => "Successfully authorized.",
                'data'   => [
                    "bookings" => $bookings,
                    "completed" => $completed
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

    /**
     * Retrieve update info.
     */
    public function schedule(Request $request)
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

            $booking = Booking::with([
                "provider",
                "category",
                "handyman",
                "service",
                "zone",
                "campaign",
                "coupon",
                "customer",
            ])->where("id", $request->booking_id)
                ->where("handyman_id", $handyman->id)
                ->where("provider_id", $handyman?->provider?->id)
                ->firstOrFail();


            $schedules = Schedule::where("booking_id", $booking->id)->where("handyman_id", $handyman->id)->get();


            return Response::json([
                'success'   => true,
                'status'    => HTTP::HTTP_OK,
                'message'   => "Successfully Authorized.",
                "data"      => [
                    "schedules" => $schedules,
                    // "booking" => $booking,
                    "duration" => $this->duration($schedules)
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
    public function duration($schedules)
    {
        try {
            $totalDuration = $schedules->map(function ($schedule) {
                $start = Carbon::parse($schedule->start);
                $end = Carbon::parse($schedule->end);
                $diff = $start->diff($end);
                return $diff->format('%H:%I:%S');
            })->reduce(function ($carry, $duration) {
                $timeParts = explode(':', $duration);
                $carry[0] += (int) $timeParts[0]; // hours
                $carry[1] += (int) $timeParts[1]; // minutes
                $carry[2] += (int) $timeParts[2]; // seconds
                // Adjust carry values if seconds/minutes exceed 60
                $carry[1] += floor($carry[2] / 60);
                $carry[2] = $carry[2] % 60;
                $carry[0] += floor($carry[1] / 60);
                $carry[1] = $carry[1] % 60;
                return $carry;
            }, ["00", "00", "00"]);

            list($hours, $minutes, $seconds) = $totalDuration;
            return "$hours:$minutes:$seconds";
        } catch (\Exception $e) {
            //throw $e;
            return "00:00:00";
        }
    }


    /**
     * Retrieve update info.
     */
    public function start(Request $request)
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


            DB::beginTransaction();

            // Get the user's id from token header and get his provider bookings
            $handyman = $request->user("handymans");
            $booking = Booking::where("id", $request->booking_id)
                ->where("handyman_id", $handyman->id)
                ->where("provider_id", $handyman?->provider?->id)
                ->firstOrFail();
            $schedules = Schedule::where("end", null)->where("handyman_id", $handyman->id)->orderBy("id", "DESC")->get();
            if ($schedules->count() > 0) {
                return Response::json([
                    'success'   => false,
                    'status'    => HTTP::HTTP_FORBIDDEN,
                    'message'   => "You can't start multiple schedule at same time.",
                ],  HTTP::HTTP_FORBIDDEN); // HTTP::HTTP_OK
            }

            $schedule = new Schedule();
            $schedule->handyman_id = $handyman->id;
            $schedule->booking_id = $booking->id;
            $schedule->date = Carbon::now();
            $schedule->start = Carbon::now();
            $schedule->save();

            // end transaction
            DB::commit();



            $schedules = Schedule::where("booking_id", $booking->id)->where("handyman_id", $handyman->id)->get();

            return Response::json([
                'success'   => true,
                'status'    => HTTP::HTTP_CREATED,
                'message'   => "Schedule successfully started.",
                "data"      => [
                    // "schedules" => $schedule,
                    // "booking" => $booking
                    "duration" => $this->duration($schedules)
                ]
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

    /**
     * Retrieve update info.
     */
    public function stop(Request $request)
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


            DB::beginTransaction();

            // Get the user's id from token header and get his provider bookings
            $handyman = $request->user("handymans");
            $booking = Booking::where("id", $request->booking_id)
                ->where("handyman_id", $handyman->id)
                ->where("provider_id", $handyman?->provider?->id)
                ->firstOrFail();
            $schedule = Schedule::where("booking_id", $booking->id)->where("handyman_id", $handyman->id)->orderBy("id", "DESC")->first();

            if ($schedule && $schedule->end != null) {
                return Response::json([
                    'success'   => false,
                    'status'    => HTTP::HTTP_FORBIDDEN,
                    'message'   => "Schedule already is completed.",
                ],  HTTP::HTTP_FORBIDDEN); // HTTP::HTTP_OK
            }

            $schedule->end = Carbon::now();
            $schedule->save();

            // end transaction
            DB::commit();


            $schedules = Schedule::where("booking_id", $booking->id)->where("handyman_id", $handyman->id)->get();

            return Response::json([
                'success'   => true,
                'status'    => HTTP::HTTP_OK,
                'message'   => "Schedule successfully stopped.",
                "data"      => [
                    // "schedules" => $schedule,
                    // "booking" => $booking,
                    "duration" => $this->duration($schedules)
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
