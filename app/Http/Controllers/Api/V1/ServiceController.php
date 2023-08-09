<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Service;
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
            'category_id' => 'required|integer',
            'zone_id' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return Response::json([
                'success'   => false,
                'status'    => HTTP::HTTP_UNPROCESSABLE_ENTITY,
                'message'   => "Validation failed.",
                'error' => $validator->errors()
            ],  HTTP::HTTP_UNPROCESSABLE_ENTITY); // HTTP::HTTP_OK
        }

        try {
            $params = Arr::only($request->input(), ["category_id", "zone_id"]);
            $services = Service::where('category_id', $category)->where('zone_id', $zone)->paginate(10)->onEachSide(0)->appends($params);
            return Response::json([
                'success'   => true,
                'status'    => HTTP::HTTP_OK,
                'message'   => "Successfully authorized.",
                'services'  => $services,
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
    public function show(Service $service)
    {
        try {
            return Response::json([
                'success'   => true,
                'status'    => HTTP::HTTP_OK,
                'message'   => "Successfully authorized.",
                'data'      => [
                    'service'  => $service
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
