<?php

namespace App\Http\Controllers\Api\V1\Auth;


use Carbon\Carbon;
use App\Models\Service;
use App\Models\Provider;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Events\RegisteredProvider;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Response as HTTP;
use Illuminate\Support\Facades\Response;
use App\Http\Requests\StoreProviderRequest;
use App\Http\Resources\Api\V1\ProviderResource;
use App\Http\Requests\Api\V1\ProviderLoginRequest;

class ProviderController extends Controller
{

    /**
     * Retrieve providers info.
     */
    public function provider(Request $request)
    {
        $provider = $request->user("providers");
        try {
            return Response::json([
                'success'   => true,
                'status'    => HTTP::HTTP_OK,
                'message'   => "Provider successfully authorized.",
                'data'   => [
                    "provider" => new ProviderResource($provider),
                    // "services" => $provider->services
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
     * Display a listing of the resource.
     */
    public function services(Request $request)
    {
        try {
            $services = Service::all();
            return Response::json([
                'success'   => true,
                'status'    => HTTP::HTTP_OK,
                'message'   => "Successfully authorized.",
                'services'  => $services
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
     * Logout provider.
     */
    public function logout(Request $request)
    {
        try {
            if ($request->user('providers')) {
                $request->user('providers')->tokens()->delete();
                return Response::json([
                    'success'   => true,
                    'status'    => HTTP::HTTP_OK,
                    'message'   => "Logged out successfully.",
                ],  HTTP::HTTP_OK); // HTTP::HTTP_OK
            } else {
                return Response::json([
                    'success'   => true,
                    'status'    => HTTP::HTTP_OK,
                    'message'   => "You have already logged out.",
                ],  HTTP::HTTP_OK); // HTTP::HTTP_OK
            }
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
     * Crete a newly created providers in database.
     */
    public function login(ProviderLoginRequest $request)
    {
        $loginField = $request->input('login_field');
        $password = $request->input('password');

        try {
            $credentials = [];
            if (filter_var($loginField, FILTER_VALIDATE_EMAIL)) {
                // The input is an email
                $credentials['email'] = $loginField;
            } else {
                // The input is a phone number
                $credentials['phone'] = $loginField;
            }

            $provider = Provider::where($credentials)->first();

            if (!$provider || !Hash::check($password, $provider->password)) {
                return Response::json([
                    'success'   => false,
                    'status'    => HTTP::HTTP_UNAUTHORIZED,
                    'message'   => "Unauthenticated provider credentials.",
                    'errors' => 'Invalid credentials.'
                ],  HTTP::HTTP_UNAUTHORIZED); // HTTP::HTTP_OK
            }

            if ($provider->phone_verify === 0) {
                return Response::json([
                    'success'   => false,
                    'status'    => HTTP::HTTP_FORBIDDEN,
                    'message'   => "Provider phone is not verified.",
                    'errors' => 'Phone is not verified.'
                ],  HTTP::HTTP_FORBIDDEN); // HTTP::HTTP_OK
            }

            // $provider->tokens()->delete(); // for live need to enable
            $token = $provider->createToken('authToken')->plainTextToken;

            return Response::json([
                'success'   => true,
                'status'    => HTTP::HTTP_OK,
                'message'   => "Provider successfully authorized.",
                'token' => $token
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
     * Crete a newly created provider in database.
     */
    public function register(StoreProviderRequest $request)
    {
        try {

            $provider = Provider::create([
                // "zone_id" => $request->zone_id,
                "company_name" => $request->company_name,
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'phone' => $request->phone,
                'address' => $request->address,
                "identity_number" => $request->identity_number,
                "contact_person_name" => $request->contact_person_name,
                "contact_person_phone" => $request->contact_person_phone,
                "contact_email" => $request->contact_email,
                // "image" => $request->image,
                // "identity_image" => [$request->identity_image],
                // "order_count" => $request->order_count,
                // "service_man_count" => $request->service_man_count,
                // "service_capacity_per_day" => $request->service_capacity_per_day,
                // "rating_count" => $request->rating_count,
                // "avg_rating" => $request->avg_rating,
                // "commission_status" => $request->commission_status,
                // "commission_percentage" => $request->input('commission_percentage', 0),
                // "is_active" => $request->is_active,
                // "is_approved" => $request->is_approved,
                "start" => Carbon::parse($request->start),
                "end" => Carbon::parse($request->end),
                "off_day" => [$request->off_day],
            ]);

            event(new RegisteredProvider($provider));

            // Handle image upload and update
            if ($request->hasFile('image')) {
                $image = $request->file('image');
                $imageName = "provider_$provider->id.png";
                $imagePath = "assets/images/provider/$imageName";

                try {
                    // Create the "public/images" directory if it doesn't exist
                    if (!File::isDirectory(public_path("assets/images/provider"))) {
                        File::makeDirectory((public_path("assets/images/provider")), 0777, true, true);
                    }

                    // Save the image to the specified path
                    $image->move(public_path('assets/images/provider'), $imageName);
                    $provider->image = $imagePath;
                    $provider->save();
                } catch (\Exception $e) {
                    //throw $e;
                    // skip if not uploaded
                }
            }


            // Send the OTP to the user's phone (You can adjust this based on your SMS service provider and configuration)
            // sendOtpToPhone($request->phone, $otp);'
            return Response::json([
                'success'   => true,
                'status'    => HTTP::HTTP_CREATED,
                'message'   => "Provider registered successfully.",
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
}
