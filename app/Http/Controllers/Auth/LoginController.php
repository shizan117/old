<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;


    protected function redirectTo()
    {
        return route('home');
    }

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $this->middleware('guest', ['except' => 'logout']);
    }


    /**
     * Send the response after the user was authenticated.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */

    protected function sendLoginResponse(Request $request) {
        $customRememberMeTimeInMinutes = 7200;
        $rememberTokenCookieKey = Auth::getRecallerName();
        Cookie::queue($rememberTokenCookieKey, Cookie::get($rememberTokenCookieKey), $customRememberMeTimeInMinutes);
        $request->session()->regenerate();
        $this->clearLoginAttempts($request);
        return $this->authenticated($request, $this->guard()->user())
            ?: redirect()->intended($this->redirectPath());
    }


    /**
     * Validate the user login request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return void
     */


    protected function validateLogin(Request $request)
    {
        $this->validate($request, [
            $this->username() => 'required|exists:clients,' . $this->username() . ',active,1',
            'password' => 'required',
        ], [
            $this->username() . '.exists' => 'The selected user is invalid or the account has been disabled.'
        ]);
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

    public function logout(Request $request)
    {
        Auth::guard()->logout();

        return $this->loggedOut($request) ?: redirect('/login');
    }


    protected function guard()
    {
        return Auth::guard('web');
    }
}
