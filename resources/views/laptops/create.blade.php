@include('header')
<script src="{{ asset(mix('js/laptop.min.js')) }}" defer></script>
@include('headerMenu')
<div class="container ps-3 pe-3 pt-5">
    <h3>Laptop Registration</h3>
    <div class="pt-4">
        <form id="lapreg_form" action="{{ route('laptops.regist') }}" method="POST">
            @csrf
            <input type="text" name="id" value="{{ !empty($laptop->id) ? $laptop->id : '' }}" hidden >
            <div class="group-category p-3 mb-4 rounded-3">
                <h4 class="text-start">Laptop Details</h4>
                @if ($errors->has('id'))
                <p class="text-danger">{{ $errors->first('id') }}</p>
                @endif
                <div class="row mb-2 ps-5 pe-3">
                    <div class="col-md-3 col-6 g-3 form-floating">
                       <input type="text" name="tag_number" class="form-control" id="tag-number" placeholder="Tag Number" value="{{ old('tag_number', !empty($laptop) ? $laptop->tag_number : '') }}" required>
                       <label for="tag-number" class="text-center">Tag Number</label>
                       @if ($errors->has('tag_number'))
                       <p class="text-danger">{{ $errors->first('tag_number') }}</p>
                       @endif
                    </div>
                </div>
                <div class="row pt-4 ps-3 pe-3">
                    <h5>PEZA</h5>
                </div>
                <div class="row mb-2 ps-5 pe-3">
                    <div class="col-md-3 col-6 g-3 form-floating">
                        <input type="text" name="peza_form_number" class="form-control" id="form-number" placeholder="Form Number" value="{{ old('peza_form_number', !empty($laptop) ? $laptop->peza_form_number : '') }}" required>
                        <label for="form-number" class="text-center">Form Number</label>
                        @if ($errors->has('peza_form_number'))
                        <p class="text-danger">{{ $errors->first('peza_form_number') }}</p>
                        @endif
                     </div>
                </div>
                <div class="row mb-2 ps-5 pe-3">
                    <div class="col-md-3 col-6 g-3 form-floating">
                        <input type="text" name="peza_permit_number" class="form-control" id="permit-number" placeholder="Permit Number" value="{{ old('peza_permit_number', !empty($laptop) ? $laptop->peza_permit_number : '') }}" required>
                        <label for="permit-number" class="text-center">Permit Number</label>
                        @if ($errors->has('peza_permit_number'))
                        <p class="text-danger">{{ $errors->first('peza_permit_number') }}</p>
                        @endif
                     </div>
                </div>
                <div class="row pt-4 ps-3 pe-3">
                    <h5>Details</h5>
                </div>
                <div class="row mb-2 ps-5 pe-3">
                    <div class="col-md-3 col-6 g-3 form-floating">
                        <input type="text" name="laptop_make" class="form-control" id="make" placeholder="Make" value="{{ old('laptop_make', !empty($laptop) ? $laptop->laptop_make : '') }}" required>
                        <label for="make" class="text-center">Make</label>
                        @if ($errors->has('laptop_make'))
                        <p class="text-danger">{{ $errors->first('laptop_make') }}</p>
                        @endif
                     </div>
                     <div class="col-md-3 col-6 g-3 form-floating">
                        <input type="text" name="laptop_model" class="form-control" id="model" placeholder="Model" value="{{ old('laptop_model', !empty($laptop) ? $laptop->laptop_model : '') }}" required>
                        <label for="model" class="text-center">Model</label>
                        @if ($errors->has('laptop_model'))
                        <p class="text-danger">{{ $errors->first('laptop_model') }}</p>
                        @endif
                     </div>
                </div>
                <div class="row mb-2 ps-5 pe-3">
                    <div class="col-md-2 col-4 g-3 form-floating">
                        <input type="text" name="laptop_cpu" class="form-control" id="cpu" placeholder="CPU" value="{{ old('laptop_cpu', !empty($laptop) ? $laptop->laptop_cpu : '') }}" required>
                        <label for="cpu" class="text-center">CPU</label>
                        @if ($errors->has('laptop_cpu'))
                        <p class="text-danger">{{ $errors->first('laptop_cpu') }}</p>
                        @endif
                     </div>
                     <div class="col-md-2 col-4 g-3 form-floating">
                        <input type="text" name="laptop_clock_speed" class="form-control" id="clock-speed" placeholder="Clock Speed (GHz)" value="{{ old('laptop_clock_speed', !empty($laptop) ? $laptop->laptop_clock_speed : '') }}" required>
                        <label for="clock-speed" class="text-center">Clock Speed (GHz)</label>
                        @if ($errors->has('laptop_clock_speed'))
                        <p class="text-danger">{{ $errors->first('laptop_clock_speed') }}</p>
                        @endif
                     </div>
                     <div class="col-md-2 col-4 g-3 form-floating">
                        <input type="text" name="laptop_ram" class="form-control" id="ram" placeholder="RAM (GB)" value="{{ old('laptop_ram', !empty($laptop) ? $laptop->laptop_ram : '') }}" required>
                        <label for="ram" class="text-center">RAM (GB)</label>
                        @if ($errors->has('laptop_ram'))
                        <p class="text-danger">{{ $errors->first('laptop_ram') }}</p>
                        @endif
                     </div>
                </div>
                <div class="row pt-4 ps-3 pe-3">
                    <h5>Remarks</h5>
                </div>
                <div class="row mb-2 ps-5 pe-3">
                    <div class="col-md-6 g-3">
                        <textarea class="form-control" name="remarks"  rows="3" id="remarks">{{ old('remarks', !empty($laptop) ? $laptop->remarks : '') }}</textarea>
                    </div>
                    @if ($errors->has('remarks'))
                    <p class="text-danger">{{ $errors->first('remarks') }}</p>
                    @endif
                </div>
            </div>
            <div class="group-category p-3 mb-4 rounded-3">
                <h4 class="text-start d-inline-block">Linkage Data</h4>
                @if (auth()->user()->roles != config('constants.ENGINEER_ROLE_VALUE'))
                    &nbsp;&nbsp;&nbsp;
                    <div class="form-check form-check-inline">
                        <label class="form-check-label" for="link_to_self">Link laptop to own account</label>
                        <input type="checkBox" class="form-check-input" name="linkage[link_to_self]" id="link_to_self" value="1" {{ old('linkage.link_to_self', !empty($linkage)) == 1 ? "checked" : "" }}>
                        <input type="text" hidden value="0" name="linkage[link_to_self]" id="link_to_self_hidden">
                    </div>
                @else
                    <input type="checkbox" name="linkage[link_to_self]" id="link_to_self" hidden checked>
                @endif
                <div id="linkage_form">
                    <div class="row mb-1 ps-5 pt-3 pe-3">
                        <div class="col-xl-2 col-lg-3 col-4 form-check form-check-inline">
                            <label class="form-check-label" for="brought_home_flag">Brought home?</label>
                            <input type="checkBox" class="form-check-input" name="linkage[brought_home_flag]" id="brought_home_flag" value="1" {{ old('linkage.brought_home_flag', !empty($linkage) ? $linkage->brought_home_flag : 0) == 1 ? "checked" : "" }} >
                            <input type="text" hidden value="0" name="linkage[brought_home_flag]" id="brought_home_flag_hidden">
                        </div>
                        <div class="col-xl-2 col-lg-3 col-4 form-check form-check-inline">
                            <label class="form-check-label" for="vpn_flag">VPN Access</label>
                            <input type="checkBox" class="form-check-input" name="linkage[vpn_flag]" id="vpn_flag" value="1" {{ old('linkage.vpn_flag', !empty($linkage) ? $linkage->vpn_flag : 0) == 1 ? "checked" : "" }} >
                            <input type="text" hidden value="0" name="linkage[vpn_flag]" id="vpn_flag_hidden">
                        </div>
                    </div>
                    <div class="row pt-4 ps-3 pe-3">
                        <h5>Remarks</h5>
                    </div>
                    <div class="row mb-2 ps-5 pe-3">
                        <div class="col-md-6 g-3">
                            <textarea class="form-control" name="linkage[remarks]"  rows="3" id="linkage_remarks">{{ old('linkage.remarks', !empty($linkage) ? $linkage->remarks : '') }}</textarea>
                        </div>
                        @if ($errors->has('linkage.remarks'))
                        <p class="text-danger">{{ $errors->first('linkage.remarks') }}</p>
                        @endif
                    </div>
                </div>
            </div>
            <div class="text-center p-2">
                <button class="btn btn-primary btn-lg mb-5" id="laptopreg-submit" type="submit">Submit</button>
            </div>
        </form>
    </div>
</div>

@include('footer')