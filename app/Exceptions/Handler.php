<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\JsonResponse;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException;
use Illuminate\Session\TokenMismatchException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }

    /**
     * Convert an authentication exception into an unauthenticated response.
     */
    protected function unauthenticated($request, AuthenticationException $exception)
    {
        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json([
                'message' => 'Unauthenticated.',
            ], 401);
        }

        return redirect()->guest(route('login'));
    }

    /**
     * Render an exception into an HTTP response.
     */
    public function render($request, Throwable $e)
    {
        if ($request->expectsJson() || $request->is('api/*')) {
            // Authorization (403)
            if ($e instanceof AuthorizationException) {
                return response()->json(['message' => 'Forbidden.'], 403);
            }

            // Model not found => 404
            if ($e instanceof ModelNotFoundException) {
                return response()->json(['message' => 'Resource not found.'], 404);
            }

            // Validation errors => 422
            if ($e instanceof ValidationException) {
                return response()->json([
                    'message' => 'The given data was invalid.',
                    'errors' => $e->errors(),
                ], 422);
            }

            // CSRF / Token mismatch => 419
            if ($e instanceof TokenMismatchException) {
                return response()->json(['message' => 'CSRF token mismatch.'], 419);
            }

            $status = 500;

            if ($e instanceof HttpExceptionInterface) {
                $status = $e->getStatusCode();
            }

            $message = $e->getMessage() ?: 'Server Error';

            return response()->json([
                'message' => $message,
            ], $status);
        }

        return parent::render($request, $e);
    }
}
