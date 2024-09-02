<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Logger;
use Illuminate\Support\Facades\Log;
use Package\Notifications\Facades\Notify;
use Illuminate\Foundation\Auth\ResetsPasswords;
use Illuminate\Http\Request;

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
     * Override the default response to include a message.
     *
     * @param \Illuminate\Http\Request $request
     * @param  string                  $response
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    protected function sendResetResponse(Request $request, $response)
    {
        $userId = $this->guard()->user()->id;
        Log::info("User $userId has reset their password");
        Logger::log('auth.reset-password', true, ['user_id' => $userId]);
        Notify::success('Password reset');
        return $this->traitSendResetResponse($request, $response);
    }
}
