@include('header')

<div class="vh-100 d-flex justify-content-center align-items-center">
    <div class="col-xl-4 col-lg-5  col-md-7  col-sm-8 col-9 p-4 shadow-sm border rounded-4 border-secondary" style="background-color: #F9F9F9">
        <h4 class="text-center mb-4 fw-bold"> DEV J PORTAL</h4>
            <form action="{{ route('login') }}" method="POST">
                @csrf
                <div class="row mb-2 ps-2 pe-2">
                    <label for="email_address" class="col-form-label fw-bold">Username:</label>
                    <div class="">
                        <input type="text" class="form-control bg-info bg-opacity-10 border border-primary " id="email_address" name="email_address" value="{{ old('email_address') }}">
                        @if ($errors->has('email_address'))
                        <span class="text-danger">{{ $errors->first('email_address') }}</span>
                        @endif
                    </div>
                </div>
                <div class="row mb-3  ps-2 pe-2">
                    <label for="password" class="col-form-label fw-bold">Password:</label>
                    <div class="">
                        <input type="password" class="form-control bg-info bg-opacity-10 border border-primary" id="password" name="password" value="{{ old('password') }}">
                        @if ($errors->has('password'))
                        <span class="text-danger">{{ $errors->first('password') }}</span>
                        @endif
                    </div>
                </div>
                <div class="row  ps-2 pe-2">
                        <div class="row ms-1 mb-4">
                            <div class="form-check col-6 text-start">
                                <input type="checkbox" id="remember" name="remember" class="form-check-input" {{ old('remember') === 'on' ?'checked' : '' }}>
                                <label for="remember" class="small form-check-label" >Remember me</label>
                            </div>
                            <div class="small col-6 text-end ">
                                <a class="text-primary" href="{{ route('login.forgotPassword') }}" style="text-decoration:none">forgot password?</a>
                            </div>
                        </div>
                        <div class="text-center">
                            <button class="btn btn-primary ps-5 pe-5 fw-bold" type="submit">Login</button>
                        </div>
                    </div>
                </div>
            </form>
    </div>
</div>

@include('footer')