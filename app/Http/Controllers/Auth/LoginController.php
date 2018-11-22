<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Logger;
use bnjns\LaravelNotifications\Facades\Notify;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;

class LoginController extends Controller
{
    use AuthenticatesUsers {
        logout as traitLogout;
        sendFailedLoginResponse as traitSendFailedLoginResponse;
    }

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = '/members';

    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        $this->middleware('guest', ['except' => 'logout']);
    }

    /**
     * Get the login username to be used by the controller.
     *
     * @return string
     */
    public function username()
    {
        return 'username';
    }

    /**
     * Validate the user login request.
     *
     * @param  \Illuminate\Http\Request $request
     *
     * @return void
     * @throws \Illuminate\Validation\ValidationException
     */
    protected function validateLogin(Request $request)
    {
        $this->validate($request, [
            'username' => 'required',
            'password' => 'required',
        ], [
            'username.required' => 'Please enter your username or email address',
            'password.required' => 'Please enter your password',
        ]);
    }

    /**
     * Attempt to log the user into the application.
     *
     * @param  \Illuminate\Http\Request $request
     *
     * @return bool
     */
    protected function attemptLogin(Request $request)
    {
        $request->merge(['email' => $request->get($this->username())]);

        return $this->guard()->attempt($request->only('username', 'password'), true)
               || $this->guard()->attempt($request->only('email', 'password'), true);
    }

    /**
     * The user has been authenticated.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  mixed                    $user
     *
     * @return void
     */
    protected function authenticated(Request $request, $user)
    {
        Logger::log('auth.login');
        Notify::success('Logged in');
    }

    /**
     * Override the failed login response to log the result.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Illuminate\Validation\ValidationException
     */
    protected function sendFailedLoginResponse(Request $request)
    {
        Logger::log('auth.login', false, $request->only('username'));
        return $this->traitSendFailedLoginResponse($request);
    }

    /**
     * Log the user out of the application.
     *
     * @param  \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function logout(Request $request)
    {
        Logger::log('auth.logout');
        Notify::success('Logged out');
        return $this->traitLogout($request);
    }
}
