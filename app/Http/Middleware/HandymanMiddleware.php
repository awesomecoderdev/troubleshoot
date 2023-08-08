<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response as HTTP;
use Illuminate\Support\Facades\Response;

class HandymanMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (Illuminate\Support\Facades\Response)  $next
     */
    public function handle(Request $request, Closure $next, $parameter = "true")
    {
        if ($parameter == "true") {
            if (!$request->user("handymans")) {
                return Response::json([
                    'success'   => false,
                    'status'    => HTTP::HTTP_UNAUTHORIZED,
                    'message'   => "Unauthorized Access.",
                ], HTTP::HTTP_UNAUTHORIZED); // HTTP::HTTP_OK
            }
        } else if ($parameter == "false") {
            if ($request->user("handymans")) {
                return Response::json([
                    'success'   => false,
                    'status'    => HTTP::HTTP_OK,
                    'message'   => "You are already logged in.",
                ], HTTP::HTTP_OK); // HTTP::HTTP_OK
            }
        }


        return $next($request);
    }
}
