@include('header')
{{-- <link rel="stylesheet" href="{{ asset('css/server.css') }}"> --}}
<link rel="stylesheet" href="{{ asset(mix('css/server.min.css')) }}">
@include('headerMenu')
@if(!empty(session('regist_update_alert')))
<div class="alert alert-success" role="alert">
    {{session()->pull('regist_update_alert')}}
</div>
<div class="container-md ps-md-3 pe-md-3 pt-2 mb-3">
@else
<div class="container-md ps-md-3 pe-md-3 pt-5 mb-3">
@endif
    <div class="d-flex justify-content-between mb-3">
        <div class="text-primary d-flex align-items-center">
            @if (!empty($detailNote))
            <i class="bi bi-info-circle-fill"></i>&nbsp;{{ $detailNote }}
            @endif
        </div>
        <div>
            <a href="{{ route('servers.edit', ['id' => $serverData->id]) }}" class="btn btn-primary text-end" type="button">Edit</a>
        </div>
    </div>
    <form id="server_reg_form" action="{{ route('servers.regist') }}" method="POST">
        @csrf
        <div class="group-category p-3 mb-4 rounded-3">
        <h4 class="fw-bold">Server Details</h4>
            <div class="row mb-2 ps-5 pe-3">
                <div class="col-lg-3 col-6 g-3 form-floating">
                    <input type="text" name="server_name" class="form-control" id="server_name" placeholder="Tag Number" value="{{ $serverData->server_name }}" disabled>
                    <label for="server_name" >Server Name</label>
                </div>
                <div class="col-6 g-3">
                    <div class="d-flex align-items-center" style="height: 100%">
                        <div class="form-check ">
                            <label class="form-check-label" for="server_status">Active Status</label>
                            <input type="checkBox" class="form-check-input" name="status" id="server_status" value="1" {{ $serverData->status ? 'checked' : '' }} disabled>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row mb-2 ps-5 pe-3">
                <div class="col-lg-3 col-4 g-3 form-floating">
                    <input type="text" name="server_ip" class="form-control" id="server_ip" placeholder="IP Address" value="{{ $serverData->server_ip }}" disabled>
                    <label for="server_ip" >IP Address</label>
                    </div>
                    <div class="col-lg-3 col-4 g-3 form-floating">
                    <input type="text" name="os" class="form-control" id="os" placeholder="Operating System" value="{{ $serverData->os }}" disabled>
                    <label for="os" >Operating System</label>
                    </div>
                    <div class="col-lg-3 col-4 g-3 form-floating">
                    <input type="text" name="motherboard" class="form-control" id="motherboard" placeholder="Motherboard" value="{{ $serverData->motherboard }}" disabled>
                    <label for="motherboard" >Motherboard</label>
                    </div>
            </div>
            <div class="row mb-2 ps-5 pe-3">
                <div class="col-lg-3 col-4 g-3 form-floating">
                    <input type="text" name="cpu" class="form-control" id="cpu" placeholder="CPU" value="{{ $serverData->cpu }}" disabled>
                    <label for="cpu" >Processor</label>
                </div>
                <div class="col-lg-3 col-4 g-3 form-floating">
                    <input type="text" name="memory" class="form-control" id="memory" placeholder="Memory" value="{{ $serverData->memory }}" disabled>
                    <label for="memory" >RAM</label>
                </div>
                <div class="col-lg-3 col-4 g-3 form-floating">
                    <input type="text" name="server_hdd" class="form-control" id="server_hdd" placeholder="HDD" value="{{ $serverData->hdd }}" disabled>
                    <label for="server_hdd" >HDD</label>
                    @if ($errors->has('server_hdd'))
                    <p class="text-danger">{{ $errors->first('server_hdd') }}</p>
                    @endif
                </div>
            </div>
            <div class="row pt-4 ps-3 pe-3">
                <h5>Function/Role</h5>
            </div>
            <div class="row mb-2 ps-5 pe-3">
                <div class="col-lg-9 g-3">
                    <textarea class="form-control" name="function_role"  rows="3" id="function_role" disabled>{{ $serverData->function_role }}</textarea>
                </div>
            </div>
            <div class="row pt-4 ps-3 pe-3">
                <h5>Remarks</h5>
            </div>
            <div class="row mb-2 ps-5 pe-3">
                <div class="col-lg-9 g-3">
                    <textarea class="form-control" name="remarks"  rows="3" id="remarks" disabled>{{ $serverData->remarks }}</textarea>
                </div>
            </div>
        </div>

        <h3 class="fw-bold d-inline-block">Utilization</h3>&nbsp;&nbsp;&nbsp;
        <span id="legend_text">
            <span id="status_legend">Status Legend:</span>&nbsp;&nbsp;&nbsp;
            <span class="status_normal">Normal: 0%-60%</span>,&nbsp;
            <span class="status_stable">Stable: 61%-89%</span>,&nbsp;
            <span class="status_critical">Critical: 90%-100%</span>
        </span>

        <section id="usage">
{{-- ==================================== HDD Usage ==================================== --}}      
            <div class="group-category p-3 mb-4 mt-2 pt-3 rounded-3">
                <div class="row">
                    <div class="col">
                        <h4 class="subheader">HDD</h4>&nbsp;&nbsp;  
                        <div class="status_group fs-6 fw-bold">
                            Status: <span id="hdd_status" class="{{ config('constants.STATUS_CLASS.' .$serverData->hdd_status) }}">{{ config('constants.STATUS_NAMES.' .$serverData->hdd_status) }}</span>
                        </div>
                    </div>
                </div>

                <div class="row g-3 mb-2 pt-3" id="hdd_partitions">
                    @if (!empty($partitionData))
                        {{-- for detail screen and edit screen --}}
                        @foreach ( $partitionData as $idx => $partition )
                            @php
                                $index = $idx + 1;
                            @endphp
                            <div class="partition_section col-md-6" >
                                <div class="hdd_partition p-1 pt-2">
                                    <div class="row p-2">
                                        <div class="col-md-8 col-9">
                                            <div class="row">
                                                <label for="partition_1" class="col-auto fs-5 fw-bold align-baseline radio">Partition</label>
                                                <div class="col-auto">
                                                <input name="{{ 'hdd[' .$index .'][partition_name]' }}" type="text" class="form-control partition_name" id="{{ 'partition' .$index }}" value="{{ $partition['hdd_partition'] }}" disabled>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="p-2">
                                        <div class="row g-0 pt-1">
                                            <div class="col-3 form-floating">
                                                <input name="{{ 'hdd[' .$index .'][total]' }}" type="text" class="form-control hdd_total" id="{{ 'hdd_total_' .$index }}" placeholder="total" value="{{ $partition['hdd_total'] }}" disabled>
                                                <label for="{{ 'hdd_total_' .$index }}">Total</label>
                                            </div>
                                            <div class="col-2 form-floating">
                                                <select name="{{ 'hdd[' .$index .'][total_unit]' }}" id="{{ 'hdd_total_unit_' .$index }}" class="form-select form-control hdd_total_unit" disabled>
                                                    @foreach (config('constants.SIZE_UNITS') as $idx => $val)
                                                        <option value="{{ $idx }}" {{ $partition['hdd_total_size_type'] == $idx ? 'selected' : '' }}>{{ $val }}</option>
                                                    @endforeach
                                                </select>
                                                <label for="{{ 'hdd_total_unit_' .$index }}">Unit</label>
                                            </div>
                                        </div>
                                        <div class="row g-1 pt-2">
                                            <div class="col">
                                                <div class="fw-bold">
                                                    &nbsp;Size
                                                </div>
                                                <div class="row g-0 pt-2 text-center">
                                                    <div class="col-6 form-floating">
                                                        <input name="{{ 'hdd[' .$index .'][used]' }}" type="text" class="form-control hdd_used" id="{{ 'hdd_used_' .$index }}" placeholder="used" value="{{ $partition['hdd_used_size'] }}" disabled>
                                                        <label for="{{ 'hdd_used_' .$index }}">Used</label>
                                                    </div>
                                                    <div class="col-4 form-floating">
                                                        <select name="{{ 'hdd[' .$index .'][used_unit]' }}" id="{{ 'hdd_used_unit_' .$index }}" class="form-select form-control hdd_used_unit" disabled>
                                                            @foreach (config('constants.SIZE_UNITS') as $idx => $val)
                                                                <option value="{{ $idx }}" {{ $partition['hdd_used_size_type'] == $idx ? 'selected' : '' }}>{{ $val }}</option>
                                                            @endforeach
                                                        </select>
                                                        <label for="{{ 'hdd_used_unit_' .$index }}">Unit</label>
                                                    </div>
                                                </div>
                                                <div class="row g-0 pt-2 text-center">
                                                    <div class="col-6 form-floating">
                                                        <input name="{{ 'hdd[' .$index .'][free]' }}" type="text" class="form-control hdd_free" id="{{ 'hdd_free_' .$index }}" placeholder="Free" value="{{ $partition['hdd_free_size'] }}" disabled>
                                                        <label for="{{ 'hdd_free_' .$index }}">Free</label>
                                                    </div>
                                                    <div class="col-4 form-floating">
                                                        <select name="{{ 'hdd[' .$index .'][free_unit]' }}" id="{{ 'hdd_free_unit_' .$index }}" class="form-select form-control hdd_free_unit" disabled>
                                                            @foreach (config('constants.SIZE_UNITS') as $idx => $val)
                                                                <option value="{{ $idx }}" {{ $partition['hdd_free_size_type'] == $idx ? 'selected' : '' }}>{{ $val }}</option>
                                                            @endforeach
                                                        </select>
                                                        <label for="{{ 'hdd_free_unit_' .$index }}">Unit</label>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col">
                                                <div class="fw-semibold">
                                                    &nbsp;Percentage
                                                </div>
                                                <div class="row g-0 pt-2 text-center">
                                                    <div class="col-6 form-floating">
                                                        <input name="{{ 'hdd[' .$index .'][used_percentage]' }}" type="text" class="form-control hdd_used_percentage" id="{{ 'hdd_used_percentage_' .$index }}" placeholder="used"  value="{{ $partition['hdd_used_percentage'] }}" disabled>
                                                        <label for="{{ 'hdd_used_percentage_' .$index }}">% Used</label>
                                                    </div>
                                                </div>
                                                <div class="row g-0 pt-2 text-center">
                                                    <div class="col-6 form-floating">
                                                        <input name="{{ 'hdd[' .$index .'][free_percentage]' }}" type="text" class="form-control hdd_free_percentage" id="{{ 'hdd_free_percentage_' .$index }}" placeholder="Free"  value="{{ $partition['hdd_free_percentage'] }}" disabled>
                                                        <label for="{{ 'hdd_free_percentage_' .$index }}">% Free</label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    @endif
                </div>
            </div>

{{-- ==================================== Memory Usage and CPU Usage ==================================== --}}

            <div class="row" id="memory_usage_section">
                <div class="col-md-6 ps-2">
                    <div class="group-category p-3 mb-4 pt-3 rounded-3">
                        <h4 class="subheader">Memory</h4>&nbsp;&nbsp;  
                        <div class="status_group fs-6 fw-bold">
                            Status: <span id="memory_status" class="{{ config('constants.STATUS_CLASS.' .$serverData->ram_status) }}">{{ config('constants.STATUS_NAMES.' .$serverData->ram_status) }}</span>
                        </div>
                        <div class="row g-0 pt-3">
                            <div class="col-3 form-floating">
                                <input name="memory_total" type="text" class="form-control" id="memory_total" placeholder="total" value="{{ $serverData->memory_total }}" disabled>
                                <label for="memory_total">Total</label>
                            </div>
                            <div class="col-2 form-floating">
                                <select name="memory_total_unit" id="memory_total_unit" class="form-select form-control" disabled>
                                    @foreach (config('constants.SIZE_UNITS') as $idx => $val)
                                        <option value="{{ $idx }}" {{ $serverData->memory_total_size_type == $idx ? 'selected' : '' }}>{{ $val }}</option>
                                    @endforeach
                                </select>
                                <label for="memory_total_unit">Unit</label>
                            </div>
                        </div>
                        <div class="row g-1 pt-2">
                            <div class="col"  id="memory_size_section">
                                <div class="fw-semibold">
                                    &nbsp;Size
                                </div>
                                <div class="row g-0 pt-2 text-center">
                                    <div class="col-6 form-floating">
                                        <input name="memory_used" type="text" class="form-control" id="memory_used" placeholder="used" value="{{ old('memory_used', !empty($serverData) ? $serverData->memory_used_size : '') }}" disabled>
                                        <label for="memory_used">Used</label>
                                    </div>
                                    <div class="col-4 form-floating">
                                        <select name="memory_used_unit" id="memory_used_unit" class="form-select form-control" disabled>
                                            @foreach (config('constants.SIZE_UNITS') as $idx => $val)
                                                <option value="{{ $idx }}" {{ $serverData->memory_used_size_type == $idx ? 'selected' : '' }}>{{ $val }}</option>
                                            @endforeach
                                        </select>
                                        <label for="memory_used_unit">Unit</label>
                                    </div>
                                </div>
                                <div class="row g-0 pt-2 text-center">
                                    <div class="col-6 form-floating">
                                        <input name="memory_free" type="text" class="form-control" id="memory_free" placeholder="Free" value="{{ old('memory_free', !empty($serverData) ? $serverData->memory_free_size : '') }}" disabled>
                                        <label for="memory_free">Free</label>
                                    </div>
                                    <div class="col-4 form-floating">
                                        <select name="memory_free_unit" id="memory_free_unit" class="form-select form-control" disabled>
                                            @foreach (config('constants.SIZE_UNITS') as $idx => $val)
                                                <option value="{{ $idx }}" {{ $serverData->memory_free_size_type == $idx ? 'selected' : '' }}>{{ $val }}</option>
                                            @endforeach
                                        </select>
                                        <label for="memory_free_unit">Unit</label>
                                    </div>
                                </div>
                            </div>
                            <div class="col" id="memory_percentage_section">
                                <div class="fw-semibold">
                                    &nbsp;Percentage
                                </div>
                                <div class="row g-0 pt-2 text-center">
                                    <div class="col-6 form-floating">
                                        <input name="memory_used_percentage" type="text" class="form-control" id="memory_used_percentage" placeholder="used"  value="{{ $serverData->memory_used_percentage }}" disabled>
                                        <label for="memory_used_percentage">% Used</label>
                                    </div>
                                </div>
                                <div class="row g-0 pt-2 text-center">
                                    <div class="col-6 form-floating">
                                        <input name="memory_free_percentage" type="text" class="form-control" id="memory_free_percentage" placeholder="Free"  value="{{ $serverData->memory_free_percentage }}" disabled>
                                        <label for="memory_free_percentage">% Free</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 ps-2" id="cpu_usage_section">
                    <div class="group-category p-3 mb-4 pt-3 rounded-3">
                        <h4 class="subheader">CPU (%)</h4>&nbsp;&nbsp;  
                        <div class="status_group fs-6 fw-bold">
                            Status: <span id="cpu_status" class="{{ config('constants.STATUS_CLASS.' .$serverData->cpu_status) }}">{{ config('constants.STATUS_NAMES.' .$serverData->cpu_status) }}</span>
                        </div>
                        <div class="row g-1 pt-3">
                            <div class="col-lg-4 col-md-6 col-4" id="linux_usage">
                                <div class="fw-semibold">
                                    &nbsp;Linux
                                </div>
                                <div class="row g-0 pt-2 text-center">
                                    <div class="col-lg-8 col-md-6 col-8 form-floating">
                                        <input name="us" type="text" class="form-control" id="us" placeholder="us" value="{{ !empty($serverData->linux_us_percentage) ? $serverData->linux_us_percentage : '' }}" disabled>
                                        <label for="us">% us</label>
                                    </div>
                                </div>
                                <div class="row g-0 pt-2 text-center">
                                    <div class="col-lg-8 col-md-6 col-8 form-floating">
                                        <input name="ni" type="text" class="form-control" id="ni" placeholder="ni" value="{{ !empty($serverData->linux_ni_percentage) ? $serverData->linux_ni_percentage : '' }}" disabled>
                                        <label for="ni">% ni</label>
                                    </div>
                                </div>
                                <div class="row g-0 pt-2 text-center">
                                    <div class="col-lg-8 col-md-6 col-8 form-floating">
                                        <input name="sy" type="text" class="form-control" id="sy" placeholder="sy" value="{{ !empty($serverData->linux_sy_percentage) ? $serverData->linux_sy_percentage : '' }}" disabled>
                                        <label for="sy">% sy</label>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-4 col-md-6 col-4">
                                <div class="fw-semibold">
                                    &nbsp;Windows, Others
                                </div>
                                <div class="row g-0 pt-2 text-center">
                                    <div class="col-lg-8 col-md-6 col-8 form-floating">
                                        <input name="other_os_percentage" type="text" class="form-control"  value="{{ !empty($serverData->other_os_percentage) ? $serverData->other_os_percentage : '' }}" disabled>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </form>
</div>

@include('footer')

