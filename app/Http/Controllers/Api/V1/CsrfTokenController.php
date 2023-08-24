<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response as HTTP;
use Illuminate\Support\Facades\Response;


class CsrfTokenController extends Controller
{
    /**
     * Refresh the csrf token.
     */
    public function refresh(Request $request)
    {
        return Response::json([
            'success'   => true,
            'status'    => HTTP::HTTP_OK,
            "message"   => "Successfully Authorized."
            // 'token' => csrf_token()
        ], HTTP::HTTP_OK); // HTTP::HTTP_OK
    }
}
