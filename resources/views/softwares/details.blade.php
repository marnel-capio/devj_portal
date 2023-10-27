@php
    $userInfo = Auth::user();
@endphp

@include('header')
<link rel="stylesheet" href="{{ asset(mix('css/software.min.css')) }}">
<script src="{{ asset(mix('js/software.min.js')) }}" defer></script>
@include('headerMenu')

@if (session('success')) 
	<div class="alert alert-success" role="alert">
        <span class="ms-2">{{ session('message') }}</span>
	</div>
@endif
<form action="#" id="regist-request">
@csrf
</form>
<div class="container text-center ps-md-3 pe-md-3 pt-5">
    <div class="d-flex justify-content-between mb-2">
        <div class="text-primary d-flex align-items-center">
            @if (!empty($detailNote))
            <i class="bi bi-info-circle-fill"></i>&nbsp;{{ $detailNote }}
            @endif
        </div>
        
        <div class="">
            @if ($allowedToEdit)
            <a href="{{ route('softwares.edit', ['id' => $software->id]) }}" class="btn btn-primary  me-1" type="button">Edit</a>
            @endif
            @if ($userInfo->roles == config('constants.MANAGER_ROLE_VALUE') && $software->approved_status != config('constants.APPROVED_STATUS_REJECTED'))
                <a href="{{ route('softwares.delete', ['id' => $software->id]) }}" class="btn btn-danger  me-1" id="software-delete" type="button">Delete</a>
                <input type="text" name="software_id" id="software_id" hidden value="{{ $software->id }}">
            @endif
            @if($userInfo->id == $software->updated_by && $software->approved_status == config("constants.APPROVED_STATUS_PENDING"))
            <div>
                <a id="cancel-register" class="btn btn-primary" type="button" style="float: right;">Cancel Register
                    <div id="react-cancel-spinner" class="spinner-border text-light spinner-border-sm" role="status" style="display: none">
                        <span class="sr-only"></span>
                    </div>
                </a>
                <input hidden name="id" id="softwareId" value="{{ $software->id }}" type="text">
            </div>
            @endif
        </div>
        
    </div>

    <div class="pt-4">
        <form>
            @csrf
            <div class="soft-regist-category p-3 mb-4 rounded-3">
                <h4 class="text-start">Software Details</h4>
                <div class="row mb-2 ps-3 pe-3">
                    <div class="col-4 g-3 form-floating">
                        <input type="text" class="form-control" name="approved_status" id="approved_status" placeholder="Status" value="{{ $current_status }}" required disabled>
                        <label class="text-center" for="approved_status">Status</label>
                        @if ($errors->has('approved_status'))
                        <p class="text-danger">{{ $errors->first('approved_status') }}</p>
                        @endif
                    </div>
                </div>
                <div class="row mb-2 ps-3 pe-3">
                    <div class="col-4 g-3 form-floating">
                        <input type="text" class="form-control" name="software_name" id="software_name" placeholder="Software Name" value="{{ $software->software_name }}" required disabled>
                        <label class="text-center" for="software_name">Software Name</label>
                        @if (isset($originalData) && ($originalData->software_name != $software->software_name))
                            <div class="text-secondary px-3 py-1 fs-6 fst-italic text-start">old value: {{ !empty($originalData->software_name) ? $originalData->software_name : "''" }}</div>
                        @endif
                        @if ($errors->has('software_name'))
                        <p class="text-danger">{{ $errors->first('software_name') }}</p>
                        @endif
                    </div>
                </div>
                <div class="row mb-2 ps-3 pe-3">
                    <div class="col-4 g-3 form-floating">
                        <select name="type" id="type" class="form-select form-control" {{ $readOnly ? 'disabled' : '' }}>
                            @if($is_display_new_software_type)
                                <option selected value="{{ config('constants.SOFTWARE_TYPE_999')}}">{{ config('constants.SOFTWARE_TYPE_999_NAME')}}</option>
                            @else
                                <option selected value="{{ $software->software_type_id}}">{{ $software->type}}</option>
                            @endif
                        </select>
                        <label  class="text-center" for="type">Software Type</label>
                        @if (isset($originalData) && ($originalData->type != $software->type))
                            <div class="text-secondary px-3 py-1 fs-6 fst-italic text-start">old value: {{ !empty($originalData->type) ? $originalData->type : "''" }}</div>
                        @endif
                        @if ($errors->has('type'))
                        <p class="text-danger">{{ $errors->first('type') }}</p>
                        @endif
                    </div>
                    @if($is_display_new_software_type)
                    <div class="col-4 g-3 form-floating">
                        <input type="text" class="form-control" name="new_software_type" id="new_software_type" placeholder="New Software Type" value="{{ $software->type }}" required disabled>
                        <label class="text-center" for="new_software_type">New Software Type</label>
                        @if ($errors->has('new_software_type'))
                        <p class="text-danger">{{ $errors->first('new_software_type') }}</p>
                        @endif
                    </div>
                    @endif
                </div>
                <div class="row mb-2 ps-3 pe-3">
                    <div class="col-4 g-3 form-floating">
                        <textarea class="form-control" name="remarks" id="remarks" placeholder="Purpose" required disabled> {{ $software->remarks }} </textarea>
                        <label class="text-center" for="remarks">Purpose</label>
                        @if (isset($originalData) && ($originalData->remarks != $software->remarks))
                            <div class="text-secondary px-3 py-1 fs-6 fst-italic text-start">old value: {{ !empty($originalData->remarks) ? $originalData->remarks : "''" }}</div>
                        @endif
                        @if ($errors->has('remarks'))
                        <p class="text-danger">{{ $errors->first('remarks') }}</p>
                        @endif
                    </div>
                </div>
                @if($software->approved_status == config('constants.APPROVED_STATUS_REJECTED'))
                    <div class="row mb-2 ps-3 pe-3">
                        <div class="col-4 g-3 form-floating">
                            <input type="text" class="form-control" name="reasons" id="reasons" placeholder="Reasons" value="{{ $software->reasons }}" required disabled>
                            <label class="text-center" for="reasons">Reasons</label>
                            @if ($errors->has('reasons'))
                            <p class="text-danger">{{ $errors->first('reasons') }}</p>
                            @endif
                        </div>
                    </div>
                @endif
                <div class="row mb-2 ps-3 pe-3">
                    <div class="col-4 g-3 form-floating">
                        <input type="text" class="form-control" name="created_by" id="created_by" placeholder="Created By" value="{{ $software->creator }}" required disabled>
                        <label class="text-center" for="created_by">Created By</label>
                        @if ($errors->has('created_by'))
                        <p class="text-danger">{{ $errors->first('created_by') }}</p>
                        @endif
                    </div>
                    <div class="col-4 g-3 form-floating">
                        <input type="text" class="form-control" name="created_date" id="created_date" placeholder="Create Date" value="{{ $software->create_time }}" disabled>
                        <label class="text-center" for="created_date">Create Date</label>
                        @if ($errors->has('created_date'))
                        <p class="text-danger">{{ $errors->first('created_date') }}</p>
                        @endif
                    </div>
                </div>
                <div class="row mb-2 ps-3 pe-3">
                    <div class="col-4 g-3 form-floating">
                        <input type="text" class="form-control" name="updated_by" id="updated_by" placeholder="Updated By" value="{{ $software->updater }}" required disabled>
                        <label class="text-center" for="updated_by">Updated By</label>
                        @if ($errors->has('updated_by'))
                        <p class="text-danger">{{ $errors->first('updated_by') }}</p>
                        @endif
                    </div>
                    <div class="col-4 g-3 form-floating">
                        <input type="text" class="form-control" name="updated_date" id="updated_date" placeholder="Update Date" value="{{ $software->update_time }}" disabled>
                        <label class="text-center" for="updated_date">Update Date</label>
                        @if ($errors->has('updated_date'))
                        <p class="text-danger">{{ $errors->first('updated_date') }}</p>
                        @endif
                    </div>
                </div>                
                @if( $is_display_approver)
                    <div class="row mb-2 ps-3 pe-3">
                        <div class="col-4 g-3 form-floating">
                            <input type="text" class="form-control" name="approved_by" id="approved_by" placeholder="Approved By" value="{{ $software->approver }}" required disabled>
                            <label class="text-center" for="approved_by">Approved By</label>
                            @if ($errors->has('approved_by'))
                            <p class="text-danger">{{ $errors->first('approved_by') }}</p>
                            @endif
                        </div>
                        <div class="col-4 g-3 form-floating">
                            <input type="text" class="form-control" name="approved_date" id="approved_date" placeholder="Approve Date" value="{{ $software->approve_time }}" disabled>
                            <label class="text-center" for="approved_date">Approve Date</label>
                            @if ($errors->has('approved_date'))
                            <p class="text-danger">{{ $errors->first('approved_date') }}</p>
                            @endif
                        </div>
                    </div>
                @endif
            </div>
        </form>
    </div>
   @if ($detailOnly && $is_project_display)
    <div class="soft-regist-category mb-4 p-3 rounded-3 table-avoid-overflow">
        <div class="d-flex justify-content-between">
            <h4 class="text-start">Projects</h4>
            <button class="btn btn-primary" data-bs-target="#linkProjectModal" data-bs-toggle="modal">Add</button>
        </div>
        @if(!empty(session('sp_alert')))
            <div class="alert alert-success mt-2" role="alert">
                    {{session()->pull('sp_alert')}}
            </div>
        @endif
        <table class="table table-bordered border-secondary mt-3" id="project-tbl">
            <thead class="bg-primary text-white fw-bold">
                <tr>
                    <th style="width:50%">NAME</th> 
                    <th style="width:30%">DATE</th>
                    <th style="">STATUS</th>
                </tr>
            </thead>
            <tbody class="">
                @if(!empty($softProject))
                    @foreach ($softProject as $project)
                        <tr>
                            <td><a href="{{ route('projects.details', ['id' => $project['project_id']]) }}" class="text-decoration-none">{{ $project['name'] }}</a></td>
                            <td>{{ date("Y/m/d", strtotime($project['start_date']))  }} - {{ $project['end_date'] ? date("Y/m/d", strtotime($project['end_date'])) : '' }}</td>
                            <td>{{ $project['project_status'] }}</td>
                        </tr>
                    @endforeach
                @endif
            </tbody>
        </table>
    </div>
    @endif

    @if (!$detailOnly)
    <div class="text-center p-4">
        <button class="btn btn-danger btn-lg mb-5 me-4 rqst-btn"  data-bs-target="#softwarerejectRequestModal" data-bs-toggle="modal" id="soft-reject-request">
            Reject <div id="soft-reject-request-spinner" class="spinner-border text-light spinner-border-sm" role="status" style="display: none">
                <span class="sr-only"></span>
            </div>
        </button>
        <button class="btn btn-success btn-lg mb-5 ms-4 rqst-btn" id="soft-approve-request"  form="soft-approve-request-form">
            Approve <div id="soft-approve-request-spinner" class="spinner-border text-light spinner-border-sm" role="status" style="display: none">
                <span class="sr-only"></span>
            </div>
        </button>
        <form action="{{ route('softwares.store') }}" method="POST" id="soft-approve-request-form">
            @csrf
            <input type="text" name="id" hidden value="{{ $software->id }}">
        </form>
    </div>
    <div class="modal fade" tabindex="-1" id="softwarerejectRequestModal">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-body">
                    <div class="p-2">
                        <form action="{{ route('softwares.reject') }}" method="POST" id="soft-reject-request-form">
                            @csrf
                            <input type="text" name="id" value="{{ $software->id }}" hidden>
                            <div class="mb-2">
                                <textarea class="form-control" name="reason" placeholder="Rejection Reason" rows="5" id="soft-reject-reason" required></textarea>
                            </div>
                            <p id="soft-reject-reason-error"></p>
                        </form>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button class="btn btn-danger" id="soft-reject-sub" type="submit" form="soft-reject-request-form">
                        Reject <div id="soft-reject-sub-spinner" class="spinner-border text-light spinner-border-sm" role="status" style="display: none">
                            <span class="sr-only"></span>
                        </div>
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif

    @if ($detailOnly && $is_project_display)
    <div class="modal fade" tabindex="-1" id="linkProjectModal">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title text-start">Link Project</h5>
                </div>
                <div class="modal-body">
                    <div class="p-2">
                        <div id="lp-success-msg"></div>
                        <form action="#" id="linkProjectForm">
                            @csrf
                            <input type="text" hidden name="lp_software_id" value="{{ $software->id }}">
                            <div class="row mb-2 ps-3 pe-3">
                                <div class="col-12 g-3 form-floating">
                                    @if (count($projectList) < 1)
                                        <select name="project_id" class="form-select" id="projectList" required>
                                            <option value="" disabled>No available software</option>
                                    @else
                                        <select name="project_id" class="form-select" id="projectList" required>
                                            @foreach ( $projectList as $project )
                                                <option value="{{ $project['id'] }}">{{ $project['name'] }}</option>
                                            @endforeach
                                    @endif
                                    </select>
                                    <label for="projectList" class="text-center">Project Name</label>
                                    <p id="error-lp-proj-name"></p>
                                </div>
                            </div>
                            <div class="row pt-2">
                                <h6 class="text-start">Remarks</h6>
                            </div>
                            <div class="row text-start">
                                <div class="gs-3 ge-3 gt-1">
                                    <textarea class="form-control" rows="3" name="project_remarks" rows="5" id="project_remarks" required></textarea>
                                    <span id="error-lp-proj-reason"></span>
                                </div>
                            </div>                            
                        </form>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button class="btn btn-primary" type="submit" id="lp-submit-btn">
                        Link  <div id="lp-submit-btn-spinner" class="spinner-border text-light spinner-border-sm" role="status" style="display: none">
                            <span class="sr-only"></span>
                        </div>
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif


</div>

@include('footer')