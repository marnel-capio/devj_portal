@include('header')

<div class="vh-100 d-flex justify-content-center align-items-center">
    <div class="col-lg-4 col-sm-6 col-8 p-4 shadow-sm border rounded-4 border-secondary" style="background-color: #F9F9F9">
        <h5 class="text-start header_text fw-bold mb-4">Please enter your AWS email address</h5>
            <form action="{{ route('login.forgotPassword') }}" method="POST">
                @csrf
                <div class="row mb-3 justify-content-center">
                    <div class="col-md-9 mb-2">
                        <input type="text" class="form-control bg-info bg-opacity-10 border border-primary " id="email_address" name="email_address" value="{{ old('email_address') }}" autocomplete="off" autofocus>
                        @if ($errors->has('email_address'))
                            <span class="text-danger">{{ $errors->first('email_address') }}</span>
                        @endif
                    </div>
                    <div class="col-md-3 text-start">
                        <button type="submit" class="btn btn-primary">
                            Submit
                        </button>
                    </div>
                    @if (Session::has('successMsg'))
                        <span class="text-success">{{ Session::get('successMsg') }}</span>
                    @endif
                </div>
                <div class="col-12 text-center">
                    <a class="small" href="{{ route('login') }}" style="text-decoration:none">Go back to login page</a>
                </div>
            </form>
    </div>
</div>

        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-OERcA2EqjJCMA+/3y+gxIOqMEjwtxJY7qPCqsdltbNJuaOe923+mo//f6V8Qbsw3" crossorigin="anonymous"></script>
    </body>
</html>