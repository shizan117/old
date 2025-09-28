<?php

namespace App\Http\Controllers\AdminAuth;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\ResetsPasswords;

//Auth Facade
use Illuminate\Support\Facades\Auth;

//Password Broker Facade
use Illuminate\Support\Facades\Password;

class ResetAdminPasswordController extends Controller
{


    use ResetsPasswords;

    /**
     * Where to redirect users after resetting their password.
     *
     * @var string
     */
 //   protected $redirectTo = '/dashboard';

    protected function redirectTo()
    {
        return redirect()->intended(route('admin.dashboard'));
    }

    /**
     * Create a new controller instance.
     *
     * @return void
     */

    public function __construct()
    {
        $this->middleware('redirect.admin');
    }

    public function showResetForm(Request $request, $token = null)
    {
        return view('admin.auth.passwords.reset')->with(
            ['token' => $token, 'email' => $request->email]
        );
    }

    //returns Password broker of seller
    public function broker()
    {
        return Password::broker('users');
    }

    //returns authentication guard of seller
    protected function guard()
    {
        return Auth::guard('admin');
    }
}
