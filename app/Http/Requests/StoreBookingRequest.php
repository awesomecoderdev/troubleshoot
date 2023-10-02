<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\Response as HTTP;
use Illuminate\Support\Facades\Response;

class StoreBookingRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    // public function authorize(): bool
    // {
    //     return false;
    // }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules()
    {
        return [
            // "provider_id" => "required|exists:providers,id",
            // "address_id" => "required|exists:addresses,id",
            // "customer_id" => "required|exists:customers,id",
            "hint" => "required|string|min:10|max:450",
            "coupon" => "nullable|string",
            // "handyman_id" => "nullable|exists:handymen,id",
            // "campaign_id" => "nullable|exists:campaigns,id",
            // "service_id" => "required|exists:services,id",
            // "category_id" => "required|exists:categories,id",
            // "zone_id" => "required|exists:zones,id",
            // "status" => "in:pending,accepted,rejected,progressing,completed",
            // "is_paid" => "boolean",
            "payment_method" => "required|in:cod,online",
            // "total_amount" => "required|string",
            // "total_tax" => "string",
            // "total_discount" => "string",
            // "additional_charge" => "string",
            "schedule" => "date_format:Y-m-d",
            // "is_rated" => "boolean",
            "quantity" => "integer"
        ];
    }

    /**
     * Get the validation attributes that apply to the request.
     *
     * @return array
     */
    // public function attributes()
    // {
    //     return [
    //         "name" => "required",
    //         "email" => "required|email|unique:users,email",
    //         "password" => "required|min:8|max:12",
    //         "confirmed" => "required|same:password",
    //     ];
    // }

    /**
     * Get the validation messages that apply to the request.
     *
     * @return array
     */
    public function messages()
    {
        return [
            // "first_name.required" => Lang::get("auth.first_name.required"),
            // "last_name.required" => Lang::get("auth.last_name.required"),
            // "email.required" => Lang::get("auth.email.required"),
            // "password.required" => Lang::get("auth.password.required"),
            // "confirmed.required" => Lang::get("auth.confirmed.required"),
        ];
    }


    /**
     * @throws \HttpResponseException When the validation rules is not valid
     */
    public function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(Response::json([
            'success'   => false,
            'status' => HTTP::HTTP_UNPROCESSABLE_ENTITY,
            'message'   => 'Validation failed.',
            'errors'     => $validator->errors(),
        ], HTTP::HTTP_UNPROCESSABLE_ENTITY)); // HTTP::HTTP_OK);
    }
}
