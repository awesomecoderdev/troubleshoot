<?php

namespace App\Http\Controllers\Api\V1\Auth;


use App\Models\Provider;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Events\RegisteredCustomer;
use App\Events\RegisteredProvider;
use Illuminate\Http\Response as HTTP;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\ProviderLoginRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Response;
use App\Http\Requests\StoreProviderRequest;

class ProviderController extends Controller
{

    /**
     * Retrieve providers info.
     */
    public function providers(Request $request)
    {
        try {
            return Response::json([
                'success'   => true,
                'status'    => HTTP::HTTP_OK,
                'message'   => "Provider successfully authorized.",
                'data'   => [
                    "provider" => $request->user('providers')
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
                    'error' => 'Invalid credentials.'
                ],  HTTP::HTTP_UNAUTHORIZED); // HTTP::HTTP_OK
            }
            if ($provider->phone_verify === 0) {
                return Response::json([
                    'success'   => false,
                    'status'    => HTTP::HTTP_FORBIDDEN,
                    'message'   => "Provider phone is not verified.",
                    'error' => 'Phone is not verified.'
                ],  HTTP::HTTP_FORBIDDEN); // HTTP::HTTP_OK
            }
            // $request->user('providers')->tokens()->delete();

            $provider->tokens()->delete();
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
            // If the user is not registered, proceed with registration
            $otp = str_pad(mt_rand(0, 9999), 4, '0', STR_PAD_LEFT);

            $provider = Provider::create([
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'phone' => $request->phone,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'status' => $request->input("status", false),
                'ref' => $this->generateUniqueRefCode($request->phone),
                'otp' => $otp, // Save the generated OTP
            ]);

            event(new RegisteredProvider($provider));

            // Handle image upload and storage
            // if ($request->hasFile('image')) {
            //     $imagePath = $request->file('image')->store('public/images');
            //     $provider->image = str_replace('public/', '', $imagePath); // Remove 'public/' from the image path
            // }

            // Send the OTP to the user's phone (You can adjust this based on your SMS service provider and configuration)
            // sendOtpToPhone($request->phone, $otp);'
            return Response::json([
                'success'   => true,
                'status'    => HTTP::HTTP_CREATED,
                'message'   => "Customer registered successfully.",
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
     * Generate a unique ref code for customer.
     */
    private function generateUniqueRefCode($ref)
    {
        $ref_code = substr($ref, 3); // Generate a 10-character random string in uppercase
        while (Provider::where('ref', $ref_code)->exists()) {
            // Check if the generated code already exists in the database
            $ref_code .= rand(0, 9);
        }
        return $ref_code;
    }
}
