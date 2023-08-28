<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\Response as HTTP;
use Illuminate\Support\Facades\Response;

class StoreProviderRequest extends FormRequest
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
            // 'zone_id' => 'required|integer',
            'company_name' => 'required|string|max:255',
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'company_name' => 'required|string|max:255',
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('providers')], //unique:users,email
            'phone' => ['required', 'string', "min:10", "max:20", Rule::unique('providers')],
            'password' => 'required|string|min:6|max:8',
            'address' => "required|string",
            "identity_number" => "required",
            "contact_person_name" => 'required|string|max:255',
            "contact_person_phone" => "required|string|min:10|max:20",
            "contact_email" => "required|email",
            'image' => 'required|image|mimes:jpeg,png,jpg|max:2048',
            // "identity_image" => "required",
            // "order_count" => "required",
            // "service_man_count" => "required|integer",
            // "service_capacity_per_day" => "required|integer",
            // "rating_count" => "required",
            // "avg_rating" => "required",
            "commission_status" => "boolean",
            // "commission_percentage" => "required",
            // "is_active" => "required",
            // "is_approved" => "required",
            "start" => "required",
            "end" => "required",
            "off_day" => "required",
            // 'status' => 'boolean', // Validate status field
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
