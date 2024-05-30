@include('header')
<script src="{{ asset(mix('js/bug.min.js')) }}" defer></script>
<link rel="stylesheet" href="{{ asset(mix('css/bug.min.css')) }}">
@include('headerMenu')
@if (session('proj_alert')) 
    <div class="alert alert-success " role="alert">
       {{session()->pull('proj_alert')}}
    </div>
@endif
@if(!empty(session('regist_update_alert')))
<div class="alert alert-success" role="alert">
    {{ session()->pull('regist_update_alert') }}
</div>
<div class="container-md ps-md-3 pe-md-3 pt-2">
@else
<div class="container-md ps-md-3 pe-md-3 pt-5">
@endif
<form action="#" id="regist-request">
@csrf
</form>
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
                       <input type="text" name="name" class="form-control" id="name" placeholder="Project Name" value="{{ $projectData->name }}" disabled>
                       <label for="name" class="text-center">Project Name</label>
                    </div>
                </div>
                <div class="row mb-2 ps-5 pe-3">
                    <div class="col-md-3 col-6 g-3 form-floating">
                        <input type="date" name="start_date" class="form-control" id="start_date" placeholder="Start Date" value="{{ date('Y-m-d', strtotime($projectData->start_date)) }}" pattern="\d{4}-\d{2}-\d{2}" disabled>
                        <label for="start_date" class="text-center">Start Date</label>
                     </div>
                    <div class="col-md-3 col-6 g-3 form-floating">
                        <input type="date" name="end_date" class="form-control" id="end_date" placeholder="End Date" value="{{ !empty($projectData->end_date) ? date('Y-m-d', strtotime($projectData->end_date)) : "" }}" pattern="\d{4}-\d{2}-\d{2}" disabled>
                        <label for="end_date" class="text-center">End Date</label>
                     </div>
                </div>
                <div class="row pt-4 ps-3 pe-3">
                    <h5>Remarks</h5>
                </div>
                <div class="row mb-2 ps-5 pe-3">
                    <div class="col-md-6 g-3">
                        <textarea class="form-control" name="remarks"  rows="3" id="remarks" disabled>{{ $projectData->remarks }}</textarea>
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
            @if($showAddBtn)
            <button class="btn btn-primary" data-bs-target="#link_employee_modal" data-bs-toggle="modal">Add</button>
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
                                <div id="le_success_msg"></div>
                                <form action="#" id="link_employee_form">
                                    @csrf
                                    <input type="text" name="project_id" value="{{ $projectData->id }}" hidden>
                                    <input type="text" name="employee_role" value="{{ Auth::user()->roles }}" hidden>
                                    <div class="row mb-2">
                                        <div class="col-12 g-3 form-floating">
                                            @if (count($employeeDropdown) < 1)
                                                <select name="employee_id" class="form-select" id="member_list" required>
                                                    <option value="" disabled>No available employee</option>
                                            @else
                                                @if (in_array(Auth::user()->roles, [config('constants.ADMIN_ROLE_VALUE'), config('constants.MANAGER_ROLE_VALUE')]))
                                                    <select name="employee_id" class="form-select" id="member_list" required>
                                                        <option value=""></option>
                                                @else
                                                    <select name="employee_id" class="form-select" id="member_list" readonly>
                                                @endif

                                                @foreach ( $employeeDropdown as $employee )
                                                    <option value="{{ $employee['id'] }}">{{ $employee['employee_name'] }}</option>
                                                @endforeach
                                            @endif
                                            </select>
                                            <label for="member_list" class="text-center">Employee Name</label>
                                            <span id="link_employee_id_error"></span>
                                        </div>
                                    </div>
                                    <div class="row mb-2">
                                        <div class="col-6 g-3 form-floating">
                                            <input type="date" name="project_start" class="form-control" id="link_project_start" min="{{ date('Y-m-d', strtotime($projectData->start_date)) }}" max="{{ !empty($projectData->end_date) ? date('Y-m-d', strtotime($projectData->end_date)) : "" }}" required>
                                            <label for="link_project_start" class="text-center">Start Date</label>
                                            <span id="link_project_start_error"></span>
                                        </div>
                                        <div class="col-6 g-3 form-floating">
                                            <input type="date" name="project_end" class="form-control" id="link_project_end" min="{{ date('Y-m-d', strtotime($projectData->start_date)) }}" max="{{ !empty($projectData->end_date) ? date('Y-m-d', strtotime($projectData->end_date)) : "" }}">
                                            <label for="link_project_end" class="text-center">End Date</label>
                                            <span id="link_project_end_error"></span>
                                        </div>
                                    </div>
                                    <div class="row mb-2 ">
                                        <div class="col-6 g-3 form-floating">
                                            <select name="project_role" id="link_role" class="form-select">
                                                @foreach (config('constants.PROJECT_ROLES') as $val => $text )
                                                    <option value="{{ $val }}">{{ $text }}</option>
                                                @endforeach
                                            </select>
                                            <label for="link_role" class="form-label text-center">Role</label>
                                            <span id="link_project_role_error"></span>
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
                                <div id="link_create_spinner" class="spinner-border text-light spinner-border-sm" role="status" style="display: none">
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
            <table class="table table-bordered border-secondary mt-3 tbl-th-centered w-100" id="proj_members_tbl">
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
                                <td class="text-center">
                                    {{-- Check if update button should be displayed --}}
                                    @php
                                        $isMngr_or_Admin = (auth()->user()->roles == config('constants.ENGINEER_ROLE_VALUE') ? 0 : 1);
                                        $isEngr_active = (auth()->user()->id == $member['employee_id']) && ($member['isActive']);
                                        $noPendingRequests = (count($employeeLinkageRequests) == 0) ? 1 : 0; 
                                    @endphp
                                    @if (($isMngr_or_Admin and $member['haveNoRequest'] and $member['isActive']) or ($isEngr_active and $noPendingRequests))
                                        <button class="btn btn-link btn-sm text-success employee_linkage_update_btn" 
                                            data-bs-target="#update_employee_linkage_modal" 
                                            data-bs-toggle="modal" 
                                            data-modaldata='{
                                                "id":"{{ $member['id'] }}",
                                                "member":"{{ $member['member_name_update'] }}",
                                                "start_date":"{{ date('Y-m-d', strtotime($member['start_date'])) }}",
                                                "end_date":"{{ $member['end_date'] ? date('Y-m-d', strtotime($member['end_date'])) : "" }}",
                                                "onsite_flag":"{{ $member['onsite_flag'] }}",
                                                "project_role_type":"{{ $member['project_role_type'] }}",
                                                "remarks":"{{ $member['remarks'] }}"
                                            }'
                                        >Update
                                    </button>
                                    @endif
                                </td>
                                <td>{{ $member['isActive'] ? 1 : 0 }}</td>
                            </tr>
                        @endforeach
                    @endif
                </tbody>
            </table>
        </div>
    </div>


	<button id="btnTop" title="Go to top"><i class="bi bi-arrow-up"></i></button> 
</div>
@include('footer')