@extends('admin.layouts.login-master')
@section('title','ADMIN LOGIN')
@section('content')



    <form action="{{ route('admin.login.post') }}" method="POST">
        @csrf
        <input type="email" name="email" placeholder="Email" value="{{ old('email') }}" required autofocus/>
        <input type="password" name="password" placeholder="Password" required/>

        @if($errors->any())
            <br><span class="invalid-feedback d-block"><strong>Invalid email or password</strong></span>
        @endif
        <a href="{{ route('admin.password.reset') }}">Forgot your password?</a><br>
        <button type="submit" class="">Sign In</button>
        <a href="{{ url('login') }}" class="d-block mt-3">Client Login</a>
    </form>



@endsection
