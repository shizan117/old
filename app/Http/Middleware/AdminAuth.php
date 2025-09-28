<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Auth\Middleware\Authenticate;


class AdminAuth extends Authenticate
{
    public function handle($request, Closure $next, ...$guards)
    {
        if ($this->auth->guard('admin')->check()) {
            $this->auth->shouldUse('admin');
            return $next($request);
        }
        return redirect()->route('admin.login');
    }
}
