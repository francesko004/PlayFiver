<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;

class Handler extends ExceptionHandler
{
    /*** The list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /*** Register the exception handling callbacks for the application.
     */
    public function render($request, Throwable $exception)
    {
        if ($exception instanceof AccessDeniedException) {
            return response()->json($exception->getMessage(), 403, [], JSON_UNESCAPED_UNICODE);
        }

        return parent::render($request, $exception);
    }
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }
}
