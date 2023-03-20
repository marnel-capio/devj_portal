@include('header')
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
                        <input type="date" name="start_date" class="form-control" id="start_date" placeholder="Start Date" value="{{ $projectData->start_date }}" pattern="\d{4}-\d{2}-\d{2}">
                        <label for="start_date" class="text-center">Start Date</label>
                     </div>
                    <div class="col-md-3 col-6 g-3 form-floating">
                        <input type="date" name="end_date" class="form-control" id="end_date" placeholder="End Date" value="{{ $projectData->end_date }}" pattern="\d{4}-\d{2}-\d{2}">
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
</div>

@include('footer')