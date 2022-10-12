<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta http-equiv="X-UA-Compatible" content="ie=edge">
        <title>Dev J Portal</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-Zenh87qX5JnK2Jl0vWa8Ck2rdkQ2Bzep5IDxbcnCeuOxjzrPF/et3URy9Bv1WTRi" crossorigin="anonymous">
    </head>
    <body>

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

      <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-OERcA2EqjJCMA+/3y+gxIOqMEjwtxJY7qPCqsdltbNJuaOe923+mo//f6V8Qbsw3" crossorigin="anonymous"></script>
    </body>
</html>