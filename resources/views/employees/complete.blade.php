@include("header")


@if (Auth::check() && Auth::user()->roles == config('constants.MANAGER_ROLE_VALUE'))
    <div class="vh-100 d-flex flex-column justify-content-center align-items-center">
        <div><h4 class="text-secondary mb-4">Update has been applied.</h4></div>
        <div><h4 class="text-secondary mb-4">The employee has been notified of the changes.</h4></div>
        <p>
            <a href="{{ route('home') }}" class="text-center">Home</a>
        </p>
    </div>
@else
    <div class="vh-100 d-flex flex-column justify-content-center align-items-center">
        <div><h4 class="text-secondary mb-4">Your manager has been notified of your request. </h4></div>
        <div><h4 class="text-secondary mb-4">An email will be sent to your email once your request has been approved. </h4></div>
        @if(Auth::check())
        <p>
            <a href="{{ route('home') }}" class="text-center">Home</a>
        </p>
        @else
        <p>
            <a href="{{ route('login') }}" class="text-center">Back to Login</a>
        </p>
        @endif
    </div> 
@endif
@include('footer')