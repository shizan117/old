<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Auth\Middleware\Authenticate;
use Illuminate\Support\Facades\Auth;


class BkashPaymentLoginCheck extends Authenticate
{
    public function handle($request, Closure $next, ...$guards)
    {
        if($this->auth->guard('admin')->check() || $this->auth->guard()->check()) {
            return $next($request);
        }

        if ($request->ajax() || $request->wantsJson()) {
            return response('Unauthorized.', 401);
        } else {
            return redirect()->guest('login');
        }

    }
}
