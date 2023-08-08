<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Models\Customer;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Events\RegisteredCustomer;
use Illuminate\Http\Response as HTTP;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;
use App\Http\Requests\StoreCustomerRequest;
use App\Http\Requests\Api\V1\CustomerLoginRequest;
use App\Http\Resources\Api\V1\CustomerResource;

class CustomerController extends Controller
{

    /**
     * Retrieve customer info.
     */
    public function customer(Request $request)
    {
        try {
            return Response::json([
                'success'   => true,
                'status'    => HTTP::HTTP_OK,
                'message'   => "Customer successfully authorized.",
                'info'   => new CustomerResource($request->user('customers'))
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
     * Validate OTP.
     */
    public function validateOtp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            // 'phone' => 'required|string|min:10|max:15',
            'phone' => [
                'required', 'string', "min:10", "max:15",
                Rule::exists('customers', 'phone'),
            ],
            'otp' => 'required|numeric',
        ], [
            'phone.exists' => 'Customer not found.',
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
            // Find the customer by email
            $customer = Customer::where('phone', $request->phone)->first();

            // Check if the provided OTP matches the stored OTP
            if ($customer->otp != $request->otp) {
                return Response::json([
                    'success'   => false,
                    'status'    => HTTP::HTTP_UNAUTHORIZED,
                    'message'   => "OTP didn't matched.",
                    'error'     => "Invalid OTP."
                ],  HTTP::HTTP_UNAUTHORIZED); // HTTP::HTTP_OK
            }

            // If the OTP matches, update the phone_verify field to 1
            $customer->phone_verify = true;
            $customer->otp = null;
            $customer->save();

            return Response::json([
                'success'   => true,
                'status'    => HTTP::HTTP_OK,
                'message'   => "Phone number verified successfully.",
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
     * Regenerate OTP.
     */
    public function regenerateOTP(Request $request)
    {
        $validator = Validator::make($request->all(), [
            // 'phone' => 'required|string|min:10|max:15',
            'phone' => [
                'required', 'string', "min:10", "max:15",
                Rule::exists('customers', 'phone'),
            ],
        ], [
            'phone.exists' => 'Customer not found.',
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
            // Find the customer by phone number
            $customer = Customer::where('phone', $request->phone)->first();

            if ($customer->phone_verify) {
                return Response::json([
                    'success'   => true,
                    'status'    => HTTP::HTTP_OK,
                    'message'   => "Phone number is already verified.",
                ],  HTTP::HTTP_OK); // HTTP::HTTP_OK
            }
            // Generate a new 4-digit random OTP
            $otp = str_pad(mt_rand(0, 9999), 4, '0', STR_PAD_LEFT);

            // Update the customer's OTP in the database
            $customer->otp = $otp;
            $customer->save();

            // SMS service for sending OTPs to the phone
            $this->sendOtpToPhone($customer->phone, $otp);
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
     * Logout customer.
     */
    public function logout(Request $request)
    {
        try {
            if ($request->user('customers')) {
                $request->user('customers')->tokens()->delete();
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
     * Customer Login with mail and phone.
     */
    public function login(CustomerLoginRequest $request)
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

            $customer = Customer::where($credentials)->first();

            if (!$customer || !Hash::check($password, $customer->password)) {
                return Response::json([
                    'success'   => false,
                    'status'    => HTTP::HTTP_UNAUTHORIZED,
                    'message'   => "Unauthenticated customer credentials.",
                    'error' => 'Invalid credentials.'
                ],  HTTP::HTTP_UNAUTHORIZED); // HTTP::HTTP_OK
            }
            if ($customer->phone_verify === 0) {
                return Response::json([
                    'success'   => false,
                    'status'    => HTTP::HTTP_FORBIDDEN,
                    'message'   => "Customer phone is not verified.",
                    'error' => 'Phone is not verified.'
                ],  HTTP::HTTP_FORBIDDEN); // HTTP::HTTP_OK
            }
            // $request->user('customers')->tokens()->delete();

            $customer->tokens()->delete();
            $token = $customer->createToken('authToken')->plainTextToken;

            return Response::json([
                'success'   => true,
                'status'    => HTTP::HTTP_OK,
                'message'   => "Customer successfully authorized.",
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
     * Crete a newly created customer in database.
     */
    public function register(StoreCustomerRequest $request)
    {
        try {
            // If the user is not registered, proceed with registration
            $otp = str_pad(mt_rand(0, 9999), 4, '0', STR_PAD_LEFT);
            $phone = $request->phone;
            $ttl = 1; // 1 min lock for otp
            $customer = Customer::create([
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'phone' => $request->phone,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'status' => $request->input("status", false),
                'ref' => $this->generateUniqueRefCode($request->phone),
                'otp' => $otp, // Save the generated OTP
            ]);

            event(new RegisteredCustomer($customer));

            // Handle image upload and storage
            // if ($request->hasFile('image')) {
            //     $imagePath = $request->file('image')->store('public/images');
            //     $customer->image = str_replace('public/', '', $imagePath); // Remove 'public/' from the image path
            // }

            // Send the OTP to the user's phone
            if (!Cache::has("$phone")) {
                try {
                    Cache::remember("$phone", 60 * $ttl, function () { // disabled for 2 minutes
                        return true;
                    });

                    // start::sending otp
                    $this->sendOtp($phone, $otp);
                    // end::sending otp

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
                        "message" => 'Something went wrong. Please try again after few min.',
                        // 'err' => $e->getMessage(),
                    ],  HTTP::HTTP_FORBIDDEN); // HTTP::HTTP_OK
                }
            }

            return Response::json([
                "success" => false,
                'status'  => HTTP::HTTP_BAD_REQUEST,
                "message" => "Please try again after $ttl min.",
            ], HTTP::HTTP_OK);
        } catch (\Exception $e) {
            //throw $e;
            return Response::json([
                'success'   => false,
                'status'    => HTTP::HTTP_FORBIDDEN,
                'message'   => "Something went wrong. Try after sometimes.",
                // 'err' => $e->getMessage(),
            ],  HTTP::HTTP_FORBIDDEN); // HTTP::HTTP_OK
        }
    }

    /**
     * Generate a unique ref code for customer.
     */
    private function generateUniqueRefCode($ref)
    {
        $ref_code = substr($ref, 3); // Generate a 10-character random string in uppercase
        while (Customer::where('ref', $ref_code)->exists()) {
            // Check if the generated code already exists in the database
            $ref_code .= rand(0, 9);
        }
        return $ref_code;
    }

    /**
     * Generate a unique ref code for customer.
     */
    private function sendOtpToPhone($phone, $otp, $ttl = 1)
    {
        if (!Cache::has("$phone")) {
            try {
                Cache::remember("$phone", 60 * $ttl, function () { // disabled for 2 minutes
                    return true;
                });

                // start::sending otp
                $this->sendOtp($phone, $otp);
                // end::sending otp

                return Response::json([
                    "success" => true,
                    'status'  => HTTP::HTTP_OK,
                    "message" => "We have sent OTP successfully.",
                ], HTTP::HTTP_OK);
            } catch (\Exception $e) {
                //throw $e;
                return Response::json([
                    'success'   => false,
                    'status'    => HTTP::HTTP_FORBIDDEN,
                    "message" => 'Something went wrong. Please try again after few min.',
                    // 'err' => $e->getMessage(),
                ],  HTTP::HTTP_FORBIDDEN); // HTTP::HTTP_OK
            }
        }

        return Response::json([
            "success" => false,
            'status'  => HTTP::HTTP_BAD_REQUEST,
            "message" => "Please try again after $ttl min.",
        ], HTTP::HTTP_OK);
    }



    /**
     * Generate a unique ref code for customer.
     */
    private function sendOtp($phone, $otp)
    {
        try {
            // send otp here

            // start::sending otp
            // do api call to otp
            // end::sending otp

        } catch (\Exception $e) {
            //throw $e;
        }
    }
}
