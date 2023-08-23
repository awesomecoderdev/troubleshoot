<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Cookie\CookieJar;
use Illuminate\Support\Facades\Cookie;
use Symfony\Component\HttpFoundation\Response;

class SetCsrfTokenMiddleware
{

    /**
     * The cookie jar instance.
     *
     * @var \Illuminate\Contracts\Cookie\QueueingFactory
     */
    protected $cookies;

    /**
     * Create a new CookieQueue instance.
     *
     * @param  \Illuminate\Contracts\Cookie\QueueingFactory  $cookies
     * @return void
     */
    public function __construct(CookieJar $cookies)
    {
        $this->cookies = $cookies;
    }


    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);
        // $customer = $request->users("customers");
        // if ($customer) {
        //     $response->headers->set('X-CSRF-TOKEN', csrf_token());
        // }

        $response->headers->setCookie(cookie('X-CSRF-TOKEN', csrf_token()));
        // $request->header->set('Access-Control-Allow-Origin', 'https://troubleshoot.one/');
        // $request->header->set('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE');
        // $request->header->set('Access-Control-Allow-Headers', 'Content-Type, X-Requested-With, Cookie');
        // $request->header->set('Access-Control-Allow-Credentials', 'true');

        return $response;
    }
}
