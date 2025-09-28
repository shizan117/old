@extends('admin.layouts.login-master')
@section('title','RESET PASSWORD')
@section('content')

    @if (session('status'))
        <div class="alert alert-success" role="alert">
            {{ session('status') }}
        </div>
    @endif
    <form action="{{ route('password.email')  }}" method="POST">
        @csrf
        <input type="email" name="email" placeholder="Email" value="{{ old('email') }}" required autofocus/>
        @if ($errors->has('email'))
            <span class="invalid-feedback d-block" role="alert">
                <strong>{{ $errors->first('email') }}</strong>
            </span>
        @endif
        <a href="{{ route('login') }}">Back to Login</a><br>
        <button type="submit">Send Password Reset Link</button>
    </form>

@endsection
