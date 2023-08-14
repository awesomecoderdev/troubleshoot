<?php

namespace App\Http\Controllers\Api\V1;


use App\Models\Zone;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Http\Response as HTTP;
use Illuminate\Support\Facades\Cache;
use App\Http\Requests\StoreZoneRequest;
use App\Http\Requests\UpdateZoneRequest;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;

class ZoneController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function zone(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'lat' => 'required',
            'lng' => 'required',
        ]);

        if ($validator->fails()) {
            return Response::json([
                'success'   => false,
                'status'    => HTTP::HTTP_UNPROCESSABLE_ENTITY,
                'message'   => "Validation failed.",
                'errors' => $validator->errors()
            ],  HTTP::HTTP_UNPROCESSABLE_ENTITY); // HTTP::HTTP_OK
        }

        $lat = $request->lat;
        $lng = $request->lng;

        try {
            // Create a point for the given lat and lng
            $point = "POINT($lng $lat)"; // Note that we switch the order of lat and lng

            // Find the zone that contains the given point
            $zone = DB::table('zones')
                ->whereRaw("ST_CONTAINS(coordinates, ST_GeomFromText('$point'))")
                ->latest()
                ->first(['id', 'name']);

            if (!$zone) {
                return Response::json([
                    'success'   => false,
                    'status'    => HTTP::HTTP_OK,
                    'message'   => "Zone Unavailable.",
                    'errors' => [
                        [
                            'code' => 'coordinates',
                            'message' => 'Service is not available in this area.'
                        ]
                    ]
                ],  HTTP::HTTP_OK); // HTTP::HTTP_OK
            }

            return Response::json([
                'success'   => false,
                'status'    => HTTP::HTTP_OK,
                'message'   => "Successfully Authorized.",
                'data'      => [
                    'zone_id' => $zone->id,
                    'zone_name' => $zone->name,
                ]
            ],  HTTP::HTTP_OK); // HTTP::HTTP_OK
        } catch (\Exception $e) {
            return Response::json([
                'success'   => false,
                'status'    => HTTP::HTTP_OK,
                'message'   => "Zone Unavailable.",
                'errors' => [
                    [
                        'code' => 'coordinates',
                        'message' => 'Service is not available in this area.'
                    ]
                ]
            ],  HTTP::HTTP_OK); // HTTP::HTTP_OK
            // throw $e;
            // return Response::json([
            //     'success'   => false,
            //     'status'    => HTTP::HTTP_FORBIDDEN,
            //     'message'   => "Something went wrong. Try after sometimes.",
            //     'err' => $e->getMessage(),
            // ],  HTTP::HTTP_FORBIDDEN); // HTTP::HTTP_OK
        }
    }

    /**
     * Display a listing of the resource.
     */
    public function zones(Request $request)
    {

        try {
            $zones = Zone::select("id", "name")->get();
            return Response::json([
                'success'   => false,
                'status'    => HTTP::HTTP_OK,
                'message'   => "Successfully Authorized.",
                'data'      => [
                    'zones' => $zones
                ]
            ],  HTTP::HTTP_OK); // HTTP::HTTP_OK
        } catch (\Exception $e) {
            return Response::json([
                'success'   => false,
                'status'    => HTTP::HTTP_OK,
                'message'   => "Zone Unavailable.",
                'errors' => [
                    [
                        'code' => 'coordinates',
                        'message' => 'Service is not available in this area.'
                    ]
                ]
            ],  HTTP::HTTP_OK); // HTTP::HTTP_OK
        }
    }
}
