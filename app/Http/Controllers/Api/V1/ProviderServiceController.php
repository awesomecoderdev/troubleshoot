<?php

namespace App\Http\Controllers\Api\V1;


use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use App\Http\Controllers\Controller;
use Illuminate\Http\Response as HTTP;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Response;
use App\Http\Requests\StoreServiceRequest;
use App\Http\Requests\UpdateServiceRequest;
use App\Http\Resources\Api\V1\ProviderResource;
use App\Http\Resources\Api\V1\ServiceResource;

class ProviderServiceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $provider = $request->user("providers");
        $services = Service::with(["category", "subcategory", "reviews"])->where("provider_id", $provider->id)->get();

        try {
            return Response::json([
                'success'   => true,
                'status'    => HTTP::HTTP_OK,
                'message'   => "Successfully authorized.",
                'data'      => [
                    'provider'  => new ProviderResource($provider),
                    'services'  => $services
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
    public function store(StoreServiceRequest $request)
    {
        $provider = $request->user("providers");
        try {
            $service = Service::create([
                "name" => $request->name,
                "price" => $request->price,
                "type" => $request->type,
                "duration" => $request->duration,
                "discount" => $request->discount,
                "short_description" => $request->short_description,
                "long_description" => $request->long_description,
                "tax" => $request->tax,
                "category_id" => $request->category_id,
                "subcategory_id" => $request->subcategory_id,
                "provider_id" => $provider->id,
                "zone_id" => $provider->zone_id,
            ]);


            // Handle image upload and update
            if ($request->hasFile('image')) {
                $image = $request->file('image');
                $imageName = "service_$service->id.png";
                $imagePath = "assets/images/service/$imageName";

                try {
                    // Create the "public/images" directory if it doesn't exist
                    if (!File::isDirectory(public_path("assets/images/service"))) {
                        File::makeDirectory((public_path("assets/images/service")), 0777, true, true);
                    }

                    // Save the image to the specified path
                    $image->move(public_path('assets/images/service'), $imageName);
                    $service->image = $imagePath;
                    $service->save();
                } catch (\Exception $e) {
                    //throw $e;
                    // skip if not uploaded
                }
            }

            return Response::json([
                'success'   => true,
                'status'    => HTTP::HTTP_CREATED,
                'message'   => "Service successfully created.",
                // "service"   => $service,
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
        $service->load(["category", "subcategory", "reviews"]);
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
     * Update the specified resource in storage.
     */
    public function update(UpdateServiceRequest $request, Service $service)
    {
        $provider = $request->user("providers");
        try {
            if ($service->provider_id != $provider->id) {
                return Response::json([
                    'success'   => false,
                    'status'    => HTTP::HTTP_FORBIDDEN,
                    'message'   => "You are not belong to this service.",
                ],  HTTP::HTTP_FORBIDDEN); // HTTP::HTTP_OK
            }

            $service->update([
                "name" => $request->name,
                "price" => $request->price,
                "type" => $request->type,
                "duration" => $request->duration,
                "discount" => $request->discount,
                "short_description" => $request->short_description,
                "long_description" => $request->long_description,
                "tax" => $request->tax,
                "category_id" => $request->category_id,
                "zone_id" => $provider->zone_id,
            ]);

            return Response::json([
                'success'   => true,
                'status'    => HTTP::HTTP_OK,
                'message'   => "Service successfully updated.",
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
    public function destroy(Service $service, Request $request)
    {
        $provider = $request->user("providers");
        try {
            if ($service->provider_id != $provider->id) {
                return Response::json([
                    'success'   => false,
                    'status'    => HTTP::HTTP_FORBIDDEN,
                    'message'   => "You are not allowed to delete this service.",
                ],  HTTP::HTTP_FORBIDDEN); // HTTP::HTTP_OK
            }

            $service->delete();

            return Response::json([
                'success'   => true,
                'status'    => HTTP::HTTP_OK,
                'message'   => "Service successfully deleted.",
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
