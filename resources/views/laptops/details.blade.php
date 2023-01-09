@php
    $userInfo = Auth::user();
@endphp

@include('header')
{{-- <link rel="stylesheet" href="{{ asset(mix('css/employee.min.css')) }}"> --}}
<script src="{{ asset(mix('js/laptop.min.js')) }}" defer></script>
@include('headerMenu')
@if(!empty(session('l_alert')))
<div class="alert alert-success" role="alert">
    {{session()->pull('l_alert')}}
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
        @if ($detailOnly)
        <div class="">
            @if ($detailOnly && !empty($detail) && $detail['approved_status'] == config('constants.APPROVED_STATUS_APPROVED'))
            <button type="button" class="btn btn-primary  ms-1" data-bs-toggle="modal" data-bs-target="#editLaptopModal" >Edit</button>
            <div class="modal modal-lg fade" tabindex='-1' id="editLaptopModal">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">
                                Laptop Update
                            </h5>
                        </div>
                        <div class="modal-body">
                            <div class="p-2">
                                <form action="#" method="POST" id="edit-form">
                                    @csrf
                                    <input type="text" hidden name="edit_id" value="{{ $detail->id }}">
                                    <input type="text" hidden name="isUpdate" value="1">
                                    <div class="group-category p-3 rounded-3">
                                        <div class="row mb-2 ps-5 pe-3">
                                            <div class="col-6 g-3 form-floating">
                                               <input type="text" name="tag_number" class="form-control" id="tag-number" placeholder="Tag Number" value="{{ $detail->tag_number }}" disabled>
                                               <label for="tag-number" class="text-center">Tag Number</label>
                                            </div>
                                            <div class="col-6 g-3">
                                                <div class="d-flex align-items-center" style="height: 100%">
                                                    <div class="form-check ">
                                                        <label class="form-check-label" for="laptop-status">Active Status</label>
                                                        <input type="checkBox" class="form-check-input" name="status" id="laptop-status" value="1" {{ $detail->status ? "checked" : "" }}>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row pt-4 ps-3 pe-3">
                                            <h5>PEZA</h5>
                                        </div>
                                        <div class="row mb-2 ps-5 pe-3">
                                            <div class="col-6 g-3 form-floating">
                                                <input type="text" name="peza_form_number" class="form-control" id="form-number" placeholder="Form Number" value="{{ $detail->peza_form_number }}" required>
                                                <label for="form-number" class="text-center">Form Number</label>
                                                <p class="text-danger" id="peza_form_number-error"></p>
                                             </div>
                                        </div>
                                        <div class="row mb-2 ps-5 pe-3">
                                            <div class="col-6 g-3 form-floating">
                                                <input type="text" name="peza_permit_number" class="form-control" id="permit-number" placeholder="Permit Number" value="{{ $detail->peza_permit_number }}" required>
                                                <label for="permit-number" class="text-center">Permit Number</label>
                                                <p class="text-danger" id="peza_permit_number-error"></p>
                                             </div>
                                        </div>
                                        <div class="row pt-4 ps-3 pe-3">
                                            <h5>Details</h5>
                                        </div>
                                        <div class="row mb-2 ps-5 pe-3">
                                            <div class="col-6 g-3 form-floating">
                                                <input type="text" name="laptop_make" class="form-control" id="make" placeholder="Make" value="{{ $detail->laptop_make }}" required>
                                                <label for="make" class="text-center">Make</label>
                                                <p class="text-danger" id="laptop_make-error"></p>
                                             </div>
                                             <div class="col-6 g-3 form-floating">
                                                <input type="text" name="laptop_model" class="form-control" id="model" placeholder="Model" value="{{ $detail->laptop_model }}" required>
                                                <label for="model" class="text-center">Model</label>
                                                <p class="text-danger" id="laptop_model-error"></p>
                                             </div>
                                        </div>
                                        <div class="row mb-2 ps-5 pe-3">
                                            <div class="col-4 g-3 form-floating">
                                                <input type="text" name="laptop_cpu" class="form-control" id="cpu" placeholder="CPU" value="{{  $detail->laptop_cpu  }}" required>
                                                <label for="cpu" class="text-center">CPU</label>
                                                <p class="text-danger" id="laptop_cpu-error"></p>
                                             </div>
                                             <div class="col-4 g-3 form-floating">
                                                <input type="text" name="laptop_clock_speed" class="form-control" id="clock-speed" placeholder="Clock Speed (GHz)" value="{{  $detail->laptop_clock_speed  }}" required>
                                                <label for="clock-speed" class="text-center">Clock Speed (GHz)</label>
                                                <p class="text-danger" id="laptop_clock_speed-error"></p>
                                             </div>
                                             <div class="col-4 g-3 form-floating">
                                                <input type="text" name="laptop_ram" class="form-control" id="ram" placeholder="RAM (GB)" value="{{ $detail->laptop_ram }}" required>
                                                <label for="ram" class="text-center">RAM (GB)</label>
                                                <p class="text-danger" id="laptop_ram-error"></p>
                                             </div>
                                        </div>
                                        <div class="row pt-4 ps-3 pe-3">
                                            <h5>Remarks</h5>
                                        </div>
                                        <div class="row mb-2 ps-5 pe-3">
                                            <div class="">
                                                <textarea class="form-control" name="remarks"  rows="3" id="remarks" required>{{ $detail->remarks }}</textarea>
                                                <p class="text-danger" id="remarks-error"></p>
                                            </div>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            <button class="btn btn-primary" type="submit"  id="el-submit-btn" form="edit-form">Submit</button>
                        </div>
                    </div>
                </div>
            </div>
            @endif
        </div> 
        @endif
    </div>
    <div class="pt-2">
        <form action="#">
            @if(!$detailOnly)
            <div class="row mb-2 ps-3 pe-3">
                <div class="col-6 g-3">
                    <div class="row">
                        <h6 class="text-danger">※Requested by {{ $requestor->requestor }}</h6>
                    </div>
                </div>
            </div>
            @endif
            <div class="group-category p-3 mb-4 rounded-3">
                @if ($detailOnly)
                    <h4 class="text-start">Laptop Details</h4>
                @else
                    <h4 class="d-inline text-start">Laptop Request</h4>&nbsp;&nbsp;
                @endif
                <div class="row mb-2 ps-5 pe-3">
                    <div class="col-md-3 col-6 g-3 form-floating">
                       <input type="text" name="tag_number" class="form-control" id="tag-number" placeholder="Tag Number" value="{{ $detail->tag_number }}" readonly>
                       <label for="tag-number" class="text-center">Tag Number</label>
                    </div>
                    <div class="col-6 g-3">
                        <div class="d-flex align-items-center" style="height: 100%">
                            <div class="form-check ">
                                <label class="form-check-label" for="laptop-status">Active Status</label>
                                <input type="checkBox" class="form-check-input" name="status" id="laptop-status" value="0" {{ $detail->status ? "checked" : "" }} disabled>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row pt-4 ps-3 pe-3">
                    <h5>PEZA</h5>
                </div>
                <div class="row mb-2 ps-5 pe-3">
                    <div class="col-md-3 col-6 g-3 form-floating">
                        <input type="text" name="peza_form_number" class="form-control" id="form-number" placeholder="Form Number" value="{{ $detail->peza_form_number }}" readonly>
                        <label for="form-number" class="text-center">Form Number</label>
                     </div>
                </div>
                <div class="row mb-2 ps-5 pe-3">
                    <div class="col-md-3 col-6 g-3 form-floating">
                        <input type="text" name="peza_permit_number" class="form-control" id="permit-number" placeholder="Permit Number" value="{{ $detail->peza_permit_number }}" readonly>
                        <label for="permit-number" class="text-center">Permit Number</label>
                     </div>
                </div>
                <div class="row pt-4 ps-3 pe-3">
                    <h5>Details</h5>
                </div>
                <div class="row mb-2 ps-5 pe-3">
                    <div class="col-md-3 col-6 g-3 form-floating">
                        <input type="text" name="laptop_make" class="form-control" id="make" placeholder="Make" value="{{ $detail->laptop_make }}" readonly>
                        <label for="make" class="text-center">Make</label>
                     </div>
                     <div class="col-md-3 col-6 g-3 form-floating">
                        <input type="text" name="laptop_model" class="form-control" id="model" placeholder="Model" value="{{ $detail->laptop_model }}" readonly>
                        <label for="model" class="text-center">Model</label>
                     </div>
                </div>
                <div class="row mb-2 ps-5 pe-3">
                    <div class="col-md-2 col-4 g-3 form-floating">
                        <input type="text" name="laptop_cpu" class="form-control" id="cpu" placeholder="CPU" value="{{ $detail->laptop_cpu }}" readonly>
                        <label for="cpu" class="text-center">CPU</label>
                     </div>
                     <div class="col-md-2 col-4 g-3 form-floating">
                        <input type="text" name="laptop_clock_speed" class="form-control" id="clock-speed" placeholder="Clock Speed (GHz)" value="{{ $detail->laptop_clock_speed }}" readonly>
                        <label for="clock-speed" class="text-center">Clock Speed (GHz)</label>
                     </div>
                     <div class="col-md-2 col-4 g-3 form-floating">
                        <input type="text" name="laptop_ram" class="form-control" id="ram" placeholder="RAM (GB)" value="{{ $detail->laptop_ram }}" readonly>
                        <label for="ram" class="text-center">RAM (GB)</label>
                     </div>
                </div>
                <div class="row pt-4 ps-3 pe-3">
                    <h5>Remarks</h5>
                </div>
                <div class="row mb-2 ps-5 pe-3">
                    <div class="col-md-6 g-3">
                        <textarea class="form-control" name="remarks"  rows="3" id="remarks" readonly>{{ $detail->remarks }}</textarea>
                    </div>
                </div>
            </div>
        </form>
    </div>
    @if ($detailOnly)
    <div class="group-category mb-4 p-3 rounded-3">
        <div class="d-flex justify-content-between">
            <h4 class="text-start">Employee History</h4>
            @if (!empty($linkageData))
                @if ($linkageData['approved_status'] == config('constants.APPROVED_STATUS_APPROVED'))
                    <button class="btn btn-success" data-bs-target="#updateLinkageModal" data-bs-toggle="modal">Update</button>
                    <div class="modal modal fade" tabindex='-1' id="updateLinkageModal">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">
                                        Update Laptop Details
                                    </h5>
                                </div>
                                <div class="modal-body">
                                    <div class="p-2">
                                        Current Assignee: {{ $linkageData->employee_name }}
                                        <form action="#" id="update-linkage-form">
                                            @csrf
                                            <span id="ul-success-msg"></span>
                                            <input hidden type="text" name="id" value="{{ $linkageData->id }}">
                                            <div class="row mb-2 ">
                                                <div class="col-6 g-3">
                                                    <div class="form-check">
                                                        <label for="ul-brought-home" class="form-check-label">Brought Home?</label>
                                                        <input type="checkbox" class="form-check-input" name="brought_home_flag" id="ul-brought-home" value="1" {{ $linkageData->brought_home_flag ? "checked" : "" }}>
                                                    </div>  
                                                </div>
                                                <div class="col-6 g-3">
                                                    <div class="form-check">
                                                        <label for="ul-vpn" class="form-check-label">VPN Access?</label>
                                                        <input type="checkbox" class="form-check-input" name="vpn_flag" id="ul-vpn" value="1" {{ $linkageData->vpn_flag ? "checked" : "" }}>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row mb-2">
                                                <div class="col-6 g-3">
                                                    <div class="d-flex align-items-center" style="height: 100%">
                                                        <div class="form-check">
                                                            <label for="ul-surrender" class="form-check-label">Surrender</label>
                                                            <input type="checkbox" class="form-check-input" name="surrender_flag" id="ul-surrender" value="1" >
                                                        </div>  
                                                    </div>
                                                </div>
                                                <div class="col-6 g-3 form-floating">
                                                    <input type="date" class="form-control" name="surrender_date" id="ul-surrender-date" placeholder="Surrender Date" value="" pattern="\d{4}-\d{2}-\d{2}">
                                                    <label  class="text-center" for="ul-surrender-date">Surrender Date</label>
                                                </div>
                                                <span id="ul-surrender_date-error"></p>
                                            </div>
                                            <div class="row">
                                                <h6>Remarks</h6>
                                            </div>
                                            <div class="row text-start">
                                                <div class="gs-3 ge-3 gt-1">
                                                    <textarea name="remarks" id="ul-remarks" rows="3" class="form-control">{{ $linkageData->remarks }}</textarea>
                                                    <span id="ul-remarks-error"></span>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                                
                                <div class="modal-footer">
                                    <button class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                    <button class="btn btn-primary" type="submit"  id="ul-submit-btn" form="update-linkage-form">Update</button>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
            @else
                <button class="btn btn-primary" data-bs-target="#newLinkageModal" data-bs-toggle="modal">Link</button>
                <div class="modal modal fade" tabindex='-1' id="newLinkageModal">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">
                                    Link Laptop To Employee
                                </h5>
                            </div>
                            <div class="modal-body">
                                <div class="p-2">
                                    <form action="#" id="link-form">
                                        @csrf
                                        <input hidden type="text" name="id" value="{{ $detail->id }}">
                                        <span id="ll-id-error"></span>
                                        <div class="row mb-2">
                                            <div class="col-12 g-3 form-floating">
                                            @if (in_array(Auth::user()->roles, [config('constants.ADMIN_ROLE_VALUE'), config('constants.MANAGER_ROLE_VALUE')]))
                                                <select name="assignee" class="form-select" id="assigneeList" required>
                                                    <option value=""></option>
                                            @else
                                                <select name="assignee" class="form-select" id="assigneeList" readonly>
                                            @endif
                                                    @foreach ( $employeeDropdown as $employee )
                                                        <option value="{{ $employee['id'] }}">{{ $employee['employee_name'] }}</option>
                                                    @endforeach
                                                </select>
                                                <label for="assigneeList" class="text-center">Assignee</label>
                                                <span id="ll-assignee-error"></span>
                                            </div>
                                        </div>
                                        <div class="row mb-2 ">
                                            <div class="col-6 g-3">
                                                <div class="form-check">
                                                    <label for="ll-brought-home" class="form-check-label">Brought Home?</label>
                                                    <input type="checkbox" class="form-check-input" name="brought_home_flag" id="ll-brought-home" value="1">
                                                </div>  
                                            </div>
                                            <div class="col-6 g-3">
                                                <div class="form-check">
                                                    <label for="ll-vpn" class="form-check-label">VPN Access?</label>
                                                    <input type="checkbox" class="form-check-input" name="vpn_flag" id="ll-vpn" value="1">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row pt-2">
                                            <h6>Remarks</h6>
                                        </div>
                                        <div class="row text-start">
                                            <div class="gs-3 ge-3 gt-1">
                                                <textarea name="remarks" id="ll-remarks" rows="3" class="form-control"></textarea>
                                                <span id="ll-remarks-error"></span>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                            
                            <div class="modal-footer">
                                <button class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                <button class="btn btn-primary" type="submit"  id="ll-submit-btn" form="link-form">Link</button>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>

        <div class="ms-3">
            @if(!empty(session('ul_alert')))
                <div class="alert alert-success mt-2" role="alert">
                    {{session()->pull('ul_alert')}}
                </div>
            @elseif(!empty(session('ll_alert')))
                <div class="alert alert-success mt-2" role="alert">
                    {{session()->pull('ll_alert')}}
                </div>
            @endif
            @if (!empty($linkageData) && $linkageData['approved_status'] == config('constants.APPROVED_STATUS_APPROVED'))
            <div class="text-primary d-flex align-items-center mb-2">
                <i class="bi bi-info-circle-fill"></i>&nbsp;Only the laptop details of the current owner can be updated
            </div>
            @endif
            <table class="table table-bordered border-secondary mt-3" id="emp-hist-tbl">
                <thead class="bg-primary text-white fw-bold">
                    <tr>
                        <th>Member</th>
                        <th>VPN Access?</th>
                        <th>Brought Home?</th>
                        <th>Remarks</th>
                        <th>Surrender Date</th>
                    </tr>
                </thead>
                <tbody class="">
                    @if(!empty($history))
                        @foreach ($history as $data)
                            <tr>
                                <td id="name-col">{{ $data['employee_name'] }}</td>
                                <td id="vpn-col">{{ $data['vpn_flag'] }}</td>
                                <td id="bhf-col">{{ $data['brought_home_flag'] }}</td>
                                <td id="remarks-col">{{ $data['remarks'] }}</td>
                                <td id="sdate-col">{{ $data['surrender_date'] }}</td>
                            </tr>
                        @endforeach
                    @endif
                </tbody>
            </table>
        </div>
    </div>

    <div class="group-category mb-4 p-3 rounded-3">
        <div class="d-flex justify-content-between">
            <h4 class="text-start">Link Requests</h4>

        </div>

        <div class="ms-3">
            @if(!empty(session('lla_alert')))
                <div class="alert alert-success mt-2" role="alert">
                    {{session()->pull('lla_alert')}}
                </div>
            @elseif(!empty(session('llr_alert')))
                <div class="alert alert-success mt-2" role="alert">
                    {{session()->pull('llr_alert')}}
                </div>
            @endif
            @if (empty($linkageData) && !empty($linkageRequest))
            <div class="text-primary d-flex align-items-center mb-2">
                <i class="bi bi-info-circle-fill"></i>&nbsp;Once a request has been approved, other request will be rejected
            </div>
            @endif
            <table class="table table-bordered border-secondary mt-3" id="link-req-tbl">
                <thead class="bg-primary text-white fw-bold">
                    <tr>
                        <th>Requestor</th>
                        <th>VPN Access?</th>
                        <th>Brought Home?</th>
                        <th>Request Date</th>
                        <th>Remarks</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody class="">
                    @foreach ($linkageRequest as $request)
                        <tr>
                            <td>{{ $request['employee_name'] }}</td>
                            <td>{{ $request['vpn_access'] }}</td>
                            <td>{{ $request['brought_home'] }}</td>
                            <td>{{ date('Y-m-d', strtotime($request['request_date'])); }}</td>
                            <td>{{ $request['remarks'] }}</td>
                            @if(Auth::user()->roles == config('constants.MANAGER_ROLE_VALUE') )
                                <td>
                                    <button class="btn btn-link btn-sm text-decoration-none" data-bs-target="#rejectLinkageRequestModal" data-bs-toggle="modal"><span class="text-danger">Reject</span></button>
                                    <div class="modal fade" tabindex="-1" id="rejectLinkageRequestModal">
                                        <div class="modal-dialog modal-dialog-centered">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">
                                                        Reject Laptop Link Request
                                                    </h5>
                                                </div>
                                                <div class="modal-body">
                                                    <div class="p-2">
                                                        <form action="{{ route('laptops.rejectLinkage') }}" method="POST" id="reject-request-form">
                                                            @csrf
                                                            <input type="text" name="id" value="{{ $request['id'] }}" hidden>
                                                            <div class="mb-2">
                                                                <textarea class="form-control" name="reason" placeholder="Reason" rows="5" id="reject-reason" required></textarea>
                                                            </div>
                                                            <p id="reject-reason-error"></p>
                                                        </form>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                                    <button class="btn btn-danger" id="reject-sub" type="submit" form="reject-request-form">Reject</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    /
                                    <button class="btn btn-link btn-sm text-decoration-none" form="link-request-form"><span class="text-success">Approve</span></button>
                                    <form action="{{ route('laptops.storeLinkage') }}" id="link-request-form" method="POST">
                                        @csrf
                                        <input type="text" hidden name="id" value="{{ $request['id'] }}">
                                    </form>
                                </td>
                            @else
                                <td></td>
                            @endif
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

    
    @if (!$detailOnly)
    <div class="text-center p-4">
        <button class="btn btn-danger btn-lg mb-5 me-4 rqst-btn"  data-bs-target="#rejectRequestModal" data-bs-toggle="modal" id="reject-request">Reject</button>
        <button class="btn btn-success btn-lg mb-5 ms-4 rqst-btn" id="approve-request"  form="approve-request-form">Approve</button>
        <form action="{{ route('laptops.storeLinkage') }}" method="POST" id="approve-request-form">
            @csrf
            <input type="text" name="id" hidden value="{{ $detail->id }}">
        </form>
    </div>
    <div class="modal fade" tabindex="-1" id="rejectRequestModal">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        Reject Laptop Request
                    </h5>
                </div>
                <div class="modal-body">
                    <div class="p-2">
                        <form action="{{ route('laptops.reject') }}" method="POST" id="reject-request-form">
                            @csrf
                            <input type="text" name="id" value="{{ $detail->id }}" hidden>
                            <div class="mb-2">
                                <textarea class="form-control" name="reason" placeholder="Reason" rows="5" id="reject-reason" required></textarea>
                            </div>
                            <p id="reject-reason-error"></p>
                        </form>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button class="btn btn-danger" id="reject-sub" type="submit" form="reject-request-form">Reject</button>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>

@include('footer')