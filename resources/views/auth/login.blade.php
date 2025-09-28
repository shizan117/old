@extends('admin.layouts.login-master')
@section('title','CLIENT LOGIN')
@section('content')

    <form action="{{ route('login') }}" method="POST">
        @csrf
        <input type="text" name="username" placeholder="Username" value="{{ old('username') }}" required autofocus/>
        <input type="password" name="password" placeholder="Password" required/>

        @if($errors->any())
            <br><span class="invalid-feedback d-block"><strong>Invalid email or password</strong></span>
        @endif
        <a href="{{ route('password.request') }}">Forgot your password?</a><br>
        <button type="submit" class="">Sign In</button>
        <a href="{{ route('admin.login') }}" class="d-block mt-3">Admin Login</a>
    </form>

@endsection
