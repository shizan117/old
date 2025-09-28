<?php

namespace App\Http\Controllers\AdminAuth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use App\Config;

class AdminLoginController extends Controller
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

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $this->middleware('redirect.admin', ['except' => 'logout']);

    }


    protected function redirectTo()
    {
        return route('admin.dashboard');
    }




    /**
     * Send the response after the user was authenticated.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */


    protected function sendLoginResponse(Request $request) {
        $customRememberMeTimeInMinutes = 7200;
        $rememberTokenCookieKey = $this->guard()->getRecallerName();
        Cookie::queue($rememberTokenCookieKey, Cookie::get($rememberTokenCookieKey), $customRememberMeTimeInMinutes);
        $request->session()->regenerate();
        $this->clearLoginAttempts($request);
        return $this->authenticated($request, $this->guard()->user())
            ?: redirect()->route('admin.dashboard');
    }


    public function redirectPath()
    {
        if (method_exists($this, 'redirectTo')) {
            return $this->redirectTo();
        }

        return property_exists($this, 'redirectTo') ? $this->redirectTo : route('admin.dashboard');
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
            $this->username() => 'required|exists:users,' . $this->username() . ',active,1',
            'password' => 'required',
        ], [
            $this->username() . '.exists' => 'The selected email is invalid or the account has been disabled.'
        ]);
    }
    /**
     * Show the application's login form.
     *
     * @return \Illuminate\Http\Response
     */
    public function showLoginForm()
    {
        return view('admin.auth.login');
    }

    /**
     * Log the user out of the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function logout(Request $request)
    {
        $this->guard()->logout();

//        $request->session()->invalidate();

        // Get remember_me cookie name
//        $rememberMeCookie = $this->guard()->getRecallerName();
//        // Tell Laravel to forget this cookie
//        $cookie = Cookie::forget($rememberMeCookie);
        return $this->loggedOut($request) ?: redirect()->route('admin.login');
    }

    /**
     * Get the guard to be used during authentication.
     *
     * @return \Illuminate\Contracts\Auth\StatefulGuard
     */
    protected function guard()
    {
        return Auth::guard('admin');
    }
}
