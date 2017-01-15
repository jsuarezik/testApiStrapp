<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Validation\ValidationException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Laravel\Lumen\Exceptions\Handler as ExceptionHandler;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that should not be reported.
     *
     * @var array
     */
    protected $dontReport = [
        AuthorizationException::class,
        HttpException::class,
        ModelNotFoundException::class,
        ValidationException::class,
        FileNotFoundException::class
    ];

    /**
     * Report or log an exception.
     *
     * This is a great spot to send exceptions to Sentry, Bugsnag, etc.
     *
     * @param  \Exception $e
     * @return void
     */
    public function report(Exception $e)
    {
        parent::report($e);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Exception $e
     * @return \Illuminate\Http\Response
     */
    public function render($request, Exception $e)
    {
        if ($request->ajax() || $request->wantsJson()) {

            $setTrace = true;
            $response = [
                'message' => 'something went wrong',
                'status' => Response::HTTP_INTERNAL_SERVER_ERROR
            ];

            if ($e instanceof HttpException) {
                $setTrace = false;
                $response['message'] = Response::$statusTexts[$e->getStatusCode()];
                $response['status'] = $e->getStatusCode();
            } else if ($e instanceof ModelNotFoundException) {
                $setTrace = false;
                $response['message'] = Response::$statusTexts[Response::HTTP_NOT_FOUND];
                $response['status'] = Response::HTTP_NOT_FOUND;
            } else if ($e instanceof FileNotFoundException) {
                $setTrace = false;
                $response['message'] = Response::$statusTexts[Response::HTTP_NOT_FOUND];
                $response['status'] = Response::HTTP_NOT_FOUND;
            } else if ($e instanceof ValidationException) {
                $setTrace = false;
                $response['message'] = $e->getMessage();
                $response['validation'] = json_decode($e->getResponse()->getContent());
                $response['status'] = Response::HTTP_UNPROCESSABLE_ENTITY;
            } else if ($e instanceof QueryException) {
                if ($e->getCode() == 23000) {
                    $setTrace = false;
                    $response['status'] = Response::HTTP_UNPROCESSABLE_ENTITY;
                }
            }

            if (env('APP_DEBUG')) {
                $message = $e->getMessage();

                // check if message is utf8 (this shouldn't be done here)
                if (!preg_match('!!u', $message)) {
                    $message = iconv(mb_detect_encoding($message, mb_detect_order(), true), "UTF-8", $message);
                }

                $response['debug'] = [
                    'message' => $message,
                    'exception' => get_class($e)
                ];

                if ($setTrace) {
                    $response['debug']['trace'] = $e->getTrace();
                }

                if ($e instanceof QueryException) {
                    $json['debug']['sql'] = $e->getSql();
                    $json['debug']['bindings'] = $e->getBindings();
                }
            }

            return response()->json($response, $response['status']);
        }

        return parent::render($request, $e);
    }
}
