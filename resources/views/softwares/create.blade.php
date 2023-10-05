@include('header')
@include('headerMenu')
<link rel="stylesheet" href="{{ asset(mix('css/software.min.css')) }}">
<script src="{{ asset(mix('js/software.min.js')) }}" defer></script>
<div class="container ps-3 pe-3 pt-5">
    <h3>Software Registration</h3>
    <div class="pt-4">
        <form action="{{ route('softwares.regist') }}" method="POST" id="software-reg-form">
            @csrf
            <input type="text" name="id" value="{{ isset($software->id) ? $software->id : '' }}" hidden >
            <div class="soft-regist-category p-3 mb-4 rounded-3">
                <h4 class="text-start">Software Details</h4>
                <div class="row mb-2 ps-5 pe-3">
                    <div class="col-12 col-lg-6 g-3 form-floating">
                        <input type="text" class="form-control" name="software_name" id="software_name" placeholder="Software Name" value="{{ old('software_name', $software ? $software->software_name : '') }}" required>
                        <label class="text-center" for="software_name">Software Name</label>
                        @if ($errors->has('software_name'))
                        <p class="text-danger">{{ $errors->first('software_name') }}</p>
                        @endif
                    </div>
                </div>
                <div class="row mb-2 ps-5 pe-3">
                    <div class="col-12 col-lg-6 g-3 form-floating">
                        <select name="software_type_id" id="software_type_id" class="form-select form-control">
                            <option value=""></option>
                            @foreach ($software_types as $software_type)
                                <option {{ old('software_type_id', $software ? $software->software_type_id : '') == $software_type['id'] ? "selected" : "" }} value="{{  $software_type['id']  }}">{{ $software_type['type_name'] }}</option>
                            @endforeach
                            <option 
                                {{ (old('software_type_id', $software ? $software->software_type_id : '') == config('constants.SOFTWARE_TYPE_999') || ($new_software_type) )   ? "selected" : "" }} 
                                value="{{ config('constants.SOFTWARE_TYPE_999') }}">{{ config('constants.SOFTWARE_TYPE_999_NAME') }}</option>
                        </select>
                        <label  class="text-center" for="software_type_id">Software Type</label>
                        @if ($errors->has('software_type_id'))
                            <p class="text-danger">{{ $errors->first('software_type_id') }}</p>
                        @endif
                    </div>
                    <div class="col-12 col-lg-6 g-3 form-floating">
                        <input 
                            {{ (old('software_type_id', $software ? $software->software_type_id : '') == config('constants.SOFTWARE_TYPE_999') ||  ($new_software_type) ) ? "" : "hidden" }} 
                            type="text" class="form-control" name="new_software_type" id="new_software_type" placeholder="Purpose" 
                            value="{{ ($new_software_type) ? $new_software_type['type_name'] : old('new_software_type') }}">
                        <label {{ ( old('software_type_id', $software ? $software->software_type_id : '') == config('constants.SOFTWARE_TYPE_999') ||  ($new_software_type) ) ? "" : "hidden" }} 
                            class="text-center" for="new_software_type" id="new_software_type_label">New Software Type</label>
                        @if ($errors->has('new_software_type'))
                            <p class="text-danger" id = "new_software_type_error">{{ $errors->first('new_software_type') }}</p>
                        @endif
                    </div>
                </div>
                <div class="row mb-2 ps-5 pe-3">
                    <div class="col-12 col-lg-6 g-3 form-floating">
                        <textarea class="form-control" name="remarks" id="remarks" placeholder="Purpose" required> {{ old('remarks', $software ? $software->remarks : '') }}</textarea>
                        <label class="text-center" for="remarks">Purpose</label>
                        @if ($errors->has('remarks'))
                        <p class="text-danger">{{ $errors->first('remarks') }}</p>
                        @endif
                    </div>
                </div>
            </div>
            <div class="text-center p-2">
                <a class="btn btn-primary btn-lg mb-5 btn-prevent-multiple-submit" id="soft-reg-submit">
                    Submit <div id="soft-reg-submit-spinner" class="spinner-border text-light spinner-border-sm" role="status" style="display: none">
                        <span class="sr-only"></span>
                    </div>
                </a>
            </div>
        </form>
    </div>
</div>
@include('footer')