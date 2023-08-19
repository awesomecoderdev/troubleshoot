<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Service;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCouponRequest;
use Illuminate\Http\Response as HTTP;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Response;
use App\Http\Requests\StoreServiceRequest;
use App\Http\Requests\UpdateServiceRequest;
use App\Http\Resources\Api\V1\ProviderResource;
use App\Http\Resources\Api\V1\ServiceResource;
use App\Models\Coupon;
use Carbon\Carbon;

class ProviderCouponController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $provider = $request->user("providers");
        try {
            return Response::json([
                'success'   => true,
                'status'    => HTTP::HTTP_OK,
                'message'   => "Successfully authorized.",
                'data'      => [
                    'coupons'  => $provider->coupons
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
     * Store a newly created resource in storage.
     */
    public function store(StoreCouponRequest $request)
    {
        $provider = $request->user("providers");
        $coupon = Coupon::where("code", $request->code)->orderBy("id", "DESC")->first();

        if ($coupon) {
            return Response::json([
                'success'   => false,
                'status'    => HTTP::HTTP_FORBIDDEN,
                'message'   => "Coupon already exists.",
            ],  HTTP::HTTP_FORBIDDEN); // HTTP::HTTP_OK
        }

        try {
            $coupon = new Coupon();
            $coupon->provider_id = $provider->id;
            $coupon->name = $request->name;
            $coupon->code = $request->code;
            $coupon->discount = $request->discount;
            $coupon->start = Carbon::parse($request->start)->startOfDay();
            $coupon->end = Carbon::parse($request->end)->endOfDay();
            $coupon->min_amount = $request->min_amount;
            $coupon->save();

            return Response::json([
                'success'   => true,
                'status'    => HTTP::HTTP_CREATED,
                'message'   => "Coupon successfully created.",
                "data" => [
                    // "request" => $request->all(),
                    "coupon" => $coupon,
                ]
            ],  HTTP::HTTP_CREATED); // HTTP::HTTP_OK

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
    public function show(Service $service, Request $request)
    {
        $provider = $request->user("providers");
        try {
            if ($service->provider_id != $provider->id) {
                return Response::json([
                    'success'   => false,
                    'status'    => HTTP::HTTP_FORBIDDEN,
                    'message'   => "You are not allowed to modify this service.",
                ],  HTTP::HTTP_FORBIDDEN); // HTTP::HTTP_OK
            }

            return Response::json([
                'success'   => true,
                'status'    => HTTP::HTTP_OK,
                'message'   => "Successfully Authorized.",
                'data'      => [
                    "service"   => $service,
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
     * Remove the specified resource from storage.
     */
    public function destroy(Coupon $coupon, Request $request)
    {
        $provider = $request->user("providers");
        try {
            if ($coupon->provider_id != $provider->id) {
                return Response::json([
                    'success'   => false,
                    'status'    => HTTP::HTTP_FORBIDDEN,
                    'message'   => "You are not allowed to delete this coupon.",
                ],  HTTP::HTTP_FORBIDDEN); // HTTP::HTTP_OK
            }

            $coupon->delete();

            return Response::json([
                'success'   => true,
                'status'    => HTTP::HTTP_OK,
                'message'   => "Coupon successfully deleted.",
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
