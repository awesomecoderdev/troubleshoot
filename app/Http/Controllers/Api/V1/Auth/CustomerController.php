<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Models\Customer;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Events\RegisteredCustomer;
use Illuminate\Http\Response as HTTP;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Response;
use App\Http\Requests\StoreCustomerRequest;

class CustomerController extends Controller
{
    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function register(StoreCustomerRequest $request)
    {
        try {
            // If the user is not registered, proceed with registration
            $otp = str_pad(mt_rand(0, 9999), 4, '0', STR_PAD_LEFT);

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


    private function generateUniqueRefCode($ref)
    {
        $ref_code = substr($ref, 3); // Generate a 10-character random string in uppercase
        while (Customer::where('ref', $ref_code)->exists()) {
            // Check if the generated code already exists in the database
            $ref_code .= rand(0, 9);
        }
        return $ref_code;
    }
}
