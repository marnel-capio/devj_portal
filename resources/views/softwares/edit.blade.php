@php
    $userInfo = Auth::user();
@endphp

@include('header')
<link rel="stylesheet" href="{{ asset(mix('css/software.min.css')) }}">
<script src="{{ asset(mix('js/software.min.js')) }}" defer></script>
@include('headerMenu')

<div class="container text-center ps-3 pe-3 pt-5">
    <h3 class="text-start">Account Update</h3>
    <div class="pt-4">
        @if ($errors->has('id'))
        <p class="text-danger mb-2">{{ $errors->first('id') }}</p>
        @endif
        <form action="{{ route('softwares.update') }}" method="POST" id="soft-update-form">
            @csrf
            <input type="text" name="id" hidden value="{{ $employee->id }}">
            <div class="soft-regist-category p-3 mb-4 rounded-3">
                <h4 class="text-start">Employee Details</h4>
                <div class="row mb-2 ps-3 pe-3">
                    <div class="col-4 g-3 form-floating">
                        <input type="text" class="form-control" name="software_status" id="software_status" placeholder="Status" value="{{ $software->approved_status }}" required @readonly($readOnly)>
                        <label class="text-center" for="software_status">Status</label>
                        @if ($errors->has('software_status'))
                        <p class="text-danger">{{ $errors->first('software_status') }}</p>
                        @endif
                    </div>
                </div>
                <div class="row mb-2 ps-3 pe-3">
                    <div class="col-4 g-3 form-floating">
                        <input type="text" class="form-control" name="software_name" id="software_name" placeholder="Software Name" value="{{ old('sofwaret_name', $software->software_name) }}" required>
                        <label class="text-center" for="software_name">Software Name</label>
                        @if ($errors->has('software_name'))
                        <p class="text-danger">{{ $errors->first('software_name') }}</p>
                        @endif
                    </div>
                </div>
                <div class="row mb-2 ps-3 pe-3">
                    <div class="col-4 g-3 form-floating">
                        <select name="software_type" id="software_type" class="form-select form-control">
                            <option {{ old('software_type', $software->position) == 1 ? "selected" : "" }} value="1">{{ config('constants.SOFTWARE_TYPE_1') }}</option>
                            <option {{ old('software_type', $software->position) == 2 ? "selected" : "" }} value="2">{{ config('constants.SOFTWARE_TYPE_2') }}</option>
                            <option {{ old('software_type', $software->position) == 3 ? "selected" : "" }} value="3">{{ config('constants.SOFTWARE_TYPE_3') }}</option>
                            <option {{ old('software_type', $software->position) == 4 ? "selected" : "" }} value="4">{{ config('constants.SOFTWARE_TYPE_4') }}</option>
                            <option {{ old('software_type', $software->position) == 5 ? "selected" : "" }} value="5">{{ config('constants.SOFTWARE_TYPE_5') }}</option>
                            <option {{ old('software_type', $software->position) == 6 ? "selected" : "" }} value="6">{{ config('constants.SOFTWARE_TYPE_6') }}</option>
                        </select>
                        <label  class="text-center" for="software_type">Position</label>
                        @if ($errors->has('software_type'))
                        <p class="text-danger">{{ $errors->first('software_type') }}</p>
                        @endif
                    </div>
                </div>
                <div class="row mb-2 ps-3 pe-3">
                    <div class="col-4 g-3 form-floating">
                        <input type="text" class="form-control" name="software_purpose" id="software_purpose" placeholder="Purpose" value="{{ old('software_purpose', $software->remarks) }}" required>
                        <label class="text-center" for="software_purpose">Purpose</label>
                        @if ($errors->has('software_purpose'))
                        <p class="text-danger">{{ $errors->first('software_purpose') }}</p>
                        @endif
                    </div>
                </div>
                <div class="row mb-2 ps-3 pe-3">
                    <div class="col-4 g-3 form-floating">
                        <input type="text" class="form-control" name="software_createdby" id="software_createdby" placeholder="Created By" value="{{ $software->created_by }}" required @readonly($readOnly)>
                        <label class="text-center" for="software_createdby">Created By</label>
                        @if ($errors->has('software_createdby'))
                        <p class="text-danger">{{ $errors->first('software_createdby') }}</p>
                        @endif
                    </div>
                </div>
                <div class="row mb-2 ps-3 pe-3">
                    <div class="col-4 g-3 form-floating">
                        <input type="text" class="form-control" name="software_approvedby" id="software_approvedby" placeholder="Approved By" value="{{ $software->approved_by }}" required @readonly($readOnly)>
                        <label class="text-center" for="software_approvedby">Created By</label>
                        @if ($errors->has('software_approvedby'))
                        <p class="text-danger">{{ $errors->first('software_approvedby') }}</p>
                        @endif
                    </div>
                </div>
            </div>
            <div class="text-center p-2">
                <button class="btn btn-primary btn-lg mb-5 btn-prevent-multiple-submit" id="emp-update-submit" type="submit">Update</button>
            </div>
        </form>
    </div>
</div>

@include('footer')