@include('header')
<script src="{{ asset('js/project_dum.js') }}"></script>
<link rel="stylesheet" href="{{ asset('css/project_dum.css') }}">
@include('headerMenu')
@if(!empty(session('regist_update_alert')))
<div class="alert alert-success" role="alert">
    {{ session()->pull('regist_update_alert') }}
</div>
<div class="container-md ps-md-3 pe-md-3 pt-2">
@else
<div class="container-md ps-md-3 pe-md-3 pt-5">
@endif
    <div class="d-flex justify-content-between mb-2">
        <div class="text-primary d-flex align-items-center">
            @if (!empty($detailNote))
            <i class="bi bi-info-circle-fill"></i>&nbsp;{{ $detailNote }}
            @endif
        </div>
        <div class="">
            @if ($isManager)
            <a href="{{ route('projects.edit', ['id' => $projectData->id]) }}" class="btn btn-primary  me-1" type="button">Edit</a>
            @endif
        </div>
    </div>
    <div class="pt-4">
        <form action="{{ route('projects.regist') }}" method="POST">
            @csrf
            <div class="group-category p-3 mb-4 rounded-3">
                <h4 class="text-start">Project Details</h4>
                <div class="row mb-2 ps-5 pe-3">
                    <div class="col-md-6 g-3 form-floating">
                       <input type="text" name="name" class="form-control" id="name" placeholder="Project Name" value="{{ $projectData->name }}">
                       <label for="name" class="text-center">Project Name</label>
                    </div>
                </div>
                <div class="row mb-2 ps-5 pe-3">
                    <div class="col-md-3 col-6 g-3 form-floating">
                        <input type="date" name="start_date" class="form-control" id="start_date" placeholder="Start Date" value="{{ date('Y-m-d', strtotime($projectData->start_date)) }}" pattern="\d{4}-\d{2}-\d{2}">
                        <label for="start_date" class="text-center">Start Date</label>
                     </div>
                    <div class="col-md-3 col-6 g-3 form-floating">
                        <input type="date" name="end_date" class="form-control" id="end_date" placeholder="End Date" value="{{ !empty($projectData->end_date) ? date('Y-m-d', strtotime($projectData->end_date)) : "" }}" pattern="\d{4}-\d{2}-\d{2}">
                        <label for="end_date" class="text-center">End Date</label>
                     </div>
                </div>
                <div class="row pt-4 ps-3 pe-3">
                    <h5>Remarks</h5>
                </div>
                <div class="row mb-2 ps-5 pe-3">
                    <div class="col-md-6 g-3">
                        <textarea class="form-control" name="remarks"  rows="3" id="remarks">{{ $projectData->remarks }}"</textarea>
                    </div>
                </div>
            </div>
        </form>
    </div>
    <div class="group-category mb-4 p-3 rounded-3">
        <div class="d-flex justify-content-between">
            <div>
                <h4 class="text-start d-inline-block">Project Members</h4>
                &nbsp;&nbsp;
                <div class="form-check d-inline-block">
                    <input type="checkbox" id="show_hist" class="form-check-input">
                    <label for="show_hist" class="form-check-label user-select-none">Show previous members</label>
                </div>
            </div>
            @if ($showAddBtn)
            <button class="btn btn-primary" data-bs-target="#link_employee_modal" data-bs-toggle="modal">Link</button>
            <div class="modal modal fade" tabindex='-1' id="link_employee_modal">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">
                                Link Employee
                            </h5>
                        </div>
                        <div class="modal-body">
                            <div class="p-2">
                                <form action="#" id="link_employee_form">
                                    @csrf
                                    <div class="row mb-2">
                                        <div class="col-12 g-3 form-floating">
                                        @if (in_array(Auth::user()->roles, [config('constants.ADMIN_ROLE_VALUE'), config('constants.MANAGER_ROLE_VALUE')]))
                                            <select name="name" class="form-select" id="member_list" required>
                                                <option value=""></option>
                                        @else
                                            <select name="name" class="form-select" id="member_list" readonly>
                                        @endif
                                                @foreach ( $employeeDropdown as $employee )
                                                    <option value="{{ $employee['id'] }}">{{ $employee['employee_name'] }}</option>
                                                @endforeach
                                            </select>
                                            <label for="member_list" class="text-center">Employee Name</label>
                                            <p id="link_name_error"></p>
                                        </div>
                                    </div>
                                    <div class="row mb-2">
                                        <div class="col-6 g-3 form-floating">
                                            <input type="date" name="start_date" class="form-control" id="link_project_start" min="{{ date('Y-m-d', strtotime($projectData->start_date)) }}" max="{{ !empty($projectData->end_date) ? date('Y-m-d', strtotime($projectData->end_date)) : "" }}" required>
                                            <label for="link_project_start" class="text-center">Start Date</label>
                                            <p id="link_start_date_error"></p>
                                        </div>
                                        <div class="col-6 g-3 form-floating">
                                            <input type="date" name="end_date" class="form-control" id="link_project_end" min="{{ date('Y-m-d', strtotime($projectData->start_date)) }}" max="{{ !empty($projectData->end_date) ? date('Y-m-d', strtotime($projectData->end_date)) : "" }}" required>
                                            <label for="link_project_end" class="text-center">End Date</label>
                                            <p id="link_end_date_error"></p>
                                        </div>
                                    </div>
                                    <div class="row mb-2 ">
                                        <div class="col-6 g-3 form-floating">
                                            <select name="role" id="link_role" class="form-select">
                                                @foreach (config('constants.PROJECT_ROLES') as $val => $text )
                                                    <option value="{{ $val }}">{{ $text }}</option>
                                                @endforeach
                                            </select>
                                            <label for="link_role" class="form-label text-center">Role</label>
                                            <p id="link_role_error"></p>
                                        </div>
                                        <div class="col-6 g-3">
                                            <p></p>
                                            <div class="form-check ">
                                                <label for="link_onsite" class="form-check-label user-select-none">Onsite</label>
                                                <input type="checkbox" class="form-check-input" name="onsite" id="link_onsite" value="1">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row pt-2">
                                        <h6>Remarks</h6>
                                    </div>
                                    <div class="row text-start">
                                        <div class="gs-3 ge-3 gt-1">
                                            <textarea name="remarks" id="link_remarks" rows="3" class="form-control"></textarea>
                                            <span id="link_remarks_error"></span>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            <button class="btn btn-primary" type="submit"  id="pj_submit_btn" form="link_employee_form">Link
                                <div id="link_update_spinner" class="spinner-border text-light spinner-border-sm" role="status" style="display: none">
                                    <span class="sr-only"></span>
                                </div>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            @endif
        </div>

        <div class="ms-3">
            @if(!empty(session('pj_alert')))
                <div class="alert alert-success mt-2" role="alert">
                    {{session()->pull('pj_alert')}}
                </div>
            @endif
            <table class="table table-bordered border-secondary mt-3 tbl-th-centered" id="proj_members_tbl">
                <thead class="bg-primary text-white fw-bold">
                    <tr>
                        <th>Member</th>
                        <th>Role</th>
                        <th>Onsite</th>
                        <th>Date</th>
                        <th>Action</th>
                        <th>isActive</th>
                    </tr>
                </thead>
                <tbody>
                    @if ($projectMembers)
                        @foreach ($projectMembers as $member)
                            <tr>
                                <td><a href="{{ route('employees.details', ['id' => $member['employee_id']]) }}">{{ $member['member_name'] }}</a></td>
                                <td>{{ config('constants.PROJECT_ROLES.' .$member['project_role_type']) }}</td>
                                <td>{{ $member['onsite_flag'] ? 'Yes' : 'No' }}</td>
                                <td>{{ $member['membership_date'] }}</td>
                                <td></td>
                                <td>{{ $member['isActive'] ? 1 : 0 }}</td>
                            </tr>
                        @endforeach
                    @endif

                </tbody>
            </table>
        </div>
    </div>

    @if (auth()->user()->roles == config('constants.MANAGER_ROLE_VALUE'))
    <div class="group-category mb-4 p-3 rounded-3">
        <h4>Employee Linkage Requests</h4>
        <div class="ms-3">
            @if(!empty(session('elr_alert')))
                <div class="alert alert-success mt-2" role="alert">
                    {{session()->pull('elr_alert')}}
                </div>
            @endif
            <table class="table table-bordered border-secondary mt-3 tbl-th-centered" id="link_request_tbl">
                <thead class="bg-primary text-white fw-bold">
                    <tr>
                        <th>Member</th>
                        <th>Role</th>
                        <th>Onsite</th>
                        <th>Date</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @if ($employeeLinkageRequests)
                        @foreach ($employeeLinkageRequests as $member)
                            <tr>
                                {{-- update para sa linkage update --}}
                                <td><a href="{{ route('employees.details', ['id' => $member['employee_id']]) }}">{{ $member['table_name'] }}</a></td>
                                <td>{{ config('constants.PROJECT_ROLES.' .$member['project_role_type']) }}</td>
                                <td>{{ $member['onsite_flag'] ? 'Yes' : 'No' }}</td>
                                <td>{{ $member['membership_date'] }}</td>
                                <td></td>
                            </tr>
                        @endforeach
                    @endif
                </tbody>
            </table>
        </div>
    </div>
    @endif

    <div class="group-category mb-4 p-3 rounded-3">
        <h4>Linked Softwares</h4>
        <div class="ms-3">
            @if(!empty(session('elr_alert')))
                <div class="alert alert-success mt-2" role="alert">
                    {{session()->pull('elr_alert')}}
                </div>
            @endif
            <table class="table table-bordered border-secondary mt-3 tbl-th-centered" id="linked_softwares_tbl">
                <thead class="bg-primary text-white fw-bold">
                    <tr>
                        <th>Name</th>
                        <th>Type</th>
                        <th>Remarks</th>
                        @if (auth()->user()->roles == config('constants.MANAGER_ROLE_VALUE'))
                        <th id="ls_remove_btn">Action</th>
                        @endif
                    </tr>
                </thead>
                <tbody class="align-middle">
                    @if ($linkedSoftwares)
                        @foreach ($linkedSoftwares as $software)
                            <tr>
                                <td><a href="{{ route('softwares.details', ['id' => $software['id']]) }}">{{ $software['software_name'] }}</a></td>
                                <td>{{ $software['software_type'] }}</td>
                                <td>{{ $software['remarks'] }}</td>
                                @if (auth()->user()->roles == config('constants.MANAGER_ROLE_VALUE'))
                                <td class="text-center"><button class="btn btn-link btn-sm text-danger">Remove</button></td>
                                @endif
                            </tr>
                        @endforeach
                    @endif
                </tbody>
            </table>
        </div>
    </div>

</div>


@include('footer')