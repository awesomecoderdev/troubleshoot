<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Category;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Http\Response as HTTP;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;
use App\Http\Requests\StoreCategoryRequest;
use App\Http\Requests\UpdateCategoryRequest;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'zone_id' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return Response::json([
                'success'   => false,
                'status'    => HTTP::HTTP_UNPROCESSABLE_ENTITY,
                'message'   => "Validation failed.",
                'errors' => $validator->errors()
            ],  HTTP::HTTP_UNPROCESSABLE_ENTITY); // HTTP::HTTP_OK
        }

        // Get categories that match the provided zone_id
        $zoneId = $request->zone_id;

        try {
            $categories = Category::where('parent_id', 0)
                ->whereRaw("FIND_IN_SET($zoneId, zone_id)") // Check if zone_id is in the 'zone' column
                ->where("is_active", true)
                ->get();

            return Response::json([
                'success'   => true,
                'status'    => HTTP::HTTP_OK,
                'message'   => "Successfully Authorized.",
                'data'      => [
                    'categories' => $categories
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

    /**
     * Display the specified resource.
     */
    public function show(Request $request)
    {
        if (in_array($request->category, ["subcategories"])) {
            if ($request->category == "subcategories") {
                $this->subcategories($request);
            }
        } else {
            try {
                $categories = Category::where('parent_id', 0)
                    ->where("id", $request->category)
                    ->where("zone_id", $request->zone_id)
                    ->where("is_active", true)
                    ->firstOrFail();

                return Response::json([
                    'success'   => true,
                    'status'    => HTTP::HTTP_OK,
                    'message'   => "Successfully Authorized.",
                    'data'      => [
                        'categories' => $categories
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
    public function subcategories(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'zone_id' => 'required|integer',
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
            $subcategories = Category::where('parent_id', $request->category)
                ->where("is_active", true)
                ->get();

            return Response::json([
                'success'   => true,
                'status'    => HTTP::HTTP_OK,
                'message'   => "Successfully Authorized.",
                'data'      => [
                    'subcategories' => $subcategories
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
