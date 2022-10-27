@php
    $userInfo = Auth::user();
@endphp

@include('header')
@include('headerMenu')

<div class="container text-center ps-md-3 pe-md-3 pt-5">
    @if ($allowedToEdit)
    <div class="d-flex justify-content-between mb-2">
        <div class="text-primary d-flex align-items-center">
            @if ($detailOnly && !empty($detailNote))
            <i class="bi bi-info-circle-fill"></i>&nbsp;{{ $detailNote }}
            @endif
        </div>
        @if($userInfo->id == $employee->id)
        <div class="">
            <a class="btn btn-primary  me-1" type="button">Edit</a>
            <button type="button" class="btn btn-success  ms-1" data-bs-toggle="modal" data-bs-target="#changePasswordModal" >Change Password</button>
        </div>
        @endif
    </div>
    @if($userInfo->id == $employee->id)
    <div class="modal fade" tabindex="-1" id="changePasswordModal">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title text-start">Change Account Password</h5>
                </div>
                <div class="modal-body">
                    <div class="p-2">
                        <div id="cp-success-msg">
                        </div>
                        <form action="#" id="changePasswordForm">
                            @csrf
                            <input type="text" hidden name="cp_id" value="{{ $employee->id }}">
                            <div class="row mb-3">
                                <div class="col-5 text-end">
                                    <label for="cp-current-pw" class="form-label">Enter Current Password</label>
                                </div>
                                <div class="col-7">
                                    <input name="cp_current_pw" type="password" class="form-control" id="cp-current-pw" required>
                                    <span id="current-pass-error"></span>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-5 text-end">
                                    <label for="cp-new-pw" class="form-label">Enter New Password</label>
                                </div>
                                <div class="col-7">
                                    <input name="cp_new_pw" type="password" class="form-control" id="cp-new-pw" required>
                                    <span id="new-pass-error"></span>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-5 text-end">
                                    <label for="cp-confirm-pw" class="form-label">Confirm New Password</label>
                                </div>
                                <div class="col-7">
                                    <input name="cp_confirm_pw" type="password" class="form-control" id="cp-confirm-pw" required>
                                    <span id="confirm-pass-text"></span>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button class="btn btn-primary" type="submit" id="cp-submit-btn">Submit</button>
                </div>
            </div>
        </div>
    </div>
    @endif
    @endif



    <div class="pt-4">
        <form action="{{ route('employees.regist') }}" method="POST">
            @csrf
            <div class="emp-regist-category p-3 mb-4 rounded-3">
                <h4 class="text-start">Employee </h4>
                <div class="row mb-2 ps-3 pe-3">
                    <div class="col-4 g-3 form-floating">
                        <input type="text" class="form-control" name="first_name" id="first_name" placeholder="First Name" value="{{ $employee->first_name }}" required @readonly($readOnly)>
                        <label class="text-center" for="first_name">First Name</label>
                        @if ($errors->has('first_name'))
                        <span class="text-danger">{{ $errors->first('first_name') }}</span>
                        @endif
                    </div>
                    <div class="col-4 g-3 form-floating">
                        <input type="text" class="form-control" name="middle_name" id="middle_name" placeholder="Middle Name" value="{{ $employee->middle_name }}" required @readonly($readOnly)>
                        <label  class="text-center" for="middle_name">Middle Name</label>
                        @if ($errors->has('first_name'))
                        <span class="text-danger">{{ $errors->first('middle_name') }}</span>
                        @endif
                    </div>
                    <div class="col-4 g-3 form-floating">
                        <input type="text" class="form-control" name="last_name" id="last_name" placeholder="Last Name" value="{{ $employee->last_name }}" required @readonly($readOnly)>
                        <label  class="text-center" for="last_name">Last Name</label>
                        @if ($errors->has('last_name'))
                        <span class="text-danger">{{ $errors->first('last_name') }}</span>
                        @endif
                    </div>
                </div>
                <div class="row mb-2 ps-3 pe-3">
                    <div class="col-4 g-3 form-floating">
                        <input type="date" class="form-control" name="birthdate" id="birthdate" placeholder="birthdate" value="{{ old('birthdate') ?: $employee->birthdate }}" pattern="\d{4}-\d{2}-\d{2}" required @readonly($readOnly)>
                        <label  class="text-center" for="birthdate">Birth Date</label>
                        @if ($errors->has('birthdate'))
                        <span class="text-danger">{{ $errors->first('birthdate') }}</span>
                        @endif
                    </div>
                    <div class="col-lg-4 col-8 g-3 text-start">
                        <div class="d-flex align-items-center ps-1" style="height: 100%">
                            <div class="d-inline">
                                Gender:&nbsp&nbsp
                            </div>
                            <div class="d-inline">
                                <div class="form-check form-check-inline">
                                    <input type="radio" class="form-check-input" name="gender" id="femaleRadio" value="0" {{ $employee->gender == 0 ? "checked" : "" }} {{ $readOnly ? 'disabled' : '' }}>
                                    <label class="form-check-label" for="femaleRadio">Female</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input type="radio" class="form-check-input" name="gender" id="maleRadio" value="1" {{ $employee->gender == 1 ? "checked" : "" }} {{ $readOnly ? 'disabled' : '' }}>
                                    <label class="form-check-label" for="maleRadio">Male</label>
                                </div>
                            </div>
                            @if ($errors->has('gender'))
                            <span class="text-danger">{{ $errors->first('gender') }}</span>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="row mb-2 ps-3 pe-3">
                    <div class="col-md-6 col-8 g-3 form-floating">
                        <select name="position" id="position" class="form-select form-control" {{ $readOnly ? 'disabled' : '' }}>
                            <option {{ $employee->position == 1 ? "selected" : "" }} value="1">{{ config('constants.POSITION_1_NAME') }}</option>
                            <option {{ $employee->position == 2 ? "selected" : "" }} value="2">{{ config('constants.POSITION_2_NAME') }}</option>
                            <option {{ $employee->position == 3 ? "selected" : "" }} value="3">{{ config('constants.POSITION_3_NAME') }}</option>
                            <option {{ $employee->position == 4 ? "selected" : "" }} value="4">{{ config('constants.POSITION_4_NAME') }}</option>
                            <option {{ $employee->position == 5 ? "selected" : "" }} value="5">{{ config('constants.POSITION_5_NAME') }}</option>
                            <option {{ $employee->position == 6 ? "selected" : "" }} value="6">{{ config('constants.POSITION_6_NAME') }}</option>
                            <option {{ $employee->position == 7 ? "selected" : "" }} value="7">{{ config('constants.POSITION_7_NAME') }}</option>
                            <option {{ $employee->position == 8 ? "selected" : "" }} value="8">{{ config('constants.POSITION_8_NAME') }}</option>
                            <option {{ $employee->position == 9 ? "selected" : "" }} value="9">{{ config('constants.POSITION_9_NAME') }}</option>
                        </select>
                        <label  class="text-center" for="position">Position</label>
                        @if ($errors->has('position'))
                        <span class="text-danger">{{ $errors->first('position') }}</span>
                        @endif
                    </div>
                    <div class="col-4 g-3 form-floating">
                        <select name="roles" id="roles" class="form-select form-control" {{ $readOnly ? 'disabled' : '' }}>
                            <option {{ $employee->roles == config('constants.ADMIN_ROLE_VALUE') ? "selected" : "" }} value="config('constants.ADMIN_ROLE_VALUE')">Admin</option>
                            <option {{ $employee->roles == config('constants.MANAGER_ROLE_VALUE') ? "selected" : "" }} value="config('constants.MANAGER_ROLE_VALUE')">Manager</option>
                            <option {{ $employee->roles == config('constants.ENGINEER_ROLE_VALUE') ? "selected" : "" }} value="config('constants.ENGINEER_ROLE_VALUE')">Engineer</option>
                        </select>
                        <label for="roles" class="text-center">Role</label>
                        @if ($errors->has('roles'))
                        <span class="text-danger">{{ $errors->first('roles') }}</span>
                        @endif
                    </div>
                </div>
                <div class="row mb-2 ps-3 pe-3">
                    <div class="col-lg-2 col-md-4 col-6 g-3 ps-1">
                        <div class="d-flex align-items-center">
                            <div class="form-check ">
                                <label class="form-check-label" for="active-status">Active Status</label>
                                <input type="checkBox" class="form-check-input" name="active_status" id="active-status" value="0" {{ $employee->active_status == 1 ? "checked" : "" }} {{ $readOnly ? 'disabled' : '' }}>
                            </div>
                            @if ($errors->has('active_status'))
                            <span class="text-danger">{{ $errors->first('active_status') }}</span>
                            @endif
                        </div>
                    </div>
                    <div class="col-lg-2 col-md-4 col-6 g-3 ps-1">
                        <div class="d-flex align-items-center">
                            <div class="form-check ">
                                <label class="form-check-label" for="server-manage-flag">Manage Server</label>
                                <input type="checkBox" class="form-check-input" name="server_manage_flag" id="server-manage-flag" value="0" {{ $employee->server_manage_flag == 1 ? "checked" : "" }} {{ $readOnly ? 'disabled' : '' }}>
                            </div>
                            @if ($errors->has('server_manage_flag'))
                            <span class="text-danger">{{ $errors->first('server_manage_flag') }}</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            <div class="emp-regist-category mb-4 p-3 rounded-3">
                <h4 class="text-start">Contact </h4>
                <div class="row mb-2 ps-3 pe-3">
                    <div class="col-4 g-3 form-floating">
                        <input type="text" class="form-control" name="email" id="email" placeholder="Email" required value="{{ $employee->email }}" readonly>
                        <label for="email" class="text-center">Email Address</label>
                        @if ($errors->has('email'))
                        <span class="text-danger">{{ $errors->first('email') }}</span>
                        @endif
                    </div>
                </div>
                <div class="row mb-2 ps-3 pe-3">
                    <div class="col-4 g-3">
                        <div class="input-group">
                            <span class="input-group-text">+63</span>
                            <div class="form-floating">
                                <input type="text" class="form-control" name="cellphone_number" id="contact" placeholder="Contact Number" required value="{{ $employee->cellphone_number }}" @readonly($readOnly)>
                                <label for="contact" class="text-center">Contact Number</label>
                            </div>
                            @if ($errors->has('cellphone_number'))
                            <span class="text-danger">{{ $errors->first('cellphone_number') }}</span>
                            @endif
                        </div>
                    </div>
                    <div class="col-4 g-3">
                        <div class="input-group">
                            <span class="input-group-text">+63</span>
                            <div class="form-floating">
                            <input type="text" class="form-control" name="other_contact_number" id="other_contact" placeholder="Other Contact Number" value="{{ $employee->other_contact_number }}" @readonly($readOnly)>
                                <label for="other_contact" class="text-center">Other Contact Number (optional)</label>
                            </div>
                            @if ($errors->has('other_contact_number'))
                            <span class="text-danger">{{ $errors->first('other_contact_number') }}</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            <div class="emp-regist-category mb-4 p-3 rounded-3">
                <h4 class="text-start">Current Address</h4>
                <div class="row mb-2 ps-3 pe-3">
                    <div class="col-12 g-3 form-floating">
                        <input type="text" class="form-control" name="current_address_street" id="cur-add-strt" placeholder="Street" required value="{{ $employee->current_address_street }}" @readonly($readOnly)>
                        <label for="cur-add-strt" class="text-center">Street</label>
                        @if ($errors->has('current_address_street'))
                        <span class="text-danger">{{ $errors->first('current_address_street') }}</span>
                        @endif
                    </div>
                </div>
                <div class="row mb-2 ps-3 pe-3">
                    <div class="col-4 g-3 form-floating">
                        <input type="text" class="form-control" name="current_address_city" id="cur-add-town" placeholder="Town" required value="{{ $employee->current_address_city }}" @readonly($readOnly)>
                        <label for="cur-add-town" class="text-center">Town/City</label>
                        @if ($errors->has('current_address_city'))
                        <span class="text-danger">{{ $errors->first('current_address_city') }}</span>
                        @endif
                    </div>
                    <div class="col-4 g-3 form-floating">
                        <input type="text" class="form-control" name="current_address_province" id="cur-add-prov" placeholder="Province" required value="{{ $employee->current_address_province }}" @readonly($readOnly)>
                        <label for="cur-add-prov" class="text-center">Province/Region</label>
                        @if ($errors->has('current_address_province'))
                        <span class="text-danger">{{ $errors->first('current_address_province') }}</span>
                        @endif
                    </div>
                    <div class="col-4 g-3 form-floating">
                        <input type="text" class="form-control" name="current_address_postalcode" id="cur-add-postal" placeholder="Postal Code" required value="{{ $employee->current_address_postalcode }}" @readonly($readOnly)>
                        <label for="cur-add-postal" class="text-center">Postal Code</label>
                        @if ($errors->has('current_address_postalcode'))
                        <span class="text-danger">{{ $errors->first('current_address_postalcode') }}</span>
                        @endif
                    </div>
                </div>
            </div>
            <div class="emp-regist-category mb-4 p-3 rounded-3">
                <h4 class="text-start">Permanent Address</h4>
                <div class="row mb-2 ps-3 pe-3">
                    <div class="col-12 g-3 form-floating">
                        <input type="text" class="form-control" name="permanent_address_street" id="perm-add-strt" placeholder="Street" required value="{{ $employee->permanent_address_street }}" @readonly($readOnly)>
                        <label for="perm-add-strt" class="text-center">Street</label>
                        @if ($errors->has('permanent_address_street'))
                        <span class="text-danger">{{ $errors->first('permanent_address_street') }}</span>
                        @endif
                    </div>
                </div>
                <div class="row mb-2 ps-3 pe-3">
                    <div class="col-4 g-3 form-floating">
                        <input type="text" class="form-control" name="permanent_address_city" id="perm-add-town" placeholder="Town" required value="{{ $employee->permanent_address_city }}" @readonly($readOnly)>
                        <label for="perm-add-town" class="text-center">Town/City</label>
                        @if ($errors->has('permanent_address_city'))
                        <span class="text-danger">{{ $errors->first('permanent_address_city') }}</span>
                        @endif
                    </div>
                    <div class="col-4 g-3 form-floating">
                        <input type="text" class="form-control" name="permanent_address_province" id="perm-add-prov" placeholder="Province" required value="{{ $employee->permanent_address_province }}" @readonly($readOnly)>
                        <label for="perm-add-prov" class="text-center">Province/Region</label>
                        @if ($errors->has('permanent_address_province'))
                        <span class="text-danger">{{ $errors->first('permanent_address_province') }}</span>
                        @endif
                    </div>
                    <div class="col-4 g-3 form-floating">
                        <input type="text" class="form-control" name="permanent_address_postalcode" id="perm-add-postal" placeholder="Postal Code" required value="{{ $employee->permanent_address_postalcode }}" @readonly($readOnly)>
                        <label for="perm-add-postal" class="text-center">Postal Code</label>
                        @if ($errors->has('permanent_address_postalcode'))
                        <span class="text-danger">{{ $errors->first('permanent_address_postalcode') }}</span>
                        @endif
                    </div>
                </div>
            </div>
            @if (!$detailOnly)
                <div class="text-center p-4">
                    <button class="btn btn-success btn-lg mb-5 me-4 rqst-btn" type="">Approve</button>
                    <button class="btn btn-danger btn-lg mb-5 ms-4 rqst-btn"  type="">Reject</button>
                </div>
            @endif
        </form>
    </div>
    <div class="emp-regist-category mb-4 p-3 rounded-3">
        <div class="d-flex justify-content-between">
            <h4 class="text-start">Projects</h4>
            <button class="btn btn-primary" data-bs-target="#linkProjectModal" data-bs-toggle="modal">Add</button>
        </div>
        <table class="table table-bordered border-secondary mt-3" id="project-tbl">
            <thead class="bg-primary text-white fw-bold">
                <tr>
                    <th style="width:50%">NAME</th>
                    <th style="width:30%">DATE</th>
                    <th style="">STATUS</th>
                </tr>
            </thead>
            <tbody class="">
                @foreach ($empProject as $project)
                    <tr>
                        <td><a href="{{ route('project.details', ['id' => $project['project_id']]) }}" class="text-decoration-none">{{ $project['name'] }}</a></td>
                        <td>{{ date("Y/m/d", strtotime($project['start_date']))  }} - {{ $project['end_date'] ? date("Y/m/d", strtotime($project['end_date'])) : '' }}</td>
                        <td>{{ $project['project_status'] }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="emp-regist-category mb-4 p-3 rounded-3">
        <div class="d-flex justify-content-between">
            <h4 class="text-start">Laptops</h4>
            <button class="btn btn-primary" data-bs-target="#linkLaptopModal" data-bs-toggle="modal">Add</button>
        </div>
        <table class="table table-bordered border-secondary mt-3" id="laptop-tbl">
            <thead class="bg-primary text-white fw-bold">
                <tr>
                    <th>TAG NUMBER</th>
                    <th>OFFICE PC BROUGHT HOME</th>
                    <th>LAPTOP MAKE</th>
                    <th>LAPTOP MODEL</th>
                    <th>VPN ACCESS</th>
                </tr>
            </thead>
            <tbody class="">
                @foreach ($empLaptop as $laptop)
                <tr>
                    <td><a href="{{ route('laptop.details', ['id' => $laptop['id']]) }}" class="text-decoration-none">{{ $laptop['tag_number'] }}</a></td>
                    <td>{{ $laptop['brought_home'] }}</td>
                    <td>{{ $laptop['laptop_make'] }}</td>
                    <td>{{ $laptop['laptop_model'] }}</td>
                    <td>{{ $laptop['use_vpn'] }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="modal fade" tabindex="-1" id="linkProjectModal">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title text-start">Link Project</h5>
                </div>
                <div class="modal-body">
                    <div class="p-2">
                        <form action="#" id="linkProjectForm">
                            @csrf
                            <input type="text" hidden name="lp_employee_id" value="{{ $employee->id }}">
                            <div class="row mb-2">
                                <div class="col-12 g-3 form-floating">
                                    <select name="project_id" class="form-select" id="projectList" required>
                                        @foreach ( $projectList as $project )
                                            <option value="{{ $project['id'] }}">{{ $project['name'] }}</option>
                                        @endforeach
                                    </select>
                                    <label for="projectList" class="text-center">Project Name</label>
                                    <span id="error-lp-proj-name"></span>
                                </div>
                            </div>
                            <div class="row mb-2">
                                <div class="col-6 g-3 form-floating">
                                    <input type="date" name="project_start_date" class="form-control" id="project-start" required>
                                    <label for="project-start" class="text-center">Start Date</label>
                                    <span id="error-lp-proj-start"></span>
                                </div>
                                <div class="col-6 g-3 form-floating">
                                    <input type="date" name="project_end_date" class="form-control" id="project-end" required>
                                    <label for="project-end" class="text-center">Start Date</label>
                                    <span id="error-lp-proj-end"></span>
                                </div>
                            </div>
                            <div class="row mb-2">
                                <div class="col-12 g-3 form-floating">
                                    <select name="project_role" id="projectRoleList" class="form-select" required>
                                        <option value="{{ config('constants.PROJECT_ROLE_TEAM_LEAD') }}">Team Lead</option>
                                        <option value="{{ config('constants.PROJECT_ROLE_PROGRAMMER') }}">Programmer</option>
                                        <option value="{{ config('constants.PROJECT_ROLE_QA') }}">QA</option>
                                    </select>
                                    <label for="projectRoleList" class="text-center">Role</label>
                                    <span id="error-lp-proj-role"></span>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button class="btn btn-primary" type="submit" id="lp-submit-btn">Submit</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" tabindex="-1" id="linkLaptopModal">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title text-start">Link Laptop</h5>
                </div>
                <div class="modal-body">
                    <div class="p-2">
                        <form action="#" id="linkLaptopForm">
                            @csrf
                            <input type="text" hidden name="ll_employee_id" value="{{ $employee->id }}">
                            <div class="row mb-2">
                                <div class="col-6 g-3 form-floating">
                                    <select name="laptop_id" id="laptopList" class="form-select" required>
                                        @foreach ($laptopList as $laptop)
                                            <option value="{{ $laptop['id'] }}">{{ $laptop['tag_number'] }}</option>
                                        @endforeach
                                    </select>
                                    <label for="laptopList" class="text-center">Tag Number</label>
                                </div>
                            </div>
                            <div class="row mb-2 text-start">
                                <div class="col-6 g-3">
                                    <div class="form-check">
                                        <label for="ll-brought-home" class="form-check-label">Brought Home?</label>
                                        <input type="checkbox" class="form-check-input" name="laptop_brought_home" id="ll-brought-home">
                                    </div>  
                                </div>
                                <div class="col-6 g-3">
                                    <div class="form-check">
                                        <label for="ll-vpn" class="form-check-label">VPN Access?</label>
                                        <input type="checkbox" class="form-check-input" name="laptop_vpn" id="ll-vpn">
                                    </div>
                                </div>
                            </div>
                            <div class="row mb-2">
                                <div class="col-6 g-3 text-start">
                                    <div class="form-check">
                                        <label for="ll-surrender" class="form-check-label">Surrender?</label>
                                        <input type="checkbox" class="form-check-input" name="laptop_surrender" id="ll-surrender">
                                    </div>
                                </div>
                                <div class="col-6 g-3 form-floating">
                                    <input type="date" class="form-control" id="ll-surrender-date">
                                    <label for="ll-surrender-date" class="text-center">Surrender Date</label>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button class="btn btn-primary" type="submit" id="ll-submit-btn">Submit</button>
                </div>
            </div>
        </div>
    </div>

</div>

@include('footer')