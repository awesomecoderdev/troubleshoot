<?php

namespace App\Exceptions;

use Throwable;
use Illuminate\Support\Str;
use Illuminate\Http\Response as HTTP;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Response;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Symfony\Component\Routing\Exception\RouteNotFoundException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Session\TokenMismatchException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;


class Handler extends ExceptionHandler
{
    /**
     * Default status code for CSRF.
     */
    const HTTP_CSRF_MISMATCH = 419;

    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
        });
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Throwable  $exception
     * @return \Illuminate\Http\Response
     */
    public function render($request, Throwable $e)
    {
        // if (Str::startsWith($request->path(), 'api')) {

        // Not FoundHttp Exception
        if ($e instanceof ModelNotFoundException || $e instanceof RouteNotFoundException || $e instanceof NotFoundHttpException) {
            return Response::json([
                'success'   => false,
                'status'    => HTTP::HTTP_NOT_FOUND,
                'message'   =>  "Not Found.",
                'err'   => $e->getMessage(),
            ], HTTP::HTTP_NOT_FOUND);
        }

        if ($e instanceof TokenMismatchException) {
            return Response::json([
                'success'   => false,
                'status'    => self::HTTP_CSRF_MISMATCH,
                'message'   =>  "CSRF Token Mismatch.",
            ], self::HTTP_CSRF_MISMATCH)->header("X-CSRF-TOKEN", csrf_token())->cookie(cookie("X-CSRF-TOKEN", csrf_token(), 60));
        }

        //  default exception
        $status = method_exists($e, 'getStatusCode') ? $e->getStatusCode() : HTTP::HTTP_INTERNAL_SERVER_ERROR;
        // $status = HTTP::HTTP_NOT_FOUND;
        return Response::json([
            'success'   => false,
            'status'    => $status,
            'message'   => $e->getMessage(),
        ], $status); // HTTP::HTTP_OK
        // }

        return parent::render($request, $e);
    }
}
