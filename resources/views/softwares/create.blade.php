@include('header')
<link rel="stylesheet" href="{{ asset(mix('css/software.min.css')) }}">
<script src="{{ asset(mix('js/software.min.js')) }}" defer></script>
<div class="container text-center ps-3 pe-3 pt-5">
    <h3 class="text-start">Software Registration</h3>
    <div class="pt-4">
        <form action="{{ route('softwares.regist') }}" method="POST">
            @csrf
            <input type="text" name="id" value="{{ isset($software->id) ? $software->id : '' }}" hidden >
            <div class="soft-regist-category p-3 mb-4 rounded-3">
                <h4 class="text-start">Software Details</h4>
                <div class="row mb-2 ps-3 pe-3">
                    <div class="col-4 g-3 form-floating">
                        <input type="text" class="form-control" name="software_name" id="software_name" placeholder="Software Name" value="{{ old('software_name', $software ? $software->software_name : '') }}" required>
                        <label class="text-center" for="software_name">Software Name</label>
                        @if ($errors->has('software_name'))
                        <p class="text-danger">{{ $errors->first('software_name') }}</p>
                        @endif
                    </div>
                </div>
                <div class="row mb-2 ps-3 pe-3">
                    <div class="col-4 g-3 form-floating">
                        <select name="software_type" id="software_type" class="form-select form-control">
                            <option {{ old('software_type', $software ? $software->type : '') == 1 ? "selected" : "" }} value="1">{{ config('constants.SOFTWARE_TYPE_1_NAME') }}</option>
                            <option {{ old('software_type', $software ? $software->type : '') == 2 ? "selected" : "" }} value="2">{{ config('constants.SOFTWARE_TYPE_2_NAME') }}</option>
                            <option {{ old('software_type', $software ? $software->type : '') == 3 ? "selected" : "" }} value="3">{{ config('constants.SOFTWARE_TYPE_3_NAME') }}</option>
                            <option {{ old('software_type', $software ? $software->type : '') == 4 ? "selected" : "" }} value="4">{{ config('constants.SOFTWARE_TYPE_4_NAME') }}</option>
                            <option {{ old('software_type', $software ? $software->type : '') == 5 ? "selected" : "" }} value="5">{{ config('constants.SOFTWARE_TYPE_5_NAME') }}</option>
                            <option {{ old('software_type', $software ? $software->type : '') == 6 ? "selected" : "" }} value="6">{{ config('constants.SOFTWARE_TYPE_6_NAME') }}</option>
                        </select>
                        <label  class="text-center" for="software_type">Software Type</label>
                        @if ($errors->has('software_type'))
                        <p class="text-danger">{{ $errors->first('software_type') }}</p>
                        @endif
                    </div>
                </div>
                <div class="row mb-2 ps-3 pe-3">
                    <div class="col-4 g-3 form-floating">
                        <input type="text" class="form-control" name="software_purpose" id="software_purpose" placeholder="Purpose" value="{{ old('software_purpose', $software ? $software->remarks : '') }}" required>
                        <label class="text-center" for="software_purpose">Purpose</label>
                        @if ($errors->has('software_purpose'))
                        <p class="text-danger">{{ $errors->first('software_purpose') }}</p>
                        @endif
                    </div>
                </div>
            </div>
            <div class="text-center p-2">
                <button class="btn btn-primary btn-lg mb-5 btn-prevent-multiple-submit" id="soft-reg-submit" type="submit">Submit</button>
            </div>
        </form>
    </div>
</div>
@include('footer')