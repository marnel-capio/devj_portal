@php
    $userInfo = Auth::user();
@endphp

@include('header')
{{-- <link rel="stylesheet" href="{{ asset(mix('css/employee.min.css')) }}"> --}}
<script src="{{ asset(mix('js/laptop.min.js')) }}" defer></script>
@include('headerMenu')

<div class="container ps-md-3 pe-md-3 pt-5">
    <div class="d-flex justify-content-between mb-2">
        <div class="text-primary d-flex align-items-center">
            @if (!empty($detailNote))
            <i class="bi bi-info-circle-fill"></i>&nbsp;{{ $detailNote }}
            @endif
        </div>
        <div class="">
            @if ($detailOnly && !empty($detail) && $detail['approved_status'] == config('constants.APPROVED_STATUS_APPROVED'))
            <a href="{{ route('laptops.edit', ['id' => $detail->id]) }}" class="btn btn-primary  me-1" type="button">Edit</a>
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
                        <label for="form-number" class="text-center">Permit Number</label>
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
            @if ($owned)
                @if ($isLinkageUpdatable)
                    <button class="btn btn-secondary" data-bs-target="#linkProjectModal" data-bs-toggle="modal">Update</button>
                @endif
            @endif
            @else
                <button class="btn btn-primary" data-bs-target="#linkProjectModal" data-bs-toggle="modal">Link</button>
            @endif
        </div>
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
                @if(!empty($history))

                @endif
            </tbody>
        </table>
    </div>
</div>