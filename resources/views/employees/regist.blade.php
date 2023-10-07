@include('header')
<link rel="stylesheet" href="{{ asset(mix('css/employee.min.css')) }}">
<script src="{{ asset(mix('js/employee.min.js')) }}" defer></script>
<div class="container text-center ps-3 pe-3 pt-5">
    <h3 class="text-start">Account Registration</h3>
    <div class="pt-4">
        <form action="{{ route('employees.regist') }}" method="POST">
            @csrf
            <input type="text" name="id" value="{{ isset($employee->id) ? $employee->id : '' }}" hidden >
            <div class="emp-regist-category p-3 mb-4 rounded-3">
                <h4 class="text-start">Employee Details</h4>
                <div class="row mb-2 ps-3 pe-3">
                    <div class="col-12 col-md-4 g-3 form-floating">
                        <input type="text" class="form-control" name="first_name" id="first_name" placeholder="First Name" value="{{ old('first_name', $employee ? $employee->first_name : '') }}" required>
                        <label class="text-center" for="first_name">First Name</label>
                        @if ($errors->has('first_name'))
                        <p class="text-danger">{{ $errors->first('first_name') }}</p>
                        @endif
                    </div>
                    <div class="col-12 col-md-3 g-3 form-floating"  data-bs-toggle="tooltip" data-bs-placement="top" data-bs-html="true" title="<em>optional</em>: Middle name">
                        <input type="text" class="form-control" name="middle_name" id="middle_name" placeholder="Middle Name" value="{{ old('middle_name', $employee ? $employee->middle_name : '') }}">
                        <label  class="text-center" for="middle_name"><em>Middle Name</em></label>
                        @if ($errors->has('middle_name'))
                        <p class="text-danger">{{ $errors->first('middle_name') }}</p>
                        @endif
                    </div>
                    <div class="col-12 col-md-4 g-3 form-floating">
                        <input type="text" class="form-control" name="last_name" id="last_name" placeholder="Last Name" value="{{ old('last_name', $employee ? $employee->last_name : '') }}" required>
                        <label  class="text-center" for="last_name">Last Name</label>
                        @if ($errors->has('last_name'))
                        <p class="text-danger">{{ $errors->first('last_name') }}</p>
                        @endif
                    </div>
                    <div class="col-12 col-md-1 g-3 form-floating" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-html="true" title="<em>optional</em>: Suffix (Jr, II, III, etc.)">
                        <input type="text" class="form-control" name="name_suffix" id="name_suffix" placeholder="*Suffix" value="{{ old('name_suffix', $employee ? $employee->name_suffix : '') }}">
                        <label  class="text-center small" for="name_suffix"><em>Suffix</em></label>
                        @if ($errors->has('name_suffix'))
                        <p class="text-danger">{{ $errors->first('name_suffix') }}</p>
                        @endif
                    </div>
                </div>
                <div class="row mb-2 ps-3 pe-3">
                    <div class="col-12 col-sm-5 g-3 form-floating">
                        <input type="date" class="form-control" name="birthdate" id="birthdate" placeholder="birthdate" value="{{ old('birthdate', $employee ? $employee->birthdate : '') }}" pattern="\d{4}-\d{2}-\d{2}" required>
                        <label  class="text-center" for="birthdate">Birth Date</label>
                        @if ($errors->has('birthdate'))
                        <p class="text-danger">{{ $errors->first('birthdate') }}</p>
                        @endif
                    </div>
                    <div class="col-12 col-sm-4 col-8 g-3 text-start">
                        <div class="input-box-radio">
                            <div class="d-inline">
                                Gender:&nbsp&nbsp
                            </div>
                            <div class="d-inline">
                                <div class="form-check form-check-inline">
                                    <input type="radio" class="form-check-input" name="gender" id="femaleRadio" value="0" {{ old('gender', $employee ? $employee->gender : 0) == 0 ? "checked" : "" }} required>
                                    <label class="form-check-label" for="femaleRadio">Female</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input type="radio" class="form-check-input" name="gender" id="maleRadio" value="1" {{ old('gender', $employee ? $employee->gender : '') == 1 ? "checked" : "" }}>
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
                            @foreach (config('constants.POSITIONS') as $value => $name)
                                <option {{ old('position', $employee ? $employee->position : '') == $value ? "selected" : "" }} value="{{ $value 
                                     }}"> {{ $name }}</option>
                            @endforeach
                        </select>
                        <label  class="text-center" for="position">Position</label>
                        @if ($errors->has('position'))
                        <p class="text-danger">{{ $errors->first('position') }}</p>
                        @endif
                    </div>
                </div>
            </div>

{{-- Contact Details --}}
<div class="emp-regist-category mb-4 p-3 rounded-3">
    <h4 class="text-start">Contact Details</h4>
    <div class="row mb-2 ps-3 pe-3">
        <div class="col-12 col-md-4 g-3 form-floating"
            data-bs-toggle="tooltip" data-bs-placement="top"
            title="Please enter AWS Email Address only">
            <input type="text" class="form-control" name="email" id="email" placeholder="Email" required value="{{ old('email', $employee ? $employee->email : '') }}">
            <label for="email" class="text-center">AWS Email Address</label>
            @if ($errors->has('email'))
            <p class="text-danger">{{ $errors->first('email') }}</p>
            @endif
        </div>
    </div>
    <div class="row mb-2 ps-3 pe-3">
        <div class="col-12 col-md-4 g-3">
            <div class="input-group">
                <span class="input-group-text">+63</span>
                <div class="form-floating">
                    <input type="text" class="form-control" name="cellphone_number" id="contact" placeholder="Contact Number" required value="{{ old('cellphone_number', $employee ? $employee->cellphone_number : '') }}">
                    <label for="contact" class="text-center">Contact Number</label>
                </div>
            </div>
            @if ($errors->has('cellphone_number'))  
            <p class="text-danger">{{ $errors->first('cellphone_number') }}</p>
            @endif
        </div>

        <div class="col-12 col-md-4 g-3 form-floating"
            data-bs-toggle="tooltip" data-bs-placement="right"
            data-bs-html="true" title="<em>optional</em>: Input secondary phone number or email">
            <input type="text" class="form-control" name="other_contact_info" id="other_contact" placeholder="Other Contact Info" value="{{ old('other_contact_info', $employee ? $employee->other_contact_info : '') }}">
            <label for="other_contact" class="text-center">Other Contact Info (optional)</label>
            @if ($errors->has('other_contact_info'))
            <p class="text-danger">{{ $errors->first('other_contact_info') }}</p>
            @endif
        </div>
    </div>
</div>

{{-- Password --}}
<div class="emp-regist-category mb-4 p-3 rounded-3">
    <div class="col col-12 col-md-8">
        <h4 class="text-start">Portal Password</h4>
        <div class="row mb-2 ps-3 pe-3">
            <div class="col-12 col-md-6 g-3 form-floating">
                <input type="password" class="form-control" name="password" id="emp-password" placeholder="Password" required>
                <label for="emp-password" class="text-center">Password</label>
                @if ($errors->has('password'))
                <p class="text-danger">{{ $errors->first('password') }}</p>
                @endif
                <small class="form-text text-secondary"><em>Minimum of 8 characters</em></small>
            </div>
            <div class="col-12 col-md-6 g-3 form-floating">
                <input type="password" class="form-control" name="confirm_password" id="emp-confirm-password" placeholder="Confirm Password" required>
                <label for="emp-confirm-password" class="text-center">Confirm Password</label>
                <p id="confirm-pass-text"></p>
            </div>
        </div>
    </div>
</div>
            
            {{-- Passport Details section --}}
            <div class="emp-regist-category mb-4 p-3 rounded-3">
                <h4 class="text-start">Passport Information</h4>
                <div class="row row-list">
                    <div class="col-lg-1 col-12 text-start" id="status-label">
                        Status: 
                    </div>
                    <div class="col-lg-11 col-12" style="text-align: left">
                        @foreach (config('constants.PASSPORT_STATUS_LIST') as $name => $details)
                             <input class="passport_status btn-check" type="radio" name="passport_status" id="status-{{$name}}" value="{{$details['val']}}" {{ old('passport_status', $employee ? $employee->passport_status : $details['val'] ) ==  $details['val']  ? "checked" : "" }}>
                                <label class="form-check-label passport-status btn btn-outline-primary" for="status-{{$name}}">
                                    {{  $details['name'] }}
                                </label>
                        @endforeach

                    </div>
                </div>

                <div class="row my-2 ps-3 pe-3">
                    <div class="col-12 g-3 form-floating">
                        <div id="passport-status-switch-spinner" class="spinner-border text-primary spinner-border-lg" role="status" style="display: none">
                            <span class="sr-only"></span>
                        </div>
                    </div>
                </div>
                

                {{-- With Valid Passport section --}}
                <div id="withPassport" class="">
                    <div class="row mb-2 ps-3 pe-3">
                        <div class="col-12 col-md-4 g-3 form-floating">
                            <input type="text" class="form-control" name="passport_number" id="passport_number" placeholder="Passport Number" value="{{ old('passport_number', $employee ? $employee->passport_number : '') }}">
                            <label for="passport_number" class="text-center">Passport Number</label>
                            @if ($errors->has('passport_number'))
                            <p class="text-danger">{{ $errors->first('passport_number') }}</p>
                            @endif
                        </div>
                        <div class="col-12 col-md-4 g-3 form-floating">
                            <select name="passport_type" id="passport_type" class="form-select form-control">
                                <option value="">Select passport type</option>
                                <option {{ old('passport_type', $employee ? $employee->passport_type : '') == config('constants.PASSPORT_TYPE_ORDINARY_VALUE')      ? "selected" : "" }} value="{{ config('constants.PASSPORT_TYPE_ORDINARY_VALUE')   }} ">{{ config('constants.PASSPORT_TYPE_1_NAME') }}</option>
                                <option {{ old('passport_type', $employee ? $employee->passport_type : '') == config('constants.PASSPORT_TYPE_OFFICIAL_VALUE')      ? "selected" : "" }} value="{{ config('constants.PASSPORT_TYPE_OFFICIAL_VALUE')   }} ">{{ config('constants.PASSPORT_TYPE_2_NAME') }}</option>
                                <option {{ old('passport_type', $employee ? $employee->passport_type : '') == config('constants.PASSPORT_TYPE_DIPLOMATIC_VALUE')    ? "selected" : "" }} value="{{ config('constants.PASSPORT_TYPE_DIPLOMATIC_VALUE') }} ">{{ config('constants.PASSPORT_TYPE_3_NAME') }}</option>
                            </select>
                            <label  class="text-center" for="passport_type">Passport Type</label>
                            @if ($errors->has('passport_type'))
                            <p class="text-danger">{{ $errors->first('passport_type') }}</p>
                            @endif
                        </div>
                        <div class="col-12 col-md-4 g-3 form-floating">
                            <input type="text" class="form-control" name="issuing_authority" id="issuing_authority" placeholder="Issuing Authority" value="{{ old('issuing_authority', $employee ? $employee->issuing_authority : '') }}">
                            <label for="issuing_authority" class="text-center">Issuing Authority</label>
                            @if ($errors->has('issuing_authority'))
                            <p class="text-danger">{{ $errors->first('issuing_authority') }}</p>
                            @endif
                        </div>
                        <div class="col-6 col-md-4 g-3 form-floating">
                            <input type="date" class="form-control" name="date_of_issue" id="date_of_issue" placeholder="date_of_issue" value="{{ old('date_of_issue', $employee ? $employee->date_of_issue : '') }}" pattern="\d{4}-\d{2}-\d{2}">
                            <label  class="text-center" for="date_of_issue">Date of Issue</label>
                            @if ($errors->has('date_of_issue'))
                            <p class="text-danger">{{ $errors->first('date_of_issue') }}</p>
                            @endif
                        </div>
                        <div class="col-6 col-md-4 g-3 form-floating">
                            <input type="date" class="form-control" name="passport_expiration_date" id="passport_expiration_date" placeholder="passport_expiration_date" value="{{ old('passport_expiration_date', $employee ? $employee->passport_expiration_date : '') }}" pattern="\d{4}-\d{2}-\d{2}">
                            <label  class="text-center" for="passport_expiration_date">Valid Until</label>
                            @if ($errors->has('passport_expiration_date'))
                            <p class="text-danger">{{ $errors->first('passport_expiration_date') }}</p>
                            @endif
                        </div>
                        <div class="col-12 col-md-4 g-3 form-floating" data-bs-toggle="tooltip" data-bs-placement="top" title="optional: Place of issue is usually found at the back cover of the passport">
                            <input type="text" class="form-control" name="place_of_issue" id="place_of_issue" placeholder="Issuing Authority" value="{{ old('place_of_issue', $employee ? $employee->place_of_issue : '') }}">
                            <label for="place_of_issue" class="text-center">Place of Issue <em>(optional)</em></label>
                            @if ($errors->has('place_of_issue'))
                            <p class="text-danger">{{ $errors->first('place_of_issue') }}</p>
                            @endif
                        </div>
                        {{-- If passport_status radio buttons values are not valid --}}
                        @if ($errors->has('passport_status'))
                        <p class="text-danger">{{ $errors->first('passport_status') }}</p>
                        @endif
                    </div>
                </div>
                
                {{-- With Passport Appointment section --}}
                <div id="withAppointment" class="d-none">
                    <div class="row mb-2 ps-3 pe-3">
                        <div class="col-6 g-3 form-floating">
                            <input type="date" class="form-control" name="date_of_appointment" id="date_of_appointment" placeholder="Date of Appointment: Passport Application" value="{{ old('date_of_appointment', $employee ? $employee->date_of_appointment : '') }}" pattern="\d{4}-\d{2}-\d{2}">
                            <label  class="text-center" for="date_of_appointment">Date of Appointment: Passport Application</label>
                            @if ($errors->has('date_of_appointment'))
                            <p class="text-danger">{{ $errors->first('date_of_appointment') }}</p>
                            @endif
                        </div>
                    </div>
                </div>
                
                {{-- Without Passport Appointment section --}}
                <div id="withoutAppointment" class="d-none" style="text-align: left">
                    <div class="row mb-2 ps-3 pe-3">
                        <div class="row mb-2 ps-5 pe-3">
                            <div class="col-lg-9 g-3">
                                <h5>Reason for No Appointment</h5>
                                <textarea class="form-control" name="no_appointment_reason"  rows="3" id="no_appointment_reason" placeholder="Please state the reason for not having a passport appointment">{{ old('no_appointment_reason', $employee ? $employee->no_appointment_reason : '') }}</textarea>
                            </div>
                            @if ($errors->has('no_appointment_reason'))
                            <p class="text-danger text-start">{{ $errors->first('no_appointment_reason') }}</p>
                            @endif
                        </div>
                    </div>
                </div>
                
                {{-- Waiting for Delivery section --}}
                <div id="waitingDelivery" class="d-none">
                    <div class="row mb-2 ps-3 pe-3">
                        <div class="col-6 g-3 form-floating">
                            <input type="date" class="form-control" name="date_of_delivery" id="date_of_delivery" placeholder="Expected Date of Delivery" value="{{ old('date_of_delivery', $employee ? $employee->date_of_delivery : '') }}" pattern="\d{4}-\d{2}-\d{2}">
                            <label  class="text-center" for="date_of_delivery">Expected Date of Delivery</label>
                            @if ($errors->has('date_of_delivery'))
                            <p class="text-danger">{{ $errors->first('date_of_delivery') }}</p>
                            @endif
                        </div>
                    </div>
                </div>

            </div>

            {{-- Permanent Address --}}
            <div class="emp-regist-category mb-4 p-3 rounded-3">
                <h4 class="text-start">Permanent Address</h4>
                <div class="row mb-2 ps-3 pe-3">
                    <div class="col-12 g-3 form-floating">
                        <input type="text" class="form-control" name="permanent_address_street" id="perm-add-strt" placeholder="Street" required value="{{ old('permanent_address_street', $employee ? $employee->permanent_address_street : '') }}">
                        <label for="perm-add-strt" class="text-center">Street</label>
                        @if ($errors->has('permanent_address_street'))
                        <p class="text-danger">{{ $errors->first('permanent_address_street') }}</p>
                        @endif
                    </div>
                </div>
                <div class="row mb-2 ps-3 pe-3">
                    <div class="col-12 col-lg-4 g-3 form-floating">
                        <input type="text" class="form-control" name="permanent_address_city" id="perm-add-town" placeholder="Town" required value="{{ old('permanent_address_city', $employee ? $employee->permanent_address_city : '') }}">
                        <label for="perm-add-town" class="text-center">Town/City</label>
                        @if ($errors->has('permanent_address_city'))
                        <p class="text-danger">{{ $errors->first('permanent_address_city') }}</p>
                        @endif
                    </div>
                    <div class="col-12 col-lg-4 g-3 form-floating">
                        <input type="text" class="form-control" name="permanent_address_province" id="perm-add-prov" placeholder="Province" required value="{{ old('permanent_address_province', $employee ? $employee->permanent_address_province : '') }}">
                        <label for="perm-add-prov" class="text-center">Province/Region</label>
                        @if ($errors->has('permanent_address_province'))
                        <p class="text-danger">{{ $errors->first('permanent_address_province') }}</p>
                        @endif
                    </div>
                    <div class="col-12 col-lg-4 g-3 form-floating">
                        <input type="text" class="form-control" name="permanent_address_postalcode" id="perm-add-postal" placeholder="Postal Code" required value="{{ old('permanent_address_postalcode', $employee ? $employee->permanent_address_postalcode : '') }}">
                        <label for="perm-add-postal" class="text-center">Postal Code</label>
                        @if ($errors->has('permanent_address_postalcode'))
                        <p class="text-danger">{{ $errors->first('permanent_address_postalcode') }}</p>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Current Address --}}
            <div class="emp-regist-category mb-4 p-3 rounded-3">
                <div class="d-flex justify-content-between">
                    <h4 class="text-start">Current Address</h4>    
                    <input type="checkbox" class="btn btn-check btn-primary btn-sm" name="copy_permanent_address" id="copy-permanent-address">
                    <label class="btn btn-outline-primary" for="copy-permanent-address"><i>same as Permanent</i></label>
                </div>
                <div class="row mb-2 ps-3 pe-3">
                    <div class="col-12 g-3 form-floating">
                        <input type="text" class="form-control" name="current_address_street" id="cur-add-strt" placeholder="Street" required value="{{ old('current_address_street', $employee ? $employee->current_address_street : '') }}">
                        <label for="cur-add-strt" class="text-center">Street</label>
                        @if ($errors->has('current_address_street'))
                        <p class="text-danger">{{ $errors->first('current_address_street') }}</p>
                        @endif
                    </div>
                </div>
                <div class="row mb-2 ps-3 pe-3">
                    <div class="col-12 col-lg-4 g-3 form-floating">
                        <input type="text" class="form-control" name="current_address_city" id="cur-add-town" placeholder="Town" required value="{{ old('current_address_city', $employee ? $employee->current_address_city : '') }}">
                        <label for="cur-add-town" class="text-center">Town/City</label>
                        @if ($errors->has('current_address_city'))
                        <p class="text-danger">{{ $errors->first('current_address_city') }}</p>
                        @endif
                    </div>
                    <div class="col-12 col-lg-4 g-3 form-floating">
                        <input type="text" class="form-control" name="current_address_province" id="cur-add-prov" placeholder="Province" required value="{{ old('current_address_province', $employee ? $employee->current_address_province : '') }}">
                        <label for="cur-add-prov" class="text-center">Province/Region</label>
                        @if ($errors->has('current_address_province'))
                        <p class="text-danger">{{ $errors->first('current_address_province') }}</p>
                        @endif
                    </div>
                    <div class="col-12 col-lg-4 g-3 form-floating">
                        <input type="text" class="form-control" name="current_address_postalcode" id="cur-add-postal" placeholder="Postal Code" required value="{{ old('current_address_postalcode', $employee ? $employee->current_address_postalcode : '') }}">
                        <label for="cur-add-postal" class="text-center">Postal Code</label>
                        @if ($errors->has('current_address_postalcode'))
                        <p class="text-danger">{{ $errors->first('current_address_postalcode') }}</p>
                        @endif
                    </div>
                </div>
            </div>
            <div class="text-center p-2">
                <button class="btn btn-primary btn-lg mb-5 btn-prevent-multiple-submit" id="emp-reg-back" type="submit">
                    <span>Back</span>
                </button>
                <button class="btn btn-primary btn-lg mb-5 btn-prevent-multiple-submit" id="emp-reg-submit" type="submit">
                    <span>Register</span>
                    <div id="employee-reg-submit-spinner" class="spinner-border text-light spinner-border-sm" role="status" style="display: none">
                        <span class="sr-only"></span>
                    </div>
                </button>
            </div>
        </form>
    </div>
</div>




@include('footer')