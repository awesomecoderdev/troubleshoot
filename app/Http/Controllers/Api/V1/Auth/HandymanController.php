<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Models\Handyman;
use Illuminate\Support\Arr;
use Illuminate\Http\Request;
use App\Events\RegisteredHandyman;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Response as HTTP;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;
use App\Http\Requests\StoreHandymanRequest;
use App\Http\Requests\UpdateHandymanRequest;
use App\Http\Resources\Api\V1\HandymanResource;
use App\Http\Requests\Api\V1\HandymanLoginRequest;

class HandymanController extends Controller
{
    /**
     * Retrieve handymans info.
     */
    public function handyman(Request $request)
    {
        try {
            return Response::json([
                'success'   => true,
                'status'    => HTTP::HTTP_OK,
                'message'   => "Handyman successfully authorized.",
                'data'   => [
                    "handyman" => new HandymanResource($request->user('handymans'))
                ]
            ],  HTTP::HTTP_OK); // HTTP::HTTP_OK
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
     * Logout handyman.
     */
    public function logout(Request $request)
    {
        try {
            if ($request->user('handymans')) {
                $request->user('handymans')->tokens()->delete();
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
     * Crete a newly created handymans in database.
     */
    public function login(HandymanLoginRequest $request)
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

            $handyman = Handyman::where($credentials)->first();

            if (!$handyman || !Hash::check($password, $handyman->password)) {
                return Response::json([
                    'success'   => false,
                    'status'    => HTTP::HTTP_UNAUTHORIZED,
                    'message'   => "Unauthenticated handyman credentials.",
                    'errors' => 'Invalid credentials.'
                ],  HTTP::HTTP_UNAUTHORIZED); // HTTP::HTTP_OK
            }
            if ($handyman->phone_verify === 0) {
                return Response::json([
                    'success'   => false,
                    'status'    => HTTP::HTTP_FORBIDDEN,
                    'message'   => "Handyman phone is not verified.",
                    'errors' => 'Phone is not verified.'
                ],  HTTP::HTTP_FORBIDDEN); // HTTP::HTTP_OK
            }

            // $handyman->tokens()->delete();
            $token = $handyman->createToken('authToken')->plainTextToken;

            return Response::json([
                'success'   => true,
                'status'    => HTTP::HTTP_OK,
                'message'   => "Handyman successfully authorized.",
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
     * Crete a newly created Handyman in database.
     */
    public function register(StoreHandymanRequest $request)
    {
        $provider = $request->user('providers');
        try {
            $handyman = Handyman::create([
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'address' => $request->address,
                'password' => Hash::make($request->password),
                'provider_id' => $provider->id,
            ]);

            event(new RegisteredHandyman($handyman));
            return Response::json([
                'success'   => true,
                'status'    => HTTP::HTTP_CREATED,
                'message'   => "Handyman registered successfully.",
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
     * Update customer data database.
     */
    public function update(UpdateHandymanRequest $request)
    {
        // get provider
        $provider = $request->user('providers');
        $handyman = Handyman::where("id", "$request->handyman_id")->first();

        $credentials = Arr::only($request->all(), [
            'name',
            'email',
            'phone',
            'image',
            'address'
        ]);

        if ($request->filled("password")) {
            $credentials['password'] = Hash::make($request->password);
        }

        try {
            if ($handyman->provider_id != $provider->id) {
                return Response::json([
                    'success'   => false,
                    'status'    => HTTP::HTTP_FORBIDDEN,
                    'message'   => "You are not allowed to modify this handyman.",
                ],  HTTP::HTTP_FORBIDDEN); // HTTP::HTTP_OK
            }

            // Handle image upload and update
            if ($request->hasFile('image')) {
                $image = $request->file('image');
                $imageName = "handyman_$handyman->id.png";
                $imagePath = "assets/images/handyman/$imageName";

                try {
                    // Create the "public/images" directory if it doesn't exist
                    if (!File::isDirectory(public_path("assets/images/handyman"))) {
                        File::makeDirectory((public_path("assets/images/handyman")), 0777, true, true);
                    }

                    // Save the image to the specified path
                    $image->move(public_path('assets/images/handyman'), $imageName);
                    $credentials["image"] = $imagePath;
                } catch (\Exception $e) {
                    //throw $e;
                    // skip if not uploaded
                }
            }
            // Update the handyman data
            $handyman->update($credentials);

            return Response::json([
                'success'   => true,
                'status'    => HTTP::HTTP_OK,
                'message'   => "Handyman updated successfully.",
                'data'      => [
                    "handyman" => $handyman
                ],
            ],  HTTP::HTTP_OK); // HTTP::HTTP_OK
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
     * Delete customer from database.
     */
    public function delete(Request $request)
    {
        // get provider
        $provider = $request->user('providers');
        $validator = Validator::make($request->all(), [
            'handyman_id' => 'required|integer|exists:handymen,id',
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
            $handyman = Handyman::where("id", $request->handyman_id)->first();

            if ($handyman->provider_id != $provider->id) {
                return Response::json([
                    'success'   => false,
                    'status'    => HTTP::HTTP_FORBIDDEN,
                    'message'   => "You are not allowed to delete this handyman.",
                ],  HTTP::HTTP_FORBIDDEN); // HTTP::HTTP_OK
            }

            // Delete the account from the database
            $handyman->tokens()->delete();
            $handyman->delete();

            return Response::json([
                'success'   => true,
                'status'    => HTTP::HTTP_ACCEPTED,
                'message'   => "Account deleted successfully.",
            ],  HTTP::HTTP_ACCEPTED); // HTTP::HTTP_OK
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
}
