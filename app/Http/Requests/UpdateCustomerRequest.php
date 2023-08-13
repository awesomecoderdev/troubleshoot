<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\Response as HTTP;
use Illuminate\Support\Facades\Response;

class UpdateCustomerRequest extends FormRequest
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
        $customer = $this->user('customers');

        return [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            // 'phone' => "required|min:10|max:20|unique:customers,phone,$customer->id",
            'email' => "required|max:100|unique:customers,email,$customer->id",
            // 'password' => 'required|string|min:6|max:10',
            'image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
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
