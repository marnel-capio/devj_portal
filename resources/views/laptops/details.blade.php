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
<div class="container ps-md-3 pe-md-3 pt-2">
@else
<div class="container ps-md-3 pe-md-3 pt-5">
@endif
    <div class="d-flex justify-content-between mb-2">
        <div class="text-primary d-flex align-items-center">
            @if (!empty($detailNote))
            <i class="bi bi-info-circle-fill"></i>&nbsp;{{ $detailNote }}
            @endif
        </div>
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
    </div>
    <div class="pt-4">
        <form action="#">
            <div class="group-category p-3 mb-4 rounded-3">
                <h4 class="text-start">Laptop Details</h4>
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
    <div class="group-category mb-4 p-3 rounded-3">
        <div class="d-flex justify-content-between">
            <h4 class="text-start">Employee History</h4>
            @if (!empty($linkageData))
                @if ($linkageData['approved_status'] == config('constants.APPROVED_STATUS_APPROVED'))
                    <button class="btn btn-success" data-bs-target="#updateLinkageModal" data-bs-toggle="modal">Update</button>
                @endif
            @else
                <button class="btn btn-primary" data-bs-target="#newLinkageModal" data-bs-toggle="modal">Link</button>
            @endif
        </div>

        <div class="ms-3">
            @if (!empty($linkageData) && $linkageData['approved_status'] == config('constants.APPROVED_STATUS_APPROVED'))
            <div class="text-primary d-flex align-items-center">
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
            @if (!empty($linkageData))
            <div class="text-primary d-flex align-items-center">
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

                </tbody>
            </table>
        </div>
    </div>
</div>

@include('footer')