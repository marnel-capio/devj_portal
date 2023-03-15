@php
    $userInfo = Auth::user();
@endphp

@include('header')
<link rel="stylesheet" href="{{ asset(mix('css/software.min.css')) }}">
<script src="{{ asset(mix('js/software.min.js')) }}" defer></script>
@include('headerMenu')

<div class="container text-center ps-3 pe-3 pt-5">
    <h3 class="text-start">Software Update</h3>
    <div class="pt-4">
        @if ($errors->has('id'))
        <p class="text-danger mb-2">{{ $errors->first('id') }}</p>
        @endif
        <form action="{{ route('softwares.update') }}" method="POST" id="soft-update-form">
            @csrf
            <input type="text" name="id" hidden value="{{ $software->id }}">
            <div class="soft-regist-category p-3 mb-4 rounded-3">
                <h4 class="text-start">Software Details</h4>
                <div class="row mb-2 ps-3 pe-3">
                    <div class="col-4 g-3 form-floating">
                        <input type="text" class="form-control" name="approved_status" id="approved_status" placeholder="Status" value="{{ $current_status }}" readonly>
                        <label class="text-center" for="approved_status">Status</label>
                        @if ($errors->has('approved_status'))
                        <p class="text-danger">{{ $errors->first('approved_status') }}</p>
                        @endif
                    </div>
                </div>
                <div class="row mb-2 ps-3 pe-3">
                    <div class="col-4 g-3 form-floating">
                        <input type="text" class="form-control" name="software_name" id="software_name" placeholder="Software Name" value="{{ old('sofwaret_name', $software->software_name) }}" required>
                        <label class="text-center" for="software_name">Software Name</label>
                        @if ($errors->has('software_name'))
                        <p class="text-danger">{{ $errors->first('software_name') }}</p>
                        @endif
                    </div>
                </div>
                <div class="row mb-2 ps-3 pe-3  align-middle">
                    <div class="col-4 g-3 form-floating">
                        <select name="software_type_id" id="software_type_id" class="form-select form-control">
                            @foreach ($software_types as $software_type)
                                <option {{ old('software_type_id', $software ? $software->software_type_id : '') == $software_type['id'] ? "selected" : "" }} value={{  $software_type['id']  }}>{{ $software_type['type_name'] }}</option>
                            @endforeach
                            <option 
                                {{ (old('software_type_id', $software ? $software->software_type_id : '') == config('constants.SOFTWARE_TYPE_999') || ($new_software_type) )   ? "selected" : "" }} 
                                value={{ config('constants.SOFTWARE_TYPE_999') }}>{{ config('constants.SOFTWARE_TYPE_999_NAME') }}</option>
                        </select>
                        <label  class="text-center" for="software_type_id">Software Type</label>
                        @if ($errors->has('software_type_id'))
                            <p class="text-danger">{{ $errors->first('software_type_id') }}</p>
                        @endif
                    </div>
                    <div class="col-4 g-3 form-floating">
                        <input 
                            {{ (old('software_type_id', $software ? $software->software_type_id : '') == config('constants.SOFTWARE_TYPE_999') ||  ($new_software_type) ) ? "" : "hidden" }} 
                            type="text" class="form-control" name="new_software_type" id="new_software_type" placeholder="Purpose" 
                            value="{{ ($new_software_type) ? $new_software_type['type_name'] : old('new_software_type') }}">
                        <label {{ ( old('software_type_id', $software ? $software->software_type_id : '') == config('constants.SOFTWARE_TYPE_999') ||  ($new_software_type) ) ? "" : "hidden" }} 
                            class="text-center" for="new_software_type" id="new_software_type_label">New Software Type</label>
                        @if ($errors->has('new_software_type'))
                            <p class="text-danger" id = "new_software_type_error">{{ $errors->first('new_software_type') }}</p>
                        @endif
                    </div>                    
                </div>
                <div class="row mb-2 ps-3 pe-3">
                    <div class="col-4 g-3 form-floating">
                        <textarea class="form-control" name="remarks" id="remarks" placeholder="Purpose" required> {{ old('remarks', $software->remarks) }}</textarea>
                        <label class="text-center" for="remarks">Purpose</label>
                        @if ($errors->has('remarks'))
                        <p class="text-danger">{{ $errors->first('remarks') }}</p>
                        @endif
                    </div>
                </div>
                <div class="row mb-2 ps-3 pe-3">
                    <div class="col-4 g-3 form-floating">
                        <input type="text" class="form-control" name="created_by" id="created_by" placeholder="Created By" value="{{ $software->creator }}" readonly>
                        <label class="text-center" for="created_by">Created By</label>
                        @if ($errors->has('created_by'))
                        <p class="text-danger">{{ $errors->first('created_by') }}</p>
                        @endif
                    </div>
                    <div class="col-4 g-3 form-floating">
                        <input type="text" class="form-control" name="create_date" id="create_date" placeholder="Create Date" value="{{ $software->create_time }}" readonly>
                        <label class="text-center" for="create_date">Create Date</label>
                        @if ($errors->has('create_date'))
                        <p class="text-danger">{{ $errors->first('create_date') }}</p>
                        @endif
                    </div>                    
                </div>
                <div class="row mb-2 ps-3 pe-3">
                    <div class="col-4 g-3 form-floating">
                        <input type="text" class="form-control" name="updated_by" id="updated_by" placeholder="Updated By" value="{{ $software->updater }}" readonly>
                        <label class="text-center" for="updated_by">Updated By</label>
                        @if ($errors->has('updated_by'))
                        <p class="text-danger">{{ $errors->first('updated_by') }}</p>
                        @endif
                    </div>
                    <div class="col-4 g-3 form-floating">
                        <input type="text" class="form-control" name="update_date" id="update_date" placeholder="Update Date" value="{{ $software->update_time }}" readonly>
                        <label class="text-center" for="update_date">Update Date</label>
                        @if ($errors->has('update_date'))
                        <p class="text-danger">{{ $errors->first('update_date') }}</p>
                        @endif
                    </div>                    
                </div>                
                <div class="row mb-2 ps-3 pe-3">
                    <div class="col-4 g-3 form-floating">
                        <input type="text" class="form-control" name="approved_by" id="approved_by" placeholder="Approved By" value="{{ $software->approver }}" readonly>
                        <label class="text-center" for="approved_by">Approved By</label>
                        @if ($errors->has('approved_by'))
                        <p class="text-danger">{{ $errors->first('approved_by') }}</p>
                        @endif
                    </div>
                    <div class="col-4 g-3 form-floating">
                        <input type="text" class="form-control" name="approve_date" id="approve_date" placeholder="Approve Date" value="{{ $software->approve_time }}" readonly>
                        <label class="text-center" for="approve_date">Approve Date</label>
                        @if ($errors->has('approve_date'))
                        <p class="text-danger">{{ $errors->first('approve_date') }}</p>
                        @endif
                    </div>                    
                </div>
            </div>
            <div class="text-center p-2">
                <button class="btn btn-primary btn-lg mb-5 btn-prevent-multiple-submit" id="soft-update-submit" type="submit">Update</button>
            </div>
        </form>
    </div>

    <div class="modal fade" tabindex="-1" id="addtype">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title text-start">Add Software Type</h5>
                </div>
                <div class="modal-body">
                    <div class="p-2">
                        <div id="lp-success-msg"></div>
                        <form action="#" id="addtypeForm">
                            <div class="row mb-2">
                                <div class="col-12 g-3 form-floating">
                                    <input type="text" class="form-control" name="project_type" id="project_type" placeholder="Project Type" value="" required>
                                    <label class="text-center" for="project_type">Software Type</label>
                                    @if ($errors->has('project_type'))
                                    <p class="text-danger">{{ $errors->first('project_type') }}</p>
                                    @endif
                                </div>                                
                            </div>
                        </form>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button class="btn btn-primary" type="submit" id="lp-submit-btn">Add</button>
                </div>
            </div>
        </div>
    </div>
</div>

@include('footer')