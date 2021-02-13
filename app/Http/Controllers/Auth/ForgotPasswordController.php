<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Package\Notifications\Facades\Notify;
use Illuminate\Foundation\Auth\SendsPasswordResetEmails;
use Illuminate\Http\Request;

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
     * @param \Illuminate\Http\Request $request
     * @param  string                  $response
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    protected function sendResetLinkResponse(Request $request, $response)
    {
        Notify::success('A link to reset your password has been sent to the email address specified.');
        return redirect()->route('auth.login');
    }
}
