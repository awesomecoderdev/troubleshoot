<?php

namespace App\Http\Controllers\Api\V1;

use Carbon\Carbon;
use App\Models\Zone;
use App\Models\Review;
use App\Models\Service;
use Illuminate\Support\Arr;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Http\Response as HTTP;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;
use App\Http\Requests\StoreServiceRequest;
use App\Http\Requests\UpdateServiceRequest;
use App\Http\Resources\Api\V1\ServiceResource;
use App\Models\Booking;

class ServiceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {

        $category = $request->category_id;
        $zone = $request->zone_id;
        $validator = Validator::make($request->all(), [
            'category_id' => 'required|integer|exists:categories,id',
            'zone_id' => 'required|integer',
            'per_page' => 'integer',
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
            $params = Arr::only($request->input(), ["category_id", "zone_id"]);
            $services = Service::with(["provider", "category", "zone"])->where('category_id', $category)->where('zone_id', $zone)->orderBy("id", "DESC")->paginate($request->input("per_page", 10))->onEachSide(-1)->appends($params);
            return Response::json([
                'success'   => true,
                'status'    => HTTP::HTTP_OK,
                'message'   => "Successfully authorized.",
                'data'      => [
                    'services'  => $services,
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
     * Display the specified resource.
     */
    public function show(Request $request)
    {
        // get the search, popular, recommended services
        if (in_array($request->service, ["search", "popular", "recommended"])) {
            $zone = $request->zone_id;
            $search = $request->input("query");
            $params = Arr::only($request->all(), ["query", "zone_id", "per_page"]);

            if ($request->service == "popular" || $request->service == "recommended") {
                $validator = Validator::make($request->all(), [
                    'zone_id' => 'required|integer',
                ]);
            } else {
                $validator = Validator::make($request->all(), [
                    'query' => 'required',
                    'zone_id' => 'required|integer',
                ]);
            }

            if ($validator->fails()) {
                return Response::json([
                    'success'   => false,
                    'status'    => HTTP::HTTP_UNPROCESSABLE_ENTITY,
                    'message'   => "Validation failed.",
                    'errors' => $validator->errors()
                ],  HTTP::HTTP_UNPROCESSABLE_ENTITY); // HTTP::HTTP_OK
            }


            try {

                if ($request->service == "recommended") {
                    $services = Service::with(["provider", "category", "zone"])->where('zone_id', $request->get('zone_id'))
                        ->orderBy('avg_rating', 'desc')
                        ->orderBy('created_at', 'desc')  // fallback to newest if ratings are equal
                        ->limit(10)
                        ->get();
                } elseif ($request->service == "popular") {
                    $services = Service::with(["provider", "category", "zone"])->where('zone_id', $request->input('zone_id'))
                        ->where('order_count', '>', 0)  // you might want to adjust this number
                        ->orderBy('order_count', 'desc')
                        ->limit(10)
                        ->get();
                } else {
                    $services = Service::where('zone_id', $zone)->with(["provider", "category", "zone"])
                        ->where('name', 'like', "%{$search}%")
                        ->orWhere('short_description', 'like', "%{$search}%")
                        ->orWhere('long_description', 'like', "%{$search}%")
                        ->paginate($request->input("per_page", 10))->onEachSide(-1)->appends($params);
                }

                return Response::json([
                    'success'   => true,
                    'status'    => HTTP::HTTP_OK,
                    'message'   => "Successfully authorized.",
                    'data'      => [
                        'services'  => $services
                    ]
                ],  HTTP::HTTP_OK); // HTTP::HTTP_OK
            } catch (\Exception $e) {
                // throw $e;
                return Response::json([
                    'success'   => false,
                    'status'    => HTTP::HTTP_FORBIDDEN,
                    'message'   => "Something went wrong. Try after sometimes.",
                    'err' => $e->getMessage(),
                ],  HTTP::HTTP_FORBIDDEN); // HTTP::HTTP_OK
            }
        } else {
            try {
                $service = Service::with(["provider", "reviews", "category", "zone"])->where("id", $request->service)->firstOrFail();
                return Response::json([
                    'success'   => true,
                    'status'    => HTTP::HTTP_OK,
                    'message'   => "Successfully authorized.",
                    'data'      => [
                        'service'  => $service
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


    /**
     * Display the specified resource.
     */
    public function details(Service $service)
    {

        try {
            $service->load(["provider", "reviews"]);
            return Response::json([
                'success'   => true,
                'status'    => HTTP::HTTP_OK,
                'message'   => "Successfully authorized.",
                'data'      => [
                    'service'  => $service
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
     * Display the specified resource.
     */
    public function review(Service $service, Request $request)
    {
        $customer = $request->user('customers');

        $validator = Validator::make($request->all(), [
            'rating' => 'required|integer|between:1,5',
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

        $booking = Booking::where("id", $request->booking_id)->first();

        if ($booking->is_rated) {
            return Response::json([
                'success'   => false,
                'status'    => HTTP::HTTP_FORBIDDEN,
                'message'   => "You can't review to this booking.",
            ],  HTTP::HTTP_FORBIDDEN); // HTTP::HTTP_OK
        }

        try {
            // Update the rating_count and avg_rating
            $provider = $booking->provider;
            $provider->rating_count = $provider->rating_count + 1;
            $provider->save();

            // If the rating_count is 0, set the avg_rating to the new rating value
            $newRatingCount = $service->rating_count + 1;
            if ($service->rating_count == 0) {
                $newAvgRating = $request->rating;
            } else {
                $newTotalRating = $service->avg_rating * $service->rating_count + $request->rating;
                $newAvgRating = $newTotalRating / $newRatingCount;
            }

            Review::create([
                'booking_id' => $request->input('booking_id'),
                'service_id' => $service->id,
                'provider_id' => $service->provider_id,
                'customer_name' => $customer->name(),
                'customer_id' => $customer->id,
                'review_rating' => $request->rating,
                'review_comment' => $request->input('review_comment'),
                'booking_date' => Carbon::now(),
            ]);

            // Update the service in the database
            $service->update([
                'rating_count' => $newRatingCount,
                'avg_rating' => $newAvgRating,
            ]);

            $booking->is_rated = true;
            $booking->save();

            return Response::json([
                'success'   => true,
                'status'    => HTTP::HTTP_OK,
                'message'   => "Thank you for your review.",
                'data'      => [
                    'service'  => $service
                ]
            ],  HTTP::HTTP_OK); // HTTP::HTTP_OK
        } catch (\Exception $e) {
            // throw $e;
            return Response::json([
                'success'   => false,
                'status'    => HTTP::HTTP_FORBIDDEN,
                'message'   => "Something went wrong. Try after sometimes.",
                'err' => $e->getMessage(),
            ],  HTTP::HTTP_FORBIDDEN); // HTTP::HTTP_OK
        }
    }
}
