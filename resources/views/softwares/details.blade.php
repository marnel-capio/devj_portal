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
            @if($detailOnly && $userInfo->id == $software->id)
            <button type="button" class="btn btn-success  ms-1" data-bs-toggle="modal" data-bs-target="#changePasswordModal" >Change Password</button>
            @endif
        </div>
        
    </div>

    <div class="pt-4">
        <form action="{{ route('softwares.create') }}" method="POST">
            @csrf
            <div class="soft-regist-category p-3 mb-4 rounded-3">
                <h4 class="text-start">Software </h4>
                <div class="row mb-2 ps-3 pe-3">
                    <div class="col-4 g-3 form-floating">
                        <input type="text" class="form-control" name="software_status" id="software_status" placeholder="Status" value="{{ $current_status }}" required @readonly($readOnly)>
                        <label class="text-center" for="software_status">Status</label>
                        @if ($errors->has('software_status'))
                        <p class="text-danger">{{ $errors->first('software_status') }}</p>
                        @endif
                    </div>
                </div>
                <div class="row mb-2 ps-3 pe-3">
                    <div class="col-4 g-3 form-floating">
                        <input type="text" class="form-control" name="software_name" id="software_name" placeholder="Software Name" value="{{ $software->software_name }}" required @readonly($readOnly)>
                        <label class="text-center" for="software_name">Software Name</label>
                        @if ($errors->has('software_name'))
                        <p class="text-danger">{{ $errors->first('software_name') }}</p>
                        @endif
                    </div>
                </div>
                <div class="row mb-2 ps-3 pe-3">
                    <div class="col-md-6 col-8 g-3 form-floating">
                        <select name="software_type" id="software_type" class="form-select form-control" {{ $readOnly ? 'disabled' : '' }}>
                            <option {{ $software->type == 1 ? "selected" : "" }} value="1">{{ config('constants.SOFTWARE_TYPE_1_NAME') }}</option>
                            <option {{ $software->type == 2 ? "selected" : "" }} value="2">{{ config('constants.SOFTWARE_TYPE_2_NAME') }}</option>
                            <option {{ $software->type == 3 ? "selected" : "" }} value="3">{{ config('constants.SOFTWARE_TYPE_3_NAME') }}</option>
                            <option {{ $software->type == 4 ? "selected" : "" }} value="4">{{ config('constants.SOFTWARE_TYPE_4_NAME') }}</option>
                            <option {{ $software->type == 5 ? "selected" : "" }} value="5">{{ config('constants.SOFTWARE_TYPE_5_NAME') }}</option>
                            <option {{ $software->type == 6 ? "selected" : "" }} value="6">{{ config('constants.SOFTWARE_TYPE_6_NAME') }}</option>
                        </select>
                        <label  class="text-center" for="software_type">Software Type</label>
                        @if ($errors->has('software_type'))
                        <p class="text-danger">{{ $errors->first('software_type') }}</p>
                        @endif
                    </div>
                </div>
                <div class="row mb-2 ps-3 pe-3">
                    <div class="col-4 g-3 form-floating">
                        <input type="text" class="form-control" name="software_purpose" id="software_purpose" placeholder="Purpose" value="{{ $software->remarks }}" required @readonly($readOnly)>
                        <label class="text-center" for="software_purpose">Purpose</label>
                        @if ($errors->has('software_purpose'))
                        <p class="text-danger">{{ $errors->first('software_purpose') }}</p>
                        @endif
                    </div>
                </div>
                <div class="row mb-2 ps-3 pe-3">
                    <div class="col-4 g-3 form-floating">
                        <input type="text" class="form-control" name="software_createdby" id="software_createdby" placeholder="Created By" value="{{ $requestor }}" required @readonly($readOnly)>
                        <label class="text-center" for="software_createdby">Created By</label>
                        @if ($errors->has('software_createdby'))
                        <p class="text-danger">{{ $errors->first('software_createdby') }}</p>
                        @endif
                    </div>
                </div>
                @if( $is_display_approver)
                    <div class="row mb-2 ps-3 pe-3">
                        <div class="col-4 g-3 form-floating">
                            <input type="text" class="form-control" name="software_approvedby" id="software_approvedby" placeholder="Approved By" value="{{ $approver }}" required @readonly($readOnly)>
                            <label class="text-center" for="software_approvedby">Approved By</label>
                            @if ($errors->has('software_approvedby'))
                            <p class="text-danger">{{ $errors->first('software_approvedby') }}</p>
                            @endif
                        </div>
                    </div>
                @endif
            </div>
        </form>
    </div>
    @if ($detailOnly)
    <div class="soft-regist-category mb-4 p-3 rounded-3">
        <div class="d-flex justify-content-between">
            <h4 class="text-start">Projects</h4>
            <button class="btn btn-primary" data-bs-target="#softLinkProjectModal" data-bs-toggle="modal">Add</button>
        </div>
        <table class="table table-bordered border-secondary mt-3" id="soft-project-tbl">
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
                            <td><a href="{{ route('project.details', ['id' => $project['project_id']]) }}" class="text-decoration-none">{{ $project['name'] }}</a></td>
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
        <button class="btn btn-danger btn-lg mb-5 me-4 rqst-btn"  data-bs-target="#softwarerejectRequestModal" data-bs-toggle="modal" id="soft-reject-request">Reject</button>
        <button class="btn btn-success btn-lg mb-5 ms-4 rqst-btn" id="soft-approve-request"  form="soft-approve-request-form">Approve</button>
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
                    <button class="btn btn-danger" id="soft-reject-sub" type="submit" form="soft-reject-request-form">Reject</button>
                </div>
            </div>
        </div>
    </div>
    @endif

    @if ($detailOnly)
    <div class="modal fade" tabindex="-1" id="softLinkProjectModal">
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
                            <div class="row mb-2">
                                <div class="col-12 g-3 form-floating">
                                    <select name="project_id" class="form-select" id="projectList" required>
                                        @foreach ( $projectList as $project )
                                            <option data-mindate="{{ date('Y-m-d', strtotime($project['start_date']))  }}" data-maxdate="{{ !empty($project['end_date']) ? date('Y-m-d', strtotime($project['end_date'])) : date("Y-m-d")  }}" value="{{ $project['id'] }}">{{ $project['name'] }}</option>
                                        @endforeach
                                    </select>
                                    <label for="projectList" class="text-center">Project Name</label>
                                    <p id="error-lp-proj-name"></p>
                                </div>
                            </div>
                            <div class="row mb-2">
                                <div class="col-12 g-3 form-floating">
                                    <input type="text" name="project_remarks">
                                    <div class="mb-2">
                                        <textarea class="form-control" name="project_remarks" placeholder="Remarks" rows="5" id="soft-project_remarks" required></textarea>
                                    </div>
                                    <p id="soft-project_remarks-error"></p>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button class="btn btn-primary" type="submit" id="lp-submit-btn">Link</button>
                </div>
            </div>
        </div>
    </div>
    @endif


</div>

@include('footer')