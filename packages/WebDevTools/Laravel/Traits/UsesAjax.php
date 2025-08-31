<?php

namespace Package\WebDevTools\Laravel\Traits;

use Illuminate\Support\Facades\Log;

trait UsesAjax
{
    /**
     * Create a response for an AJAX request.
     *
     * @param       $text
     * @param int   $status
     * @param array $data
     * @param array $headers
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function ajaxResponse($text, $status = 200, array $data = [], array $headers = [])
    {
        $data = array_merge($data, [$status == 200 ? 'response' : 'error' => $text]);

        Log::debug('Sending JSON response', ['response' => response()->json($data, $status, $headers)]);

        return response()->json($data, $status, $headers);
    }

    /**
     * Create an AJAX error response.
     *
     * @param     $errorText
     * @param int $status
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function ajaxError($errorCode, $status = 422, $errorText = null)
    {
        $data = [
            'error_code' => $errorCode,
            '__error' => true,
        ];
        $text = $errorText ?: trans('errors.' . $errorCode);

        return $this->ajaxResponse($text, $status, $data);
    }

    /**
     * Require that the request is made over AJAX.
     *
     * @return void
     */
    protected function requireAjax()
    {
        if (!request()->ajax()) {
            app()->abort(404);
        }
    }
}
