<?php

namespace App\Http\Controllers\Api\V1;

use Carbon\Carbon;
use App\Models\Zone;
use App\Models\Service;
use App\Models\Campaign;
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
use App\Http\Resources\Api\V1\ZoneResource;
use App\Models\Category;

class CampaignController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'category_id' => 'integer|exists:categories,id',
            'zone_id' => 'integer|exists:zones,id',
            'type' => 'string|in:category,service',
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
            $today = Carbon::now()->endOfDay();
            // $campaigns = Campaign::where('end', '>=', $today)->paginate(10)->onEachSide(-1);
            $campaigns = Campaign::when($request->category_id != null, function ($query) use ($request) {
                return $query->where("category_id", $request->category_id);
            })->when($request->zone_id != null, function ($query) use ($request) {
                return $query->where("zone_id", $request->zone_id);
            })->when($request->type != null, function ($query) use ($request) {
                return $query->where("type", $request->type);
            })->where('end', '>=', $today)->get();


            if ($request->type == "category") {
                $categories_ids = array_unique($campaigns->pluck("category_id")->toArray());
                $categories = Category::whereIn('id', $categories_ids)->where("is_active", true)->get();
                // $services = Service::whereIn('category_id', $categories_ids)
                //     ->get();
                return Response::json([
                    'success'   => true,
                    'status'    => HTTP::HTTP_OK,
                    'message'   => "Successfully authorized.",
                    'data'      => [
                        'campaigns'  => $campaigns,
                        "categories" => $categories,
                        // 'services' => $services,
                    ]
                ],  HTTP::HTTP_OK); // HTTP::HTTP_OK
            }

            return Response::json([
                'success'   => true,
                'status'    => HTTP::HTTP_OK,
                'message'   => "Successfully authorized.",
                'data'      => [
                    'campaigns'  => $campaigns,
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
        $today = Carbon::now();

        // if (in_array($request->campaign, ["service", "category"])) {

        //     if (in_array($request->campaign, ["service", "category"])) {
        //         if ($request->campaign == "service") {
        //             $validator = Validator::make($request->all(), [
        //                 'campaign_id' => 'required|integer|exists:campaigns,id',
        //                 'zone_id' => 'integer|exists:zones,id',
        //             ]);
        //         } else {
        //             $validator = Validator::make($request->all(), [
        //                 'campaign_id' => 'integer|exists:campaigns,id',
        //                 'category_id' => 'required|integer|exists:categories,id',
        //                 'zone_id' => 'integer|exists:zones,id',
        //             ]);
        //         }

        //         if ($validator->fails()) {
        //             return Response::json([
        //                 'success'   => false,
        //                 'status'    => HTTP::HTTP_UNPROCESSABLE_ENTITY,
        //                 'message'   => "Validation failed.",
        //                 'errors' => $validator->errors()
        //             ],  HTTP::HTTP_UNPROCESSABLE_ENTITY); // HTTP::HTTP_OK
        //         }
        //     }

        //     if ($request->campaign == "service") {
        //         $campaign = Campaign::where("type", "service")->where("id", $request->campaign_id)->where('end', '>=', $today)
        //             ->when($request->zone_id != null, function ($query) use ($request) {
        //                 return $query->where("zone_id", $request->zone_id);
        //             })
        //             ->first();
        //         $service = isset($campaign->service_id) ?
        //             Service::where("id", $campaign->service_id)->when($request->zone_id != null, function ($query) use ($campaign) {
        //                 return $query->where("zone_id", $campaign->zone_id);
        //             })->get()
        //             : [];
        //     } else if ($request->campaign == "category") {
        //         $campaign = Campaign::when($request->category_id != null, function ($query) use ($request) {
        //             return $query->where("category_id", $request->category_id);
        //         })->when($request->zone_id != null, function ($query) use ($request) {
        //             return $query->where("zone_id", $request->zone_id);
        //         })->when($request->campaign_id != null, function ($query) use ($request) {
        //             return $query->where("campaign_id", $request->campaign_id);
        //         })->where("type", "category")->where('end', '>=', $today)->first();

        //         $service = isset($campaign->category_id) ?
        //             Service::where("category_id", $campaign->category_id)->when($request->zone_id != null, function ($query) use ($campaign) {
        //                 return $query->where("zone_id", $campaign->zone_id);
        //             })->get()
        //             : [];
        //     } else {
        //         return Response::json([
        //             'success'   => false,
        //             'status'    => HTTP::HTTP_FORBIDDEN,
        //             'message'   => "You are not allowed.",
        //         ],  HTTP::HTTP_FORBIDDEN); // HTTP::HTTP_OK
        //     }
        // } else {
        //     $campaign = Campaign::when($request->category_id != null, function ($query) use ($request) {
        //         return $query->where("category_id", $request->category_id);
        //     })->when($request->zone_id != null, function ($query) use ($request) {
        //         return $query->where("zone_id", $request->zone_id);
        //     })->where('end', '>=', $today)->where("id", $request->campaign)->first();

        //     $service = isset($campaign->service_id) ?
        //         Service::where("id", $campaign->service_id)->when($request->zone_id != null, function ($query) use ($campaign) {
        //             return $query->where("zone_id", $campaign->zone_id);
        //         })->get()
        //         : [];
        // }
        $campaign = Campaign::when($request->category_id != null, function ($query) use ($request) {
            return $query->where("category_id", $request->category_id);
        })->when($request->zone_id != null, function ($query) use ($request) {
            return $query->where("zone_id", $request->zone_id);
        })->where('end', '>=', $today)->where("id", $request->campaign)->first();

        $service = isset($campaign->service_id) ?
            Service::where("id", $campaign->service_id)->when($request->zone_id != null, function ($query) use ($campaign) {
                return $query->where("zone_id", $campaign->zone_id);
            })->get()
            : [];

        try {
            return Response::json([
                'success'   => true,
                'status'    => HTTP::HTTP_OK,
                'message'   => "Successfully authorized.",
                'data'      => [
                    'campaign'  => $campaign,
                    'service'  => $service,
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
     * Display the specified resource based on zone.
     */
    public function zone(Zone $zone)
    {
        try {
            $zone->load("campaigns");
            return Response::json([
                'success'   => true,
                'status'    => HTTP::HTTP_OK,
                'message'   => "Successfully authorized.",
                'data'      => [
                    'zone'  => new ZoneResource($zone),
                ]
            ],  HTTP::HTTP_OK); // HTTP::HTTP_OK
        } catch (\Exception $e) {
            throw $e;
            return Response::json([
                'success'   => false,
                'status'    => HTTP::HTTP_FORBIDDEN,
                'message'   => "Something went wrong. Try after sometimes.",
                'err' => $e->getMessage(),
            ],  HTTP::HTTP_FORBIDDEN); // HTTP::HTTP_OK
        }
    }

    /**
     * Display the specified resource based on zone.
     */
    public function service(Service $service)
    {
        $service->load(["provider", "reviews"]);
        try {
            return Response::json([
                'success'   => true,
                'status'    => HTTP::HTTP_OK,
                'message'   => "Successfully authorized.",
                'data'      => [
                    "service"   =>  $service
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
}
