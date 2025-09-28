@extends('admin.layouts.login-master')
@section('title','Reset Admin Password')
@section('content')

    <form action="{{ route('admin.password.reset.post') }}" method="POST">
        @csrf
        <input type="hidden" name="token" value="{{ $token }}">
        <input type="email" name="email" placeholder="Email" value="{{ old('email') }}" autofocus required />
        @if ($errors->has('email'))
            <span class="invalid-feedback d-block" role="alert">
                <strong>{{ $errors->first('email') }}</strong>
            </span>
        @endif
        <input type="password" name="password" placeholder="Password" required/>
        @if ($errors->has('password'))
            <span class="invalid-feedback d-block" role="alert">
                <strong>{{ $errors->first('password') }}</strong>
            </span>
        @endif
        <input type="password" name="password_confirmation" placeholder="Confirm Password" required/>
        <a href="{{ route('admin.login') }}">Back to Login</a><br>
        <button type="submit">Reset</button>
    </form>

@endsection
