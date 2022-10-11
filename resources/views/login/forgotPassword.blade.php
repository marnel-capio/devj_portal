<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta http-equiv="X-UA-Compatible" content="ie=edge">
        <title>Dev J Portal</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-Zenh87qX5JnK2Jl0vWa8Ck2rdkQ2Bzep5IDxbcnCeuOxjzrPF/et3URy9Bv1WTRi" crossorigin="anonymous">
        <style>
            .resetPassword-form
            {
                padding-top: 1.5rem;
                padding-bottom: 1.5rem;
            }
            .resetPassword-form .row
            {
                margin-left: 1rem;
                margin-right: 1rem;
            }
        </style>
    </head>
    <body>

    <main class="resetPassword-form">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-header">
                            <div class="fw-bold header_text">
                                Please enter your AWS email address
                            </div>
                        </div>
                        <div class="card-body">
                            <form action=" {{ route('login.forgotPassword') }}" method="POST">
                                @csrf
                                <div class="form-group row">
                                    <div class="col-md-8">
                                        <input type="text" id="email_address" class="form-control" name="email_address" value="{{ old('email_address') }}" autocomplete="off" autofocus>
                                        @if ($errors->has('email_address'))
                                            <span class="text-danger">{{ $errors->first('email_address') }}</span>
                                        @endif
                                    </div>
                                    <div class="col-md-4">
                                        <button type="submit" class="btn btn-primary">
                                            Submit
                                        </button>
                                    </div>
                                    @if (Session::has('successMsg'))
                                        <span class="text-success">{{ Session::get('successMsg') }}</span>
                                    @endif
                                </div>
                                <div class="form-group row" style="padding-top: 1rem">
                                    <div class="text-center">
                                        <a href="{{ route('login') }}">Go back to login page</a>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </main>



      <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-OERcA2EqjJCMA+/3y+gxIOqMEjwtxJY7qPCqsdltbNJuaOe923+mo//f6V8Qbsw3" crossorigin="anonymous"></script>
    </body>
</html>