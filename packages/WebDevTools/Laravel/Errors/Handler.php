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

class Handler extends BaseHandler
{
    /**
     * Render an exception into an HTTP response.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Exception               $exception
     *
     * @return \Illuminate\Http\Response
     */
    public function render($request, Exception $exception)
    {
        $exception = $this->prepareException($exception);

        if ($this->shouldReturnAjaxError($request, $exception)) {
            return response()->json([
                'error'   => $this->getHttpMessage($exception),
                '__error' => true,
            ],
                $this->getHttpStatusCode($exception));
        }

        if ($this->showRedirectToAuthentication($request, $exception)) {
            return redirect()->guest(isset($this->loginRoute) ? $this->loginRoute : '/');
        }

        return parent::render($request, $exception);
    }

    /**
     * Prepare exception for rendering.
     *
     * @param \Exception $e
     *
     * @return \Exception
     */
    protected function prepareException(Exception $e)
    {
        $e = parent::prepareException($e);

        if ($e instanceof MethodNotFoundException || $e instanceof BadMethodCallException) {
            $e = new NotFoundHttpException($e->getMessage(), $e);
        }

        return $e;
    }

    /**
     * Convert an authentication exception into an unauthenticated response.
     *
     * @param                                          $request
     * @param \Illuminate\Auth\AuthenticationException $exception
     *
     * @return \Illuminate\Http\Response
     */
    protected function unauthenticated($request, AuthenticationException $exception)
    {
        return $request->expectsJson()
            ? response()->json(['message' => $exception->getMessage()], 401)
            : redirect()->guest(isset($this->loginRoute) ? $this->loginRoute : '/');
    }

    /**
     * Get the HTTP status code for an exception.
     *
     * @param \Exception $e
     *
     * @return int
     */
    protected function getHttpStatusCode(Exception $e)
    {
        if ($e instanceof AuthenticationException) {
            return Response::HTTP_UNAUTHORIZED;
        } else if ($e instanceof AuthorizationException || ($e instanceof HttpException && $e->getStatusCode() == 403)) {
            return Response::HTTP_FORBIDDEN;
        } else if ($e instanceof NotFoundHttpException) {
            return Response::HTTP_NOT_FOUND;
        }

        return Response::HTTP_INTERNAL_SERVER_ERROR;
    }

    /**
     * Get the HTTP status message for an exception.
     *
     * @param \Exception $e
     *
     * @return string
     */
    protected function getHttpMessage(Exception $e)
    {
        if ($e instanceof AuthenticationException) {
            return 'You need to be logged in to do that.';
        } else if ($e instanceof AuthorizationException || ($e instanceof HttpException && $e->getStatusCode() == 403)) {
            return 'You aren\'t allowed to do that.';
        } else if ($e instanceof NotFoundHttpException) {
            return 'We couldn\'t find what you were after.';
        }

        return 'Oops! An unknown error occurred';
    }

    /**
     * Determine whether the response should be a redirect to the login form.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Exception               $e
     *
     * @return bool
     */
    protected function showRedirectToAuthentication(Request $request, Exception $e)
    {
        return !$request->user()
               && ($e instanceof AuthenticationException ||
                   $e instanceof AuthorizationException ||
                   ($e instanceof HttpException && $e->getStatusCode() == 403));
    }

    /**
     * Test whether the error should be handled by returning an AJAX error.
     *
     * @param            $request
     * @param \Exception $exception
     *
     * @return bool
     */
    protected function shouldReturnAjaxError($request, Exception $exception)
    {
        return $request->expectsJson() && !($exception instanceof ValidationException);
    }
}
