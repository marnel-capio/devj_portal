@include('header')
@include('headerMenu')
<div class="container ps-3 pe-3 pt-5">
    <h3>Project Registration</h3>
    <div class="pt-4">
        <form action="{{ $isRegist ? route('projects.regist') : route('projects.store') }}" method="POST">
            @csrf
            @if (!$isRegist)
                <input type="text" name="id" value="{{ $project->id }}" hidden>
            @endif
            <div class="group-category p-3 mb-4 rounded-3">
                <h4 class="text-start">Project Details</h4>
                <div class="row mb-2 ps-5 pe-3">
                    <div class="col-md-6 g-3 form-floating">
                       <input type="text" name="name" class="form-control" id="name" placeholder="Project Name" value="{{ old('name', $project->name) }}" required>
                       <label for="name" class="text-center">Project Name</label>
                       @if ($errors->has('name'))
                       <p class="text-danger">{{ $errors->first('name') }}</p>
                       @endif
                    </div>
                </div>
                <div class="row mb-2 ps-5 pe-3">
                    <div class="col-md-3 col-6 g-3 form-floating">
                        <input type="date" name="start_date" class="form-control" id="start_date" placeholder="Start Date" value="{{ old('start_date', date('Y-m-d', strtotime($project->start_date))) }}" pattern="\d{4}-\d{2}-\d{2}" required>
                        <label for="start_date" class="text-center">Start Date</label>
                        @if ($errors->has('start_date'))
                        <p class="text-danger">{{ $errors->first('start_date') }}</p>
                        @endif
                     </div>
                    <div class="col-md-3 col-6 g-3 form-floating">
                        <input type="date" name="end_date" class="form-control" id="end_date" placeholder="End Date" value="{{ old('end_date', $project->end_date ? date('Y-m-d', strtotime($project->end_date)) : "") }}" pattern="\d{4}-\d{2}-\d{2}">
                        <label for="end_date" class="text-center">End Date</label>
                        @if ($errors->has('end_date'))
                        <p class="text-danger">{{ $errors->first('end_date') }}</p>
                        @endif
                     </div>
                </div>
                <div class="row pt-4 ps-3 pe-3">
                    <h5>Remarks</h5>
                </div>
                <div class="row mb-2 ps-5 pe-3">
                    <div class="col-md-6 g-3">
                        <textarea class="form-control" name="remarks"  rows="3" id="remarks">{{ old('remarks', $project->remarks) }}</textarea>
                    </div>
                    @if ($errors->has('remarks'))
                    <p class="text-danger">{{ $errors->first('remarks') }}</p>
                    @endif
                </div>
            </div>
            <div class="text-center p-2">
                <button class="btn btn-primary btn-lg mb-5" id="project-reg-submit" type="submit">Submit</button>
            </div>
        </form>
    </div>
</div>

@include('footer')