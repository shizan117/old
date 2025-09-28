@component('mail::layout')
    {{-- Header --}}
    @slot('header')
        @component('mail::header', ['url' => config('app.url')])
            {{ $message->companyName.' '.config('app.name') }}
        @endcomponent
    @endslot

    {{-- Body --}}
# Dear {{$message->clientName}}!

{!! $message->top_line !!}

@component('mail::button', ['url' => $message->url])
{{ $message->buttonText }}
@endcomponent

Thanks,<br>
{{ $message->companyName }}

    {{-- Subcopy --}}
    @isset($subcopy)
        @slot('subcopy')
            @component('mail::subcopy')
                {{ $subcopy }}
            @endcomponent
        @endslot
    @endisset

    {{-- Footer --}}
    @slot('footer')
        @component('mail::footer')
            © {{ date('Y') }} {{ config('app.name') }}. All rights reserved.
        @endcomponent
    @endslot
@endcomponent