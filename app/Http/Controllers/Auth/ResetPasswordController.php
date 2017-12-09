<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use bnjns\LaravelNotifications\Facades\Notify;
use Illuminate\Foundation\Auth\ResetsPasswords;

class ResetPasswordController extends Controller
{
    use ResetsPasswords {
        sendResetResponse as traitSendResetResponse;
    }

    /**
     * Where to redirect users after resetting their password.
     *
     * @var string
     */
    protected $redirectTo = '/';

    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        $this->middleware('guest');
    }

    /**
     * Overriwde the default response to include a message.
     *
     * @param  string $response
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    protected function sendResetResponse($response)
    {
        Notify::success('Password reset');
        return $this->traitSendResetResponse($response);
    }
}
