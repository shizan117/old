<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Internet Service Provider">

    <link rel="shortcut icon" href="{{ 'assets/images/favicon.ico' }}">

    <title>{{ $setting['companyName'] }} - Internet Service Provider</title>

    <!-- App css -->
    <link href="{{ asset('assets/css/bootstrap.min.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('assets/css/icons.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('assets/css/style.css') }}" rel="stylesheet" type="text/css" />

    <script src="{{ 'assets/js/modernizr.min.js' }}"></script>

</head>

<body>

<div class="account-pages"></div>
<div class="clearfix"></div>
<div class="wrapper-page">
    <div class="ex-page-content text-center">
        <div class="text-error">404</div>
        <h3 class="text-uppercase font-600">Page not Found</h3>
        <p class="text-muted">
            It's looking like you may have taken a wrong turn. Don't worry... it happens to
            the best of us. You might want to check your internet connection. Here's a little tip that might
            help you get back on track.
        </p>
        <br>
       <a class="btn btn-success waves-effect waves-light" href="{{ route('home') }}"> Return Home</a>

    </div>
</div>

</body>
</html>