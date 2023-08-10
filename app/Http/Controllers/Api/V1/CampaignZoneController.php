<?php

namespace App\Http\Controllers\Api\V1;

use Carbon\Carbon;
use App\Models\Campaign;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Http\Response as HTTP;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;
use App\Http\Requests\StoreServiceRequest;
use App\Http\Requests\UpdateServiceRequest;
use App\Http\Resources\Api\V1\ServiceResource;
use Illuminate\Support\Arr;

class CampaignZoneController extends Controller
{
    /**
     * Display the specified resource.
     */
    public function show(Request $request)
    {
        // get the search, popular, recommended services
        // if (in_array($request->service, ["search", "popular", "recommended"])) {
        //     $zone = $request->zone_id;
        //     $query = $request->query;
        //     $params = Arr::only($request->input(), ["query", "zone_id"]);

        //     if ($request->service == "popular" || $request->service == "recommended") {
        //         $validator = Validator::make($request->all(), [
        //             'zone_id' => 'required|integer',
        //         ]);
        //     } else {
        //         $validator = Validator::make($request->all(), [
        //             'query' => 'required',
        //             'zone_id' => 'required|integer',
        //         ]);
        //     }

        //     if ($validator->fails()) {
        //         return Response::json([
        //             'success'   => false,
        //             'status'    => HTTP::HTTP_UNPROCESSABLE_ENTITY,
        //             'message'   => "Validation failed.",
        //             'errors' => $validator->errors()
        //         ],  HTTP::HTTP_UNPROCESSABLE_ENTITY); // HTTP::HTTP_OK
        //     }

        //     try {
        //         if ($request->service == "recommended") {
        //             $services = Service::where('zone_id', $request->get('zone_id'))
        //                 ->orderBy('avg_rating', 'desc')
        //                 ->orderBy('created_at', 'desc')  // fallback to newest if ratings are equal
        //                 ->limit(10)
        //                 ->get();
        //         } elseif ($request->service == "popular") {
        //             $services = Service::where('zone_id', $request->get('zone_id'))
        //                 ->where('order_count', '>', 0)  // you might want to adjust this number
        //                 ->orderBy('order_count', 'desc')
        //                 ->limit(10)
        //                 ->get();
        //         } else {
        //             $services = Service::where('zone_id', $zone)
        //                 ->where(function ($q) use ($query) {
        //                     $q->where('name', 'like', "%{$query}%")
        //                         ->orWhere('short_description', 'like', "%{$query}%")
        //                         ->orWhere('long_description', 'like', "%{$query}%");
        //                 })->paginate(10)->onEachSide(-1)->appends($params);
        //             return Response::json([
        //                 'success'   => true,
        //                 'status'    => HTTP::HTTP_OK,
        //                 'message'   => "Successfully authorized.",
        //                 'data'      => [
        //                     'services'  => $services
        //                 ]
        //             ],  HTTP::HTTP_OK); // HTTP::HTTP_OK
        //         }

        //         return Response::json([
        //             'success'   => true,
        //             'status'    => HTTP::HTTP_OK,
        //             'message'   => "Successfully authorized.",
        //             'data'      => [
        //                 'services'  => $services
        //             ]
        //         ],  HTTP::HTTP_OK); // HTTP::HTTP_OK
        //     } catch (\Exception $e) {
        //         //throw $e;
        //         return Response::json([
        //             'success'   => false,
        //             'status'    => HTTP::HTTP_FORBIDDEN,
        //             'message'   => "Something went wrong. Try after sometimes.",
        //             'err' => $e->getMessage(),
        //         ],  HTTP::HTTP_FORBIDDEN); // HTTP::HTTP_OK
        //     }
        // } else {
        //     try {
        //         $service = Service::findOrFail($request->service);
        //         return Response::json([
        //             'success'   => true,
        //             'status'    => HTTP::HTTP_OK,
        //             'message'   => "Successfully authorized.",
        //             'data'      => [
        //                 'service'  => $service
        //             ]
        //         ],  HTTP::HTTP_OK); // HTTP::HTTP_OK
        //     } catch (\Exception $e) {
        //         //throw $e;
        //         return Response::json([
        //             'success'   => false,
        //             'status'    => HTTP::HTTP_FORBIDDEN,
        //             'message'   => "Something went wrong. Try after sometimes.",
        //             'err' => $e->getMessage(),
        //         ],  HTTP::HTTP_FORBIDDEN); // HTTP::HTTP_OK
        //     }
        // }
    }
}
