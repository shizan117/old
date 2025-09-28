<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {

        $roles = $this->getRequiredRoleForRoute($request->route());


        if($request->user('admin')->hasRole($roles) || !$roles){
            return $next($request);
        }
        //Auth::logout();
        Session::flash('message', 'You Have Not Permission To View This Page!');
        Session::flash('m-class', 'alert-danger');
        return redirect()->back();
    }

    private function getRequiredRoleForRoute($route){
        $action = $route->getAction();
        return isset($action['roles']) ? $action['roles'] : null;
    }
}
