<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Cookie\CookieJar;
use Illuminate\Support\Facades\Cookie;
use Symfony\Component\HttpFoundation\Response;

class SetCsrfTokenMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);
        // $response->headers->setCookie(cookie('XSRF-TOKEN', csrf_token()));
        $customer = $request->user("customers");
        if ($customer) {
            // $address =  Arr::only($customer->address, [
            //     "apartment_name",
            //     "apartment_number",
            //     "city",
            //     "street_one",
            //     "street_two",
            //     "zip",
            // ]);
            // $customer->address = $address;
            // $customer = Arr::only($customer, [
            //     "first_name",
            //     "last_name",
            //     "email",
            //     "image",
            //     "phone",
            //     "phone_verify",
            //     "status",
            //     "address"
            // ]);


            $token = base64_encode(json_encode($customer));
            $hash = Str::random(50);
            $response->headers->setCookie(cookie('session_id',  "$hash.$token.$hash"));
        }

        // $response->headers->setCookie(cookie('X-CSRF-TOKEN', csrf_token()));

        return $response;
    }
}
