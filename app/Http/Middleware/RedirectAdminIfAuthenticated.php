<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class RedirectAdminIfAuthenticated
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string|null  $guard
     * @return mixed
     */
    public function handle($request, Closure $next, $guard = 'admin')
    {
        if (Auth::guard($guard)->check()) {
            return redirect()->route('admin.dashboard');
        }

        return $next($request);
    }
}
/*
 *  Oi githum page e onek gulo popular admin package  ase, tumi check koro, tumar . Adminlte laravel admin package na.
 * aita admin template, oi page e joto gulo admin package ase pray sob guloi adminlte template use korese
 */