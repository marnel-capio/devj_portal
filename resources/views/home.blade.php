

@if (Auth::check())
    logined user:  {{ Auth::user() }}
@else
    not logined
@endif