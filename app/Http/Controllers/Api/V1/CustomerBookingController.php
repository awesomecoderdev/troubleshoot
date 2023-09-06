<?php

namespace App\Http\Controllers\Api\V1;

use Carbon\Carbon;
use App\Models\Coupon;
use App\Models\Booking;
use App\Models\Service;
use App\Models\Campaign;
use Illuminate\Support\Arr;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Http\Response as HTTP;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;
use App\Http\Requests\StoreBookingRequest;
use App\Http\Resources\Api\V1\CustomerResource;

class CustomerBookingController extends Controller
{
    /**
     * Retrieve booking info.
     */
    public function booking(Request $request)
    {
        try {
            // Get the user's id from token header and get his provider bookings
            // status
            $params = Arr::only($request->input(), ["query", "zone_id", "per_page"]);
            $customer = $request->user("customers");
            $bookings = Booking::with([
                "provider",
                "category",
                "handyman",
                "service",
                "zone",
                "campaign",
                "coupon",
                "customer",
                "schedules"
            ])->where("customer_id", $customer->id)->when($request->status != null && in_array($request->status, ["pending", "accepted", "rejected", "progressing", "progressed", "cancelled", "completed"]), function ($query) use ($request) {
                return $query->where("status", strtolower($request->status));
            })->orderBy("id", "DESC")->paginate($request->input("per_page", 10))->onEachSide(-1)->appends($params);

            return Response::json([
                'success'   => true,
                'status'    => HTTP::HTTP_OK,
                'message'   => "Successfully authorized.",
                'data'   => [
                    "bookings" => $bookings,
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
     * Retrieve update info.
     */
    public function change(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'booking_id' => 'required|integer|exists:bookings,id',
            "status" => 'required|in:cancelled,completed'
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
            // Get the user's id from token header and get his customer bookings
            $customer = $request->user("customers");
            $booking = Booking::with("handyman")->where("id", $request->booking_id)->when($request->status != null, function ($query) use ($request) {
                if ($request->status == "cancelled") {
                    $query->where("status", "pending");
                } elseif ($request->status == "completed") {
                    $query->where("status", "progressed");
                } else {
                    $query->where("status", null);
                }
            })->where("customer_id", $customer->id)->firstOrFail();

            $booking->status = $request->status;
            $booking->save();

            $handyman = $booking->handyman;

            if ($handyman) {
                $handyman->status = "available";
                $handyman->save();
            }

            return Response::json([
                'success'   => true,
                'status'    => HTTP::HTTP_OK,
                'message'   => "Successfully updated.",
                "data"      => [
                    "booking" => $booking
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

    /**
     * Register Booking
     */
    public function register(StoreBookingRequest $request)
    {
        try {
            $errors = [];
            $calculation = [];
            $today = Carbon::now();
            $customer = $request->user("customers");
            $customer->load("address");
            $service = Service::where("id", $request->service_id)->where("status", true)->firstOrFail();
            $coupon = Coupon::where("provider_id", $service->provider_id)->where("code", $request->input("coupon", 0))->first();
            $campaign = Campaign::where("id", $request->input("campaign_id", 0))->first();
            $service = Service::where("id", $request->service_id)->firstOrFail();
            $quantity = $request->input("quantity", 1);
            $quantity = $quantity < 1 ? 1 : $quantity;

            if ($request->filled("schedule")) {
                if ($request->schedule < date("Y-m-d", strtotime($today))) {
                    return Response::json([
                        'success'   => false,
                        'status'    => HTTP::HTTP_UNPROCESSABLE_ENTITY,
                        'message'   => "Validation failed.",
                        'errors' => [
                            "schedule" => [
                                "Please select a valid schedule.",
                            ]
                        ]
                    ],  HTTP::HTTP_UNPROCESSABLE_ENTITY); // HTTP::HTTP_OK
                }
            }

            // data
            $price = intval($service->price);
            $discount = intval($service->discount);
            $tax = intval($price * ($service->tax / 100));

            // discount price
            $discountPrice = $price - $discount;
            // with tax
            $withTax = $discountPrice + $tax;

            if ($coupon && $coupon->end >= $today) { // with coupon
                // coupon minimum amount
                if ($withTax < $coupon->min_amount) {
                    $totalDiscount = $discount;
                    $subtotal = $withTax;
                    $totalAmount = $quantity * $subtotal;
                    $calculation = [
                        "price" => $price,
                        "discount" => $discount,
                        "total_discount" => $totalDiscount,
                        "with_discount" => $discountPrice,
                        "tax" => $service->tax,
                        "total_tax" => $tax,
                        "with_tax" => $withTax,
                        "with_coupon" => $totalAmount,
                        "without_coupon" => $withTax,
                        "subtotal" => $subtotal,
                        "total_amount" => $totalAmount,
                    ];
                    $errors = [
                        "errors" => [
                            "coupon" => [
                                "To get coupon discount, minimum requirements is ৳$coupon->min_amount order."
                            ]
                        ]
                    ];
                } else {
                    // with coupon
                    $totalDiscount = $discount + $coupon->discount;
                    $subtotal = $withTax - $coupon->discount;
                    $totalAmount = $quantity * $subtotal;
                    $calculation = [
                        "price" => $price,
                        "discount" => $discount,
                        "total_discount" => $totalDiscount,
                        "with_discount" => $discountPrice,
                        "tax" => $service->tax,
                        "total_tax" => $tax,
                        "with_tax" => $withTax,
                        "with_coupon" => $totalAmount,
                        "without_coupon" => $withTax,
                        "subtotal" => $subtotal,
                        "total_amount" => $totalAmount,
                    ];
                }
            } else { // without coupon
                $totalDiscount = $discount;
                $subtotal = $withTax;
                $totalAmount = $quantity * $subtotal;

                $calculation = [
                    "price" => $price,
                    "discount" => $discount,
                    "total_discount" => $totalDiscount,
                    "with_discount" => $discountPrice,
                    "tax" => $service->tax,
                    "total_tax" => $tax,
                    "with_tax" => $withTax,
                    "with_coupon" => $totalAmount,
                    "without_coupon" => $withTax,
                    "subtotal" => $subtotal,
                    "total_amount" => $totalAmount,
                ];
                $errors = [
                    "errors" => [
                        "coupon" => [
                            "Invalid Coupon Code."
                        ]
                    ]
                ];
            }


            // need to calculate total tax etc
            $total_amount = intval($totalAmount);
            $total_tax = intval($tax);
            $total_discount = intval($totalDiscount);
            $additional_charge = intval(0);
            // start Transaction
            DB::beginTransaction();

            // new booking
            $booking = new Booking();
            $booking->customer_id = $customer->id;
            $booking->address_id = $customer->address->id ?? 0;
            $booking->service_id = $service->id;
            $booking->provider_id = $service->provider_id;
            $booking->hint = $request->hint;
            $booking->quantity = $quantity;

            $booking->metadata = [
                "service" => $service,
                "customer" => new CustomerResource($customer),
                "coupon" => $coupon,
                "campaign" => $campaign,
                "calculation" => $calculation
            ];

            $booking->coupon_id = $request->input("coupon_id", 0);
            $booking->campaign_id = $request->input("campaign_id", 0);
            // $booking->handyman_id = $request->input("handyman_id", 0);
            $booking->category_id = $service->category_id;
            $booking->zone_id = $service->zone_id;
            $booking->payment_method = $request->payment_method;
            // $booking->status = $request->status;
            // $booking->is_paid = $request->is_paid;
            // $booking->is_paid = $request->is_paid;
            $booking->total_amount = $total_amount;
            $booking->total_tax = $total_tax;
            $booking->total_discount = $total_discount;
            $booking->additional_charge = $additional_charge;
            $booking->is_rated = false; // that mean booking is not given rating
            $booking->schedule = Carbon::parse($request->schedule)->startOfDay();
            $booking->save();

            // end transaction
            DB::commit();


            return Response::json(
                [
                    'success'   => true,
                    'status'    => HTTP::HTTP_CREATED,
                    'message'   => "Booking successfully created.",
                    // "data"      => [
                    // "customer" => $customer,
                    // "booking" => $booking,
                    // "service" => $service
                    // ]
                ],
                HTTP::HTTP_CREATED
            ); // HTTP::HTTP_OK
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

    /**
     * Retrieve update info.
     */
    public function details(Request $request)
    {
        try {
            // Get the user's id from token header and get his provider bookings
            $customer = $request->user("customers");
            $booking = Booking::with([
                "provider",
                "category",
                "handyman",
                "service",
                "zone",
                "campaign",
                "coupon",
                "customer",
            ])->where("id", $request->booking)->where("customer_id", $customer->id)->firstOrFail();

            return Response::json([
                'success'   => true,
                'status'    => HTTP::HTTP_OK,
                'message'   => "Successfully Authorized.",
                "data"      => [
                    "booking" => $booking
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

    /**
     * Calculate bookings.
     */
    public function calculate(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'service_id' => 'required|integer|exists:services,id',
            'quantity'   => "integer"
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
            // Get the user's id from token header and get his provider bookings
            $customer = $request->user("customers");
            $today = Carbon::today();
            $service = Service::where("id", $request->service_id)->firstOrFail();
            $coupon = Coupon::where("provider_id", $service->provider_id)->where("code", $request->input("coupon", 0))->where("end", '>=', $today)->first();
            $quantity = $request->input("quantity", 1);
            // data
            $price = intval($service->price);
            $discount = intval($service->discount);
            $tax = intval($price * ($service->tax / 100));

            // discount price
            $discountPrice = $price - $discount;

            // with tax
            $withTax = $discountPrice + $tax;


            if ($coupon) {
                // coupon minimum amount
                if ($withTax < $coupon->min_amount) {
                    $subtotal = $withTax;
                    $totalAmount = $quantity * $subtotal;
                    return Response::json([
                        'success'   => true,
                        'status'    => HTTP::HTTP_OK,
                        'message'   => "Successfully Authorized.",
                        "data"      => [
                            "price" => $price,
                            "discount" => $discount,
                            "total_discount" => $discount,
                            "with_discount" => $discountPrice,
                            "tax" => $service->tax,
                            "total_tax" => $tax,
                            "with_tax" => $withTax,
                            "with_coupon" => $totalAmount,
                            "without_coupon" => $withTax,
                            "subtotal" => $subtotal,
                            "total_amount" => $totalAmount,
                            "coupon" => $coupon,
                            "service" => $service
                        ],
                        "errors" => [
                            "coupon" => [
                                "To get coupon discount, minimum requirements is ৳$coupon->min_amount order."
                            ]
                        ]
                    ],  HTTP::HTTP_OK); // HTTP::HTTP_OK
                } else {
                    // with coupon
                    $totalDiscount = $discount + $coupon->discount;
                    $subtotal = $withTax - $coupon->discount;
                    $totalAmount = $quantity * $subtotal;
                    return Response::json([
                        'success'   => true,
                        'status'    => HTTP::HTTP_OK,
                        'message'   => "Successfully Authorized.",
                        "data"      => [
                            "price" => $price,
                            "discount" => $discount,
                            "total_discount" => $totalDiscount,
                            "with_discount" => $discountPrice,
                            "tax" => $service->tax,
                            "total_tax" => $tax,
                            "with_tax" => $withTax,
                            "with_coupon" => $totalAmount,
                            "without_coupon" => $withTax,
                            "subtotal" => $subtotal,
                            "total_amount" => $totalAmount,
                            "coupon" => $coupon,
                            "service" => $service
                        ],
                    ],  HTTP::HTTP_OK); // HTTP::HTTP_OK
                }
            } else {
                $subtotal = $withTax;
                $totalAmount = $quantity * $subtotal;
                return Response::json([
                    'success'   => true,
                    'status'    => HTTP::HTTP_OK,
                    'message'   => "Successfully Authorized.",
                    "data"      => [
                        "price" => $price,
                        "discount" => $discount,
                        "total_discount" => $discount,
                        "with_discount" => $discountPrice,
                        "tax" => $service->tax,
                        "total_tax" => $tax,
                        "with_tax" => $withTax,
                        "with_coupon" => $totalAmount,
                        "without_coupon" => $withTax,
                        "subtotal" => $subtotal,
                        "total_amount" => $totalAmount,
                        "coupon" => $coupon,
                        "service" => $service
                    ],
                    "errors" => [
                        "coupon" => [
                            "Invalid Coupon Code."
                        ]
                    ]
                ],  HTTP::HTTP_OK); // HTTP::HTTP_OK

            }
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
