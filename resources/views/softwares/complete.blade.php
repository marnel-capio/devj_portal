@include("header")


@if (Auth::check() && Auth::user()->roles == config('constants.MANAGER_ROLE_VALUE'))
    <div class="vh-100 d-flex flex-column justify-content-center align-items-center">
        <div><h4 class="text-secondary mb-4">Software has been registered/updated successfully.</h4></div>
        <a href="{{ route('home') }}" class="text-center">Home</a></p>
    </div>
@else
    <div class="vh-100 d-flex flex-column justify-content-center align-items-center">
        <div><h4 class="text-secondary mb-4">Your manager has been notify of your request. </h4></div>
        <div><h4 class="text-secondary mb-4">An email will be sent to your email once your request has been approved. </h4></div>
        @if(Auth::check())
        <a href="{{ route('home') }}" class="text-center">Home</a></p>
        @endif
    </div> 
@endif
@include('footer')