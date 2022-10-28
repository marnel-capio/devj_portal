@include('header')

<div class="vh-100 d-flex flex-column justify-content-center align-items-center">

    <p class="mb-1"><h3 class="text-center text-secondary mb-4 ">Error! | {{ $error }}</h3>
    <p><a href="{{ URL::previous() }}" class="text-center">Back</a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a href="{{ route('home') }}" class="text-center">Home</a></p>
</div>



@include('footer')