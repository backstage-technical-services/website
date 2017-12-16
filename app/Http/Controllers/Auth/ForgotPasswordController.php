<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use bnjns\LaravelNotifications\Facades\Notify;
use Illuminate\Foundation\Auth\SendsPasswordResetEmails;

class ForgotPasswordController extends Controller
{
    use SendsPasswordResetEmails;

    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        $this->middleware('guest');
    }

    /**
     * Override the default response for when sending
     * the reset email is successful.
     *
     * @param  string $response
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    protected function sendResetLinkResponse($response)
    {
        Notify::success('A link to reset your password has been sent to the email address specified.');
        return redirect()->route('auth.login');
    }
}
