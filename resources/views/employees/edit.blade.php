@php
    $userInfo = Auth::user();
@endphp

@include('header')
<link rel="stylesheet" href="{{ asset(mix('css/employee.min.css')) }}">
<script src="{{ asset(mix('js/employee.min.js')) }}" defer></script>
@include('headerMenu')

<div class="container text-center ps-3 pe-3 pt-5">
    <h3 class="text-start">Account Update</h3>
    <div class="pt-4">
        @if ($errors->has('id'))
        <p class="text-danger mb-2">{{ $errors->first('id') }}</p>
        @endif
        <form action="{{ route('employees.update') }}" method="POST" id="emp-update-form">
            @csrf
            <input type="text" name="id" hidden value="{{ $employee->id }}">
            <div class="emp-regist-category p-3 mb-4 rounded-3">
                <h4 class="text-start">Employee Details</h4>
                <div class="row mb-2 ps-3 pe-3">
                    <div class="col-4 g-3 form-floating">
                        <input type="text" class="form-control" name="first_name" id="first_name" placeholder="First Name" value="{{ old('first_name', $employee->first_name) }}" required>
                        <label class="text-center" for="first_name">First Name</label>
                        @if ($errors->has('first_name'))
                        <p class="text-danger">{{ $errors->first('first_name') }}</p>
                        @endif
                    </div>
                    <div class="col-4 g-3 form-floating">
                        <input type="text" class="form-control" name="middle_name" id="middle_name" placeholder="Middle Name" value="{{ old('middle_name', $employee->middle_name) }}" required>
                        <label  class="text-center" for="middle_name">Middle Name</label>
                        @if ($errors->has('middle_name'))
                        <p class="text-danger">{{ $errors->first('middle_name') }}</p>
                        @endif
                    </div>
                    <div class="col-4 g-3 form-floating">
                        <input type="text" class="form-control" name="last_name" id="last_name" placeholder="Last Name" value="{{ old('last_name', $employee->last_name) }}" required>
                        <label  class="text-center" for="last_name">Last Name</label>
                        @if ($errors->has('last_name'))
                        <p class="text-danger">{{ $errors->first('last_name') }}</p>
                        @endif
                    </div>
                </div>
                <div class="row mb-2 ps-3 pe-3">
                    <div class="col-4 g-3 form-floating">
                        <input type="date" class="form-control" name="birthdate" id="birthdate" placeholder="birthdate" value="{{ old('birthdate', $employee->birthdate) }}" pattern="\d{4}-\d{2}-\d{2}" required>
                        <label  class="text-center" for="birthdate">Birth Date</label>
                        @if ($errors->has('birthdate'))
                        <p class="text-danger">{{ $errors->first('birthdate') }}</p>
                        @endif
                    </div>
                    <div class="col-lg-4 col-8 g-3 text-start">
                        <div class="d-flex align-items-center ps-1" style="height: 100%">
                            <div class="d-inline">
                                Gender:&nbsp&nbsp
                            </div>
                            <div class="d-inline">
                                <div class="form-check form-check-inline">
                                    <input type="radio" class="form-check-input" name="gender" id="femaleRadio" value="0" {{ old('gender', $employee->gender) == 0 ? "checked" : "" }}>
                                    <label class="form-check-label" for="femaleRadio">Female</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input type="radio" class="form-check-input" name="gender" id="maleRadio" value="1" {{ old('gender', $employee->gender) == 1 ? "checked" : "" }}>
                                    <label class="form-check-label" for="maleRadio">Male</label>
                                </div>
                            </div>
                        </div>
                        @if ($errors->has('gender'))
                        <p class="text-danger">{{ $errors->first('gender') }}</p>
                        @endif
                    </div>
                </div>
                <div class="row mb-2 ps-3 pe-3">
                    <div class="col-md-6 col-12 g-3 form-floating">
                        <select name="position" id="position" class="form-select form-control">
                            <option {{ old('position', $employee->position) == 1 ? "selected" : "" }} value="1">{{ config('constants.POSITION_1_NAME') }}</option>
                            <option {{ old('position', $employee->position) == 2 ? "selected" : "" }} value="2">{{ config('constants.POSITION_2_NAME') }}</option>
                            <option {{ old('position', $employee->position) == 3 ? "selected" : "" }} value="3">{{ config('constants.POSITION_3_NAME') }}</option>
                            <option {{ old('position', $employee->position) == 4 ? "selected" : "" }} value="4">{{ config('constants.POSITION_4_NAME') }}</option>
                            <option {{ old('position', $employee->position) == 5 ? "selected" : "" }} value="5">{{ config('constants.POSITION_5_NAME') }}</option>
                            <option {{ old('position', $employee->position) == 6 ? "selected" : "" }} value="6">{{ config('constants.POSITION_6_NAME') }}</option>
                            <option {{ old('position', $employee->position) == 7 ? "selected" : "" }} value="7">{{ config('constants.POSITION_7_NAME') }}</option>
                            <option {{ old('position', $employee->position) == 8 ? "selected" : "" }} value="8">{{ config('constants.POSITION_8_NAME') }}</option>
                            <option {{ old('position', $employee->position) == 9 ? "selected" : "" }} value="9">{{ config('constants.POSITION_9_NAME') }}</option>
                        </select>
                        <label  class="text-center" for="position">Position</label>
                        @if ($errors->has('position'))
                        <p class="text-danger">{{ $errors->first('position') }}</p>
                        @endif
                    </div>
                    @if ($manager_admin)
                    <div class="col-lg-2 col-4 g-3 ps-3" id="admin-check">
                        <div class="d-flex align-items-center" style="height: 100%">
                            <div class="form-check ">
                                <label class="form-check-label" for="is-admin">Admin</label>
                                <input type="checkBox" class="form-check-input" name="is_admin" id="is-admin" value="1" {{ old('is_admin', $employee->roles) == 1 ? "checked" : "" }}>
                            </div>
                            @if ($errors->has('is-admin'))
                            <p class="text-danger">{{ $errors->first('is-admin') }}</p>
                            @endif
                        </div>
                    </div>
                    @else
                    <div class="col-lg-2 col-4 g-3 ps-3">
                        <div class="d-flex align-items-center" style="height: 100%">
                            <div class="form-check ">
                                <label class="form-check-label" for="is-admin-detail">Admin</label>
                                <input type="checkBox" class="form-check-input" name="is_admin" id="is-admin-detail" value="1" {{ $employee->roles == config('constants.ADMIN_ROLE_VALUE') ? "checked" : "" }} disabled>
                            </div>
                            @if ($errors->has('is-admin'))
                            <p class="text-danger">{{ $errors->first('is-admin') }}</p>
                            @endif
                        </div>
                    </div>
                    @endif
                </div>
                <div class="row mb-2 ps-3 pe-3">
                    <div class="col-lg-2 col-md-4 col-6 g-3 ps-1">
                        <div class="d-flex align-items-center">
                            <div class="form-check ">
                                <label class="form-check-label" for="active-status">Active Status</label>
                                <input type="checkBox" class="form-check-input" name="active_status" id="active-status" value="1" {{ old('active_status', $employee->active_status) == 1 ? "checked" : "" }} {{ !$manager_admin ? 'disabled' : '' }}>
                                <input type="text" hidden value="0" name="active_status" id="active-status-hidden">
                            </div>
                        </div>
                        @if ($errors->has('active_status'))
                        <p class="text-danger">{{ $errors->first('active_status') }}</p>
                        @endif
                    </div>
                    <div class="col-lg-2 col-md-4 col-6 g-3 ps-1">
                        <div class="d-flex align-items-center">
                            <div class="form-check ">
                                <label class="form-check-label" for="server-manage-flag">Manage Server</label>
                                <input type="checkBox" class="form-check-input" name="server_manage_flag" id="server-manage-flag" value="1" {{ old('server_manage_flag', $employee->server_manage_flag) == 1 ? "checked" : "" }} {{ !$manager_admin ? 'disabled' : '' }}>
                                <input type="text" hidden value="0" name="server_manage_flag" id="server-manage-flag-hidden">
                            </div>
                        </div>
                        @if ($errors->has('server_manage_flag'))
                        <p class="text-danger">{{ $errors->first('server_manage_flag') }}</p>
                        @endif
                    </div>
                </div>
            </div>
            <div class="emp-regist-category mb-4 p-3 rounded-3">
                <h4 class="text-start">Contact Details</h4>
                <div class="row mb-2 ps-3 pe-3">
                    <div class="col-4 g-3 form-floating">
                        <input type="text" class="form-control" name="email" id="email" placeholder="Email" required value="{{ old('email', $employee->email) }}" readonly>
                        <label for="email" class="text-center">Email Address</label>
                        @if ($errors->has('email'))
                        <p class="text-danger">{{ $errors->first('email') }}</p>
                        @endif
                    </div>
                </div>
                <div class="row mb-2 ps-3 pe-3">
                    <div class="col-4 g-3">
                        <div class="input-group">
                            <span class="input-group-text">+63</span>
                            <div class="form-floating">
                                <input type="text" class="form-control" name="cellphone_number" id="contact" placeholder="Contact Number" required value="{{ old('cellphone_number', $employee->cellphone_number) }}">
                                <label for="contact" class="text-center">Contact Number</label>
                            </div>
                        </div>
                        @if ($errors->has('cellphone_number'))
                        <p class="text-danger">{{ $errors->first('cellphone_number') }}</p>
                        @endif
                    </div>
                    <div class="col-4 g-3">
                        <div class="input-group">
                            <span class="input-group-text">+63</span>
                            <div class="form-floating">
                                <input type="text" class="form-control" name="other_contact_number" id="other_contact" placeholder="Other Contact Number" value="{{ old('other_contact_number', $employee->other_contact_number) }}">
                                <label for="other_contact" class="text-center">Other Contact Number (optional)</label>
                            </div>
                        </div>
                        @if ($errors->has('other_contact_number'))
                        <p class="text-danger">{{ $errors->first('other_contact_number') }}</p>
                        @endif
                    </div>
                </div>
            </div>
            <div class="emp-regist-category mb-4 p-3 rounded-3">
                <h4 class="text-start">Current Address</h4>
                <div class="row mb-2 ps-3 pe-3">
                    <div class="col-12 g-3 form-floating">
                        <input type="text" class="form-control" name="current_address_street" id="cur-add-strt" placeholder="Street" required value="{{ old('current_address_street', $employee->current_address_street) }}">
                        <label for="cur-add-strt" class="text-center">Street</label>
                        @if ($errors->has('current_address_street'))
                        <p class="text-danger">{{ $errors->first('current_address_street') }}</p>
                        @endif
                    </div>
                </div>
                <div class="row mb-2 ps-3 pe-3">
                    <div class="col-4 g-3 form-floating">
                        <input type="text" class="form-control" name="current_address_city" id="cur-add-town" placeholder="Town" required value="{{ old('current_address_city', $employee->current_address_city) }}">
                        <label for="cur-add-town" class="text-center">Town/City</label>
                        @if ($errors->has('current_address_city'))
                        <p class="text-danger">{{ $errors->first('current_address_city') }}</p>
                        @endif
                    </div>
                    <div class="col-4 g-3 form-floating">
                        <input type="text" class="form-control" name="current_address_province" id="cur-add-prov" placeholder="Province" required value="{{ old('current_address_province', $employee->current_address_province) }}">
                        <label for="cur-add-prov" class="text-center">Province/Region</label>
                        @if ($errors->has('current_address_province'))
                        <p class="text-danger">{{ $errors->first('current_address_province') }}</p>
                        @endif
                    </div>
                    <div class="col-4 g-3 form-floating">
                        <input type="text" class="form-control" name="current_address_postalcode" id="cur-add-postal" placeholder="Postal Code" required value="{{ old('current_address_postalcode', $employee->current_address_postalcode) }}">
                        <label for="cur-add-postal" class="text-center">Postal Code</label>
                        @if ($errors->has('current_address_postalcode'))
                        <p class="text-danger">{{ $errors->first('current_address_postalcode') }}</p>
                        @endif
                    </div>
                </div>
            </div>
            <div class="emp-regist-category mb-4 p-3 rounded-3">
                <h4 class="text-start">Permanent Address</h4>
                <div class="row mb-2 ps-3 pe-3">
                    <div class="col-12 g-3 form-floating">
                        <input type="text" class="form-control" name="permanent_address_street" id="perm-add-strt" placeholder="Street" required value="{{ old('permanent_address_street', $employee->permanent_address_street) }}">
                        <label for="perm-add-strt" class="text-center">Street</label>
                        @if ($errors->has('permanent_address_street'))
                        <p class="text-danger">{{ $errors->first('permanent_address_street') }}</p>
                        @endif
                    </div>
                </div>
                <div class="row mb-2 ps-3 pe-3">
                    <div class="col-4 g-3 form-floating">
                        <input type="text" class="form-control" name="permanent_address_city" id="perm-add-town" placeholder="Town" required value="{{ old('permanent_address_city', $employee->permanent_address_city) }}">
                        <label for="perm-add-town" class="text-center">Town/City</label>
                        @if ($errors->has('permanent_address_city'))
                        <p class="text-danger">{{ $errors->first('permanent_address_city') }}</p>
                        @endif
                    </div>
                    <div class="col-4 g-3 form-floating">
                        <input type="text" class="form-control" name="permanent_address_province" id="perm-add-prov" placeholder="Province" required value="{{ old('permanent_address_province', $employee->permanent_address_province) }}">
                        <label for="perm-add-prov" class="text-center">Province/Region</label>
                        @if ($errors->has('permanent_address_province'))
                        <p class="text-danger">{{ $errors->first('permanent_address_province') }}</p>
                        @endif
                    </div>
                    <div class="col-4 g-3 form-floating">
                        <input type="text" class="form-control" name="permanent_address_postalcode" id="perm-add-postal" placeholder="Postal Code" required value="{{ old('permanent_address_postalcode', $employee->permanent_address_postalcode) }}">
                        <label for="perm-add-postal" class="text-center">Postal Code</label>
                        @if ($errors->has('permanent_address_postalcode'))
                        <p class="text-danger">{{ $errors->first('permanent_address_postalcode') }}</p>
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