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
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class Handler extends ExceptionHandler
{
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
        if (Str::startsWith($request->path(), 'api')) {

            // $status = method_exists(Throwable::class, 'getStatusCode') ? $e->getStatusCode() : 404;
            $status = HTTP::HTTP_OK;
            return Response::json([
                'success'   => false,
                'status'    => $status,
                'message'   => $e->getMessage(),
            ], $status); // HTTP::HTTP_OK
        }
        return parent::render($request, $e);
    }
}
