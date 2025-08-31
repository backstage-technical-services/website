<?php

namespace Package\WebDevTools\Laravel\Errors;

use BadMethodCallException;
use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Exceptions\Handler as BaseHandler;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Prophecy\Exception\Doubler\MethodNotFoundException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

class Handler extends BaseHandler
{
    /**
     * Render an exception into an HTTP response.
     *
     * @param  Request    $request
     * @param  Throwable  $e
     *
     * @return Response
     * @throws Throwable
     */
    public function render($request, Throwable $e)
    {
        $e = $this->prepareException($e);

        if ($this->shouldReturnAjaxError($request, $e)) {
            return response()->json(
                [
                    'error' => $this->getHttpMessage($e),
                    '__error' => true,
                ],
                $this->getHttpStatusCode($e),
            );
        }

        if ($this->showRedirectToAuthentication($request, $e)) {
            return redirect()->guest(isset($this->loginRoute) ? $this->loginRoute : '/');
        }

        return parent::render($request, $e);
    }

    /**
     * Prepare exception for rendering.
     *
     * @param  Throwable  $e
     *
     * @return Exception
     */
    protected function prepareException(Throwable $e)
    {
        $e = parent::prepareException($e);

        if ($e instanceof MethodNotFoundException || $e instanceof BadMethodCallException) {
            $e = new NotFoundHttpException($e->getMessage(), $e);
        }

        return $e;
    }

    /**
     * Test whether the error should be handled by returning an AJAX error.
     *
     * @param              $request
     * @param  Exception   $exception
     *
     * @return bool
     */
    protected function shouldReturnAjaxError($request, Exception $exception)
    {
        return $request->expectsJson() && !($exception instanceof ValidationException);
    }

    /**
     * Get the HTTP status message for an exception.
     *
     * @param  Exception  $e
     *
     * @return string
     */
    protected function getHttpMessage(Exception $e)
    {
        if ($e instanceof AuthenticationException) {
            return 'You need to be logged in to do that.';
        } elseif ($e instanceof AuthorizationException || ($e instanceof HttpException && $e->getStatusCode() == 403)) {
            return 'You aren\'t allowed to do that.';
        } elseif ($e instanceof NotFoundHttpException) {
            return 'We couldn\'t find what you were after.';
        }

        return 'Oops! An unknown error occurred';
    }

    /**
     * Get the HTTP status code for an exception.
     *
     * @param  Exception  $e
     *
     * @return int
     */
    protected function getHttpStatusCode(Exception $e)
    {
        if ($e instanceof AuthenticationException) {
            return Response::HTTP_UNAUTHORIZED;
        } elseif ($e instanceof AuthorizationException || ($e instanceof HttpException && $e->getStatusCode() == 403)) {
            return Response::HTTP_FORBIDDEN;
        } elseif ($e instanceof NotFoundHttpException) {
            return Response::HTTP_NOT_FOUND;
        }

        return Response::HTTP_INTERNAL_SERVER_ERROR;
    }

    /**
     * Determine whether the response should be a redirect to the login form.
     *
     * @param  Request    $request
     * @param  Exception  $e
     *
     * @return bool
     */
    protected function showRedirectToAuthentication(Request $request, Exception $e)
    {
        return !$request->user() &&
            ($e instanceof AuthenticationException ||
                $e instanceof AuthorizationException ||
                ($e instanceof HttpException && $e->getStatusCode() == 403));
    }

    /**
     * Convert an authentication exception into an unauthenticated response.
     *
     * @param                           $request
     * @param  AuthenticationException  $exception
     *
     * @return Response
     */
    protected function unauthenticated($request, AuthenticationException $exception)
    {
        return $request->expectsJson()
            ? response()->json(['message' => $exception->getMessage()], 401)
            : redirect()->guest(isset($this->loginRoute) ? $this->loginRoute : '/');
    }
}
