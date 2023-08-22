<?php

namespace App\Http\Controllers\Api\V1;

use Carbon\Carbon;
use App\Models\Coupon;
use App\Models\Provider;
use Illuminate\Support\Arr;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Http\Response as HTTP;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;


class CustomerCouponController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function coupons(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'zone_id' => 'required|integer|exists:zones,id',
        ]);

        if ($validator->fails()) {
            return Response::json([
                'success'   => false,
                'status'    => HTTP::HTTP_UNPROCESSABLE_ENTITY,
                'message'   => "Validation failed.",
                'errors' => $validator->errors()
            ],  HTTP::HTTP_UNPROCESSABLE_ENTITY); // HTTP::HTTP_OK
        }
        $today = Carbon::now()->endOfDay();
        $params = Arr::only($request->input(), ["zone_id", "per_page"]);

        // $providers = Provider::where("zone_id", $request->zone_id)->where("is_approved", true)->where("is_active", true)->get();
        $providers = Provider::select("id")->where("zone_id", $request->zone_id)->pluck("id")->toArray();
        $coupons = Coupon::whereIn("provider_id", $providers)->when($request->status == null, function ($query) use ($today) {
            return $query->where('end', '>=', $today);
        })->when($request->status != null, function ($query) use ($today) {
            return $query->where('end', '<', $today);
        })->orderBy("id", "DESC")->paginate($request->input("per_page", 10))->onEachSide(-1)->appends($params);

        try {
            return Response::json([
                'success'   => true,
                'status'    => HTTP::HTTP_OK,
                'message'   => "Successfully authorized.",
                'data'      => [
                    // 'providers'  => $providers,
                    'coupons'  => $coupons,
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
