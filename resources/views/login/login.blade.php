@include('header')

<div class="vh-100 d-flex justify-content-center align-items-center">
    <div class="col-lg-4 col-sm-6 col-8 p-4 shadow-sm border rounded-4 border-secondary">
        <h4 class="text-center mb-4 fw-bold">DEV J PORTAL</h4>
            <form action="{{ route('login') }}" method="POST">
                @csrf
                <div class="row mb-3">
                    <label for="email_address" class="col-form-label col-3 text-end">Username:</label>
                    <div class="col-9">
                        <input type="text" class="form-control bg-info bg-opacity-10 border border-primary " id="email_address" name="email_address" value="{{ old('email_address') }}">
                        @if ($errors->has('email_address'))
                        <span class="text-danger">{{ $errors->first('email_address') }}</span>
                        @endif
                    </div>
                </div>
                <div class="row mb-3">
                    <label for="password" class="col-form-label col-3 text-end">Password:</label>
                    <div class="col-9">
                        <input type="password" class="form-control bg-info bg-opacity-10 border border-primary" id="password" name="password" value="{{ old('password') }}">
                        @if ($errors->has('password'))
                        <span class="text-danger">{{ $errors->first('password') }}</span>
                        @endif
                    </div>
                </div>
                <p class="small  text-center"><a class="text-primary" href="{{ route('login.forgotPassword') }}">Forgot password?</a></p>
                <div class="col-12 text-end">
                    <button class="btn btn-primary" type="submit">Login</button>
                </div>
            </form>
    </div>
</div>

@include('footer')