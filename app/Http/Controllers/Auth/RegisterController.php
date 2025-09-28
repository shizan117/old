<?php

namespace App\Http\Controllers\Auth;

use App\Client;
use App\User;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Foundation\Auth\RegistersUsers;

class RegisterController extends Controller
{
    public function showRegistrationForm()
    {
        return redirect('login');
    }

}
