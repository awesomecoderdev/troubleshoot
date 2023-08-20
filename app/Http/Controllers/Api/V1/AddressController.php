<?php

namespace App\Http\Controllers\Api\V1;


use App\Models\Address;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Http\Response as HTTP;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Response;
use App\Http\Requests\StoreAddressRequest;
use App\Http\Requests\UpdateAddressRequest;
use App\Http\Resources\Api\V1\AddressResource;
use Illuminate\Support\Arr;

class AddressController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            $customer = $request->user('customers');
            return Response::json([
                'success'   => true,
                'status'    => HTTP::HTTP_OK,
                'message'   => "Successfully authorized.",
                'data'      => [
                    "address" => new AddressResource($customer->address)
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
    public function update(UpdateAddressRequest $request)
    {
        try {
            $customer = $request->user('customers');
            $address = $customer->address;

            $address->street_one = $request->street_one;
            $address->apartment_name = $request->apartment_name;
            $address->apartment_number = $request->apartment_number;
            $address->street_two = $request->input("street_two", "");
            $address->city = $request->city;
            $address->zip = $request->zip;
            $address->lat = $request->lat;
            $address->lng = $request->lng;
            $address->save();

            return Response::json([
                'success'   => true,
                'status'    => HTTP::HTTP_OK,
                'message'   => "Address successfully updated.",
                // 'data'      => [
                //     "address" => new AddressResource($address),
                // ]
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
