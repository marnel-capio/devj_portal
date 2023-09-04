@include('header')
<script src="{{ asset(mix('js/server.min.js')) }}" defer></script>
<link rel="stylesheet" href="{{ asset(mix('css/server.min.css')) }}">
@include('headerMenu')
{{-- <script src="{{ asset('js/servers_dum.js') }}"></script>
<link rel="stylesheet" href="{{ asset('css/server.css') }}"> --}}
<div class="container ps-md-3 pe-md-3 pt-5">
    <h3 class="fw-bold d-inline-block">{{ $forUpdate ? 'Server Update' : 'Server Registration' }}</h3>
    &nbsp;&nbsp;
    <a href="{{ route('servers.help') }}" type="button" target="_blank" 
        data-bs-toggle="tooltip" 
        data-bs-placement="right" 
        data-bs-custom-class="custom-tooltip"
        data-bs-title="Guide in filling out the Capacity Monitoring Form">
        <h4 class="d-inline-block"><i class="bi bi-question-circle-fill text-primary"></i></h4>
    </a>
    <div class="pt-4">
        <form id="server_reg_form" action="{{ $forUpdate ? route('servers.store') : route('servers.regist') }}" method="POST">
            @csrf
            <input type="text" name="id" value="{{ !empty($serverData) ? $serverData->id : '' }}" hidden >
            <div class="group-category p-3 mb-4 rounded-3">
                <h4 class="fw-semi-bold">Server Details</h4>
                <div class="row mb-2 ps-5 pe-3">
                    <div class="col-lg-3 col-6 g-3 form-floating">
                       <input type="text" name="server_name" class="form-control" id="server_name" placeholder="Server Name" value="{{ old('server_name', !empty($serverData) ? $serverData->server_name : '') }}" required>
                       <label for="server_name" >Server Name</label>
                       @if ($errors->has('server_name'))
                       <p class="text-danger text-start">{{ $errors->first('server_name') }}</p>
                       @endif
                    </div>
                    <div class="col-6 g-3">
                        <div class="form-check input-box-radio">
                            <label class="form-check-label" for="server_status">Active Status</label>
                            <input type="checkBox" class="form-check-input" name="status" id="server_status" value="1" {{ old('status', !empty($serverData) ? $serverData->status : false) ? 'checked' : '' }}>
                        </div>
                    </div>
                </div>
                <div class="row mb-2 ps-5 pe-3">
                    <div class="col-lg-3 col-4 g-3 form-floating">
                        <input type="text" name="server_ip" class="form-control" id="server_ip" placeholder="IP Address" value="{{ old('server_ip', !empty($serverData) ? $serverData->server_ip : '') }}" required>
                        <label for="server_ip" >IP Address</label>
                        @if ($errors->has('server_ip'))
                        <p class="text-danger text-start">{{ $errors->first('server_ip') }}</p>
                        @endif
                     </div>
                     <div class="col-lg-3 col-4 g-3 form-floating">
                        <input type="text" name="os" class="form-control" id="os" placeholder="Operating System" value="{{ old('os', !empty($serverData) ? $serverData->os : '') }}" required>
                        <label for="os" >Operating System</label>
                        @if ($errors->has('os'))
                        <p class="text-danger text-start">{{ $errors->first('os') }}</p>
                        @endif
                     </div>
                     <div class="col-lg-3 col-4 g-3 form-floating">
                        <input type="text" name="motherboard" class="form-control" id="motherboard" placeholder="Motherboard" value="{{ old('motherboard', !empty($serverData) ? $serverData->motherboard : '') }}">
                        <label for="motherboard" >Motherboard</label>
                        @if ($errors->has('motherboard'))
                        <p class="text-danger text-start">{{ $errors->first('motherboard') }}</p>
                        @endif
                     </div>
                </div>
                <div class="row mb-2 ps-5 pe-3">
                    <div class="col-lg-3 col-4 g-3 form-floating">
                        <input type="text" name="cpu" class="form-control" id="cpu" placeholder="CPU" value="{{ old('cpu', !empty($serverData) ? $serverData->cpu : '') }}" required>
                        <label for="cpu" >Processor</label>
                        @if ($errors->has('cpu'))
                        <p class="text-danger text-start">{{ $errors->first('cpu') }}</p>
                        @endif
                    </div>
                    <div class="col-lg-3 col-4 g-3 form-floating">
                        <input type="text" name="memory" class="form-control" id="memory" placeholder="Memory" value="{{ old('memory', !empty($serverData) ? $serverData->memory : '') }}" required>
                        <label for="memory" >RAM</label>
                        @if ($errors->has('memory'))
                        <p class="text-danger text-start">{{ $errors->first('memory') }}</p>
                        @endif
                    </div>
                    <div class="col-lg-3 col-4 g-3 form-floating">
                        <input type="text" name="server_hdd" class="form-control" id="server_hdd" placeholder="HDD" value="{{ old('server_hdd', !empty($serverData) ? $serverData->hdd : '') }}" required>
                        <label for="server_hdd" >HDD</label>
                        @if ($errors->has('server_hdd'))
                        <p class="text-danger text-start">{{ $errors->first('server_hdd') }}</p>
                        @endif
                    </div>
                </div>
                <div class="row pt-4 ps-3 pe-3">
                    <h5>Function/Role</h5>
                </div>
                <div class="row mb-2 ps-5 pe-3">
                    <div class="col-lg-9 g-3">
                        <textarea class="form-control" name="function_role"  rows="3" id="function_role" required>{{ old('function_role', !empty($serverData) ? $serverData->function_role : '') }}</textarea>
                    </div>
                    @if ($errors->has('function_role'))
                    <p class="text-danger text-start">{{ $errors->first('function_role') }}</p>
                    @endif
                </div>
                <div class="row pt-4 ps-3 pe-3">
                    <h5>Remarks</h5>
                </div>
                <div class="row mb-2 ps-5 pe-3">
                    <div class="col-lg-9 g-3">
                        <textarea class="form-control" name="remarks"  rows="3" id="remarks">{{ old('remarks', !empty($serverData) ? $serverData->remarks : '') }}</textarea>
                    </div>
                    @if ($errors->has('remarks'))
                    <p class="text-danger text-start">{{ $errors->first('remarks') }}</p>
                    @endif
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
                                @php
                                    $hddStatus = !empty($serverData) ? $serverData->hdd_status : '';
                                @endphp
                                Status: <span id="hdd_status" class="{{ !empty(old('hdd_status', $hddStatus)) ? config('constants.STATUS_CLASS.' .old('hdd_status', $hddStatus)) : '' }}">{{ !empty(old('hdd_status', $hddStatus)) ? config('constants.STATUS_NAMES.' .old('hdd_status', $hddStatus)) : '' }}</span>
                                <input type="text" hidden name="hdd_status" value="{{ !empty(old('hdd_status', $hddStatus)) ? old('hdd_status', $hddStatus) : '' }}">
                                <input type="text" hidden name="partitions_count" value="{{ !empty(old('partitions_count')) ? old('partitions_count') : '' }}">
                            </div>
                        </div>
                        <div class="col text-end">
                            <button class="btn btn-primary text-end" id="add_partition">Add</button>
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
                                    <input type="text" hidden name="{{ 'hdd[' .$index .'][id]' }}" value="{{ $partition['id'] }}">
                                    <div class="hdd_partition p-1 pt-2">
                                        <div class="row p-2">
                                            <div class="col-md-8 col-9">
                                                <div class="row">
                                                    <label for="partition_1" class="col-auto fs-5 fw-bold align-baseline radio">Partition name:</label>
                                                    <div class="col-auto">
                                                    <input name="{{ 'hdd[' .$index .'][partition_name]' }}" type="text" class="form-control partition_name" id="{{ 'partition' .$index }}" value="{{ $partition['hdd_partition'] }}" required> 
                                                    </div>
                                                    @if ($errors->has('hdd.' .$index .'.partition_name'))
                                                    <p class="text-danger text-start">{{ $errors->first('hdd.' .$index .'.partition_name') }}</p>
                                                    @endif
                                                </div>
                                            </div>
                                            <div class="col-md-4 col-3 text-end">
                                                <button class="btn btn-danger remove_partition">Remove</button>
                                            </div>
                                        </div>
                                        <div class="p-2">
                                            <div class="row g-0 pt-1">
                                                <div class="col-3 form-floating">
                                                    <input name="{{ 'hdd[' .$index .'][total]' }}" type="text" class="form-control hdd_total" id="{{ 'hdd_total_' .$index }}" placeholder="total" value="{{ $partition['hdd_total'] }}" required>
                                                    <label for="{{ 'hdd_total_' .$index }}">Total</label>
                                                </div>
                                                <div class="col-2 form-floating">
                                                    <select name="{{ 'hdd[' .$index .'][total_unit]' }}" id="{{ 'hdd_total_unit_' .$index }}" class="form-select form-control hdd_total_unit" required>
                                                        @foreach (config('constants.SIZE_UNITS') as $idx => $val)
                                                            <option value="{{ $idx }}" {{ $partition['hdd_total_size_type'] == $idx ? 'selected' : '' }}>{{ $val }}</option>
                                                        @endforeach
                                                    </select>
                                                    <label for="{{ 'hdd_total_unit_' .$index }}">Unit</label>
                                                </div>
                                                @if ($errors->has('hdd.' .$index .'.total'))
                                                <p class="text-danger text-start">{{ $errors->first('hdd.' .$index .'.total') }}</p>
                                                @endif
                                                @if ($errors->has('hdd.' .$index .'.total_unit'))
                                                <p class="text-danger text-start">{{ $errors->first('hdd.' .$index .'.total_unit') }}</p>
                                                @endif
                                            </div>
                                            <div class="mt-2 pt-1 fw-semibold">
                                                Select mode of input:
                                            </div>
                                            <div class="row g-1 pt-2">
                                                <div class="col">
                                                    <div class="form-check form-check-inline">
                                                        <input type="radio" class="form-check-input hdd_select_radio hdd_size_radio" name="{{ 'hdd[' .$index .'][input_type]' }}" id="{{ 'hdd_size_radio_' .$index }}" value="1" checked>
                                                        <label class="form-check-label text-start" for="{{ 'hdd_size_radio_' .$index }}">Size</label>
                                                    </div>
                                                    <div class="row g-0 pt-2 text-center">
                                                        <div class="col-6 form-floating">
                                                            <input name="{{ 'hdd[' .$index .'][used]' }}" type="text" class="form-control hdd_used" id="{{ 'hdd_used_' .$index }}" placeholder="used" value="{{ $partition['hdd_used_size'] }}" required>
                                                            <label for="{{ 'hdd_used_' .$index }}">Used</label>
                                                        </div>
                                                        <div class="col-4 form-floating">
                                                            <select name="{{ 'hdd[' .$index .'][used_unit]' }}" id="{{ 'hdd_used_unit_' .$index }}" class="form-select form-control hdd_used_unit" required>
                                                                @foreach (config('constants.SIZE_UNITS') as $idx => $val)
                                                                    <option value="{{ $idx }}" {{ $partition['hdd_used_size_type'] == $idx ? 'selected' : '' }}>{{ $val }}</option>
                                                                @endforeach
                                                            </select>
                                                            <label for="{{ 'hdd_used_unit_' .$index }}">Unit</label>
                                                        </div>
                                                        
                                                        @if ($errors->has('hdd.' .$index .'.used'))
                                                        <p class="text-danger text-start">{{ $errors->first('hdd.' .$index .'.used') }}</p>
                                                        @endif
                                                        @if ($errors->has('hdd.' .$index .'.used_unit'))
                                                        <p class="text-danger text-start">{{ $errors->first('hdd.' .$index .'.used_unit') }}</p>
                                                        @endif
                                                    </div>
                                                    <div class="row g-0 pt-2 text-center">
                                                        <div class="col-6 form-floating">
                                                            <input name="{{ 'hdd[' .$index .'][free]' }}" type="text" class="form-control hdd_free" id="{{ 'hdd_free_' .$index }}" placeholder="Free" value="{{ $partition['hdd_free_size'] }}" required>
                                                            <label for="{{ 'hdd_free_' .$index }}">Free</label>
                                                        </div>
                                                        <div class="col-4 form-floating">
                                                            <select name="{{ 'hdd[' .$index .'][free_unit]' }}" id="{{ 'hdd_free_unit_' .$index }}" class="form-select form-control hdd_free_unit" required>
                                                                @foreach (config('constants.SIZE_UNITS') as $idx => $val)
                                                                    <option value="{{ $idx }}" {{ $partition['hdd_free_size_type'] == $idx ? 'selected' : '' }}>{{ $val }}</option>
                                                                @endforeach
                                                            </select>
                                                            <label for="{{ 'hdd_free_unit_' .$index }}">Unit</label>
                                                        </div>
                                                        @if ($errors->has('hdd.' .$index .'.free'))
                                                        <p class="text-danger text-start">{{ $errors->first('hdd.' .$index .'.free') }}</p>
                                                        @endif
                                                        @if ($errors->has('hdd.' .$index .'.free_unit'))
                                                        <p class="text-danger text-start">{{ $errors->first('hdd.' .$index .'.free_unit') }}</p>
                                                        @endif
                                                    </div>
                                                </div>
                                                <div class="col">
                                                    <div class="form-check form-check-inline">
                                                        <input type="radio" class="form-check-input hdd_select_radio hdd_percentage_radio" name="{{ 'hdd[' .$index .'][input_type]' }}" id="{{ 'hdd_percentage_radio_' .$index }}" value="2">
                                                        <label class="form-check-label text-start" for="{{ 'hdd_percentage_radio_' .$index }}">Percentage</label>
                                                    </div>
                                                    <div class="row g-0 pt-2 text-center">
                                                        <div class="col-6 form-floating">
                                                            <input name="{{ 'hdd[' .$index .'][used_percentage]' }}" type="text" class="form-control hdd_used_percentage" id="{{ 'hdd_used_percentage_' .$index }}" placeholder="used" disabled value="{{ $partition['hdd_used_percentage'] }}" required>
                                                            <label for="{{ 'hdd_used_percentage_' .$index }}">% Used</label>
                                                        </div>
                                                    </div>
                                                    <div class="row g-0 pt-2 text-center">
                                                        <div class="col-6 form-floating">
                                                            <input name="{{ 'hdd[' .$index .'][free_percentage]' }}" type="text" class="form-control hdd_free_percentage" id="{{ 'hdd_free_percentage_' .$index }}" placeholder="Free" disabled value="{{ $partition['hdd_free_percentage'] }}" required>
                                                            <label for="{{ 'hdd_free_percentage_' .$index }}">% Free</label>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        @else
                            @php
                                $partitionCount = old('partitions_count', 1);
                            @endphp
                            @for ( $index = 1 ; $index <= $partitionCount ; $index++ )
                                <div class="partition_section col-md-6" >
                                    <div class="hdd_partition p-1 pt-2">
                                        <div class="row p-2">
                                            <div class="col-md-8 col-9">
                                                <div class="row">
                                                    <label for="partition_1" class="col-auto fs-5 fw-bold align-baseline radio">Partition name:</label>
                                                    <div class="col-auto">
                                                    <input name="{{ 'hdd[' .$index .'][partition_name]' }}" type="text" class="form-control partition_name" id="{{ 'partition' .$index }}" value="{{ old('hdd.' .$index .'.partition_name') }}" required>
                                                    </div>
                                                    @if ($errors->has('hdd.' .$index .'.partition_name'))
                                                    <p class="text-danger text-start">{{ $errors->first('hdd.' .$index .'.partition_name') }}</p>
                                                    @endif
                                                </div>
                                            </div>
                                            <div class="col-md-4 col-3 text-end">
                                                <button class="btn btn-danger remove_partition">Remove</button>
                                            </div>
                                        </div>
                                        <div class="p-2">
                                            <div class="row g-0 pt-1">
                                                <div class="col-3 form-floating">
                                                    <input name="{{ 'hdd[' .$index .'][total]' }}" type="text" class="form-control hdd_total" id="{{ 'hdd_total_' .$index }}" placeholder="total" value="{{ old('hdd.' .$index .'.total') }}">
                                                    <label for="{{ 'hdd_total_' .$index }}">Total</label>
                                                </div>
                                                <div class="col-2 form-floating">
                                                    <select name="{{ 'hdd[' .$index .'][total_unit]' }}" id="{{ 'hdd_total_unit_' .$index }}" class="form-select form-control hdd_total_unit">
                                                        @foreach (config('constants.SIZE_UNITS') as $idx => $val)
                                                            <option value="{{ $idx }}" {{ old('hdd.' . $index .'.total_unit') == $idx ? 'selected' : '' }}>{{ $val }}</option>
                                                        @endforeach
                                                    </select>
                                                    <label for="{{ 'hdd_total_unit_' .$index }}">Unit</label>
                                                </div>
                                                @if ($errors->has('hdd.' .$index .'.total'))
                                                <p class="text-danger text-start">{{ $errors->first('hdd.' .$index .'.total') }}</p>
                                                @endif
                                                @if ($errors->has('hdd.' .$index .'.total_unit'))
                                                <p class="text-danger text-start">{{ $errors->first('hdd.' .$index .'.total_unit') }}</p>
                                                @endif
                                            </div>
                                            <div class="mt-2 pt-1 fw-semibold">
                                                Select mode of input:
                                            </div>
                                            <div class="row g-1 pt-2">
                                                <div class="col">
                                                    <div class="form-check form-check-inline">
                                                        <input type="radio" class="form-check-input hdd_select_radio hdd_size_radio" name="{{ 'hdd[' .$index .'][input_type]' }}" id="{{ 'hdd_size_radio_' .$index }}" value="1" {{ old('hdd.' .$index .'.input_type', 1) == 1 ? 'checked' : '' }}>
                                                        <label class="form-check-label text-start" for="{{ 'hdd_size_radio_' .$index }}">Size</label>
                                                    </div>
                                                    <div class="row g-0 pt-2 text-center">
                                                        <div class="col-6 form-floating">
                                                            <input name="{{ 'hdd[' .$index .'][used]' }}" type="text" class="form-control hdd_used" id="{{ 'hdd_used_' .$index }}" placeholder="used" value="{{ old('hdd.' .$index .'.used') }}" required>
                                                            <label for="{{ 'hdd_used_' .$index }}">Used</label>
                                                        </div>
                                                        <div class="col-4 form-floating">
                                                            <select name="{{ 'hdd[' .$index .'][used_unit]' }}" id="{{ 'hdd_used_unit_' .$index }}" class="form-select form-control hdd_used_unit" required>
                                                                @foreach (config('constants.SIZE_UNITS') as $idx => $val)
                                                                    <option value="{{ $idx }}" {{ old('hdd.' . $index .'.used_unit') == $idx ? 'selected' : '' }}>{{ $val }}</option>
                                                                @endforeach
                                                            </select>
                                                            <label for="{{ 'hdd_used_unit_' .$index }}">Unit</label>
                                                        </div>
                                                        @if ($errors->has('hdd.' .$index .'.used'))
                                                        <p class="text-danger text-start">{{ $errors->first('hdd.' .$index .'.used') }}</p>
                                                        @endif
                                                        @if ($errors->has('hdd.' .$index .'.used_unit'))
                                                        <p class="text-danger text-start">{{ $errors->first('hdd.' .$index .'.used_unit') }}</p>
                                                        @endif
                                                    </div>
                                                    <div class="row g-0 pt-2 text-center">
                                                        <div class="col-6 form-floating">
                                                            <input name="{{ 'hdd[' .$index .'][free]' }}" type="text" class="form-control hdd_free" id="{{ 'hdd_free_' .$index }}" placeholder="Free" value="{{ old('hdd.' .$index .'.free') }}" required>
                                                            <label for="{{ 'hdd_free_' .$index }}">Free</label>
                                                        </div>
                                                        <div class="col-4 form-floating">
                                                            <select name="{{ 'hdd[' .$index .'][free_unit]' }}" id="{{ 'hdd_free_unit_' .$index }}" class="form-select form-control hdd_free_unit" required>
                                                                @foreach (config('constants.SIZE_UNITS') as $idx => $val)
                                                                    <option value="{{ $idx }}" {{ old('hdd.' . $index .'.free_unit') == $idx ? 'selected' : '' }}>{{ $val }}</option>
                                                                @endforeach
                                                            </select>
                                                            <label for="{{ 'hdd_free_unit_' .$index }}">Unit</label>
                                                        </div>
                                                        @if ($errors->has('hdd.' .$index .'.free'))
                                                        <p class="text-danger text-start">{{ $errors->first('hdd.' .$index .'.free') }}</p>
                                                        @endif
                                                        @if ($errors->has('hdd.' .$index .'.free_unit'))
                                                        <p class="text-danger text-start">{{ $errors->first('hdd.' .$index .'.free_unit') }}</p>
                                                        @endif
                                                    </div>
                                                </div>
                                                <div class="col">
                                                    <div class="form-check form-check-inline">
                                                        <input type="radio" class="form-check-input hdd_select_radio hdd_percentage_radio" name="{{ 'hdd[' .$index .'][input_type]' }}" id="{{ 'hdd_percentage_radio_' .$index }}" value="2" {{ old('hdd.' .$index .'.input_type', 1) == 2 ? 'checked' : '' }}>
                                                        <label class="form-check-label text-start" for="{{ 'hdd_percentage_radio_' .$index }}">Percentage</label>
                                                    </div>
                                                    <div class="row g-0 pt-2 text-center">
                                                        <div class="col-6 form-floating">
                                                            <input name="{{ 'hdd[' .$index .'][used_percentage]' }}" type="text" class="form-control hdd_used_percentage" id="{{ 'hdd_used_percentage_' .$index }}" placeholder="used" disabled value="{{ old('hdd.' .$index .'.used_percentage') }}" required>
                                                            <label for="{{ 'hdd_used_percentage_' .$index }}">% Used</label>
                                                        </div>
                                                        @if ($errors->has('hdd.' .$index .'.used_percentage'))
                                                        <p class="text-danger text-start">{{ $errors->first('hdd.' .$index .'.used_percentage') }}</p>
                                                        @endif
                                                    </div>
                                                    <div class="row g-0 pt-2 text-center">
                                                        <div class="col-6 form-floating">
                                                            <input name="{{ 'hdd[' .$index .'][free_percentage]' }}" type="text" class="form-control hdd_free_percentage" id="{{ 'hdd_free_percentage_' .$index }}" placeholder="Free" disabled value="{{ old('hdd.' .$index .'.free_percentage') }}" required>
                                                            <label for="{{ 'hdd_free_percentage_' .$index }}">% Free</label>
                                                        </div>
                                                        @if ($errors->has('hdd.' .$index .'.free_percentage'))
                                                        <p class="text-danger text-start">{{ $errors->first('hdd.' .$index .'.free_percentage') }}</p>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endfor
                        @endif
                    </div>
                </div>

{{-- ==================================== Memory Usage and CPU Usage ==================================== --}}

                <div class="row" id="memory_usage_section">
                    <div class="col-md-6 ps-2">
                        <div class="group-category p-3 mb-4 pt-3 rounded-3">
                            <h4 class="subheader">Memory</h4>&nbsp;&nbsp;  
                            <div class="status_group fs-6 fw-bold">
                                @php
                                    $ramStatus = !empty($serverData) ? $serverData->ram_status : '';
                                @endphp
                                Status: <span id="memory_status" class="{{ !empty(old('memory_status', $ramStatus)) ? config('constants.STATUS_CLASS.' .old('memory_status', $ramStatus)) : '' }}">{{ !empty(old('memory_status', $ramStatus)) ? config('constants.STATUS_NAMES.' .old('memory_status', $ramStatus)) : '' }}</span>
                                <input type="text" hidden name="memory_status" value="{{ !empty(old('memory_status', $ramStatus)) ? old('memory_status', $ramStatus) : '' }}">
                            </div>
                            <div class="row g-0 pt-3">
                                <div class="col-3 form-floating">
                                    <input name="memory_total" type="text" class="form-control" id="memory_total" placeholder="total" value="{{ old('memory_total', !empty($serverData) ? $serverData->memory_total : '') }}" required>
                                    <label for="memory_total">Total</label>
                                </div>
                                <div class="col-2 form-floating">
                                    <select name="memory_total_unit" id="memory_total_unit" class="form-select form-control" required>
                                        @foreach (config('constants.SIZE_UNITS') as $idx => $val)
                                            <option value="{{ $idx }}" {{ old('memory_total_unit', !empty($serverData) ? $serverData->memory_total_size_type : 1) == $idx ? 'selected' : '' }}>{{ $val }}</option>
                                        @endforeach
                                    </select>
                                    <label for="memory_total_unit">Unit</label>
                                </div>
                                @if ($errors->has('memory_total'))
                                <p class="text-danger text-start">{{ $errors->first('memory_total') }}</p>
                                @endif
                                @if ($errors->has('memory_total_unit'))
                                <p class="text-danger text-start">{{ $errors->first('memory_total_unit') }}</p>
                                @endif
                            </div>
                            <div class="mt-2 pt-1 fw-semibold">
                                Select mode of input:
                            </div>
                            <div class="row g-1 pt-2">
                                <div class="col"  id="memory_size_section">
                                    <div class="form-check form-check-inline">
                                        <input type="radio" class="form-check-input" name="memory_input_type" id="memory_size_radio" value="1" {{ old('memory_input_type', 1) == 1 ? 'checked' : '' }}>
                                        <label class="form-check-label text-start" for="memory_size_radio">Size</label>
                                    </div>
                                    <div class="row g-0 pt-2 text-center">
                                        <div class="col-6 form-floating">
                                            <input name="memory_used" type="text" class="form-control" id="memory_used" placeholder="used" value="{{ old('memory_used', !empty($serverData) ? $serverData->memory_used_size : '') }}" required>
                                            <label for="memory_used">Used</label>
                                        </div>
                                        <div class="col-4 form-floating">
                                            <select name="memory_used_unit" id="memory_used_unit" class="form-select form-control" required>
                                                @foreach (config('constants.SIZE_UNITS') as $idx => $val)
                                                    <option value="{{ $idx }}" {{ old('memory_used_unit', !empty($serverData) ? $serverData->memory_used_size_type : 1) == $idx ? 'selected' : '' }}>{{ $val }}</option>
                                                @endforeach
                                            </select>
                                            <label for="memory_used_unit">Unit</label>
                                        </div>
                                        @if ($errors->has('memory_used'))
                                        <p class="text-danger text-start">{{ $errors->first('memory_used') }}</p>
                                        @endif
                                        @if ($errors->has('memory_used_unit'))
                                        <p class="text-danger text-start">{{ $errors->first('memory_used_unit') }}</p>
                                        @endif
                                    </div>
                                    <div class="row g-0 pt-2 text-center">
                                        <div class="col-6 form-floating">
                                            <input name="memory_free" type="text" class="form-control" id="memory_free" placeholder="Free" value="{{ old('memory_free', !empty($serverData) ? $serverData->memory_free_size : '') }}" required>
                                            <label for="memory_free">Free</label>
                                        </div>
                                        <div class="col-4 form-floating">
                                            <select name="memory_free_unit" id="memory_free_unit" class="form-select form-control" required>
                                                @foreach (config('constants.SIZE_UNITS') as $idx => $val)
                                                    <option value="{{ $idx }}" {{ old('memory_free_unit', !empty($serverData) ? $serverData->memory_free_size_type : 1) == $idx ? 'selected' : '' }}>{{ $val }}</option>
                                                @endforeach
                                            </select>
                                            <label for="memory_free_unit">Unit</label>
                                        </div>
                                        @if ($errors->has('memory_free'))
                                        <p class="text-danger text-start">{{ $errors->first('memory_free') }}</p>
                                        @endif
                                        @if ($errors->has('memory_free_unit'))
                                        <p class="text-danger text-start">{{ $errors->first('memory_free_unit') }}</p>
                                        @endif
                                    </div>
                                </div>
                                <div class="col" id="memory_percentage_section">
                                    <div class="form-check form-check-inline">
                                        <input type="radio" class="form-check-input" name="memory_input_type" id="memory_percentage_radio" value="2" {{ old('memory_input_type') == 2 ? 'checked' : '' }}>
                                        <label class="form-check-label text-start" for="memory_percentage_radio">Percentage</label>
                                    </div>
                                    <div class="row g-0 pt-2 text-center">
                                        <div class="col-6 form-floating">
                                            <input name="memory_used_percentage" type="text" class="form-control" id="memory_used_percentage" placeholder="used" disabled value="{{ old('memory_used_percentage', !empty($serverData) ? $serverData->memory_used_percentage : '') }}" required>
                                            <label for="memory_used_percentage">% Used</label>
                                        </div>
                                        @if ($errors->has('memory_used_percentage'))
                                        <p class="text-danger text-start">{{ $errors->first('memory_used_percentage') }}</p>
                                        @endif
                                    </div>
                                    <div class="row g-0 pt-2 text-center">
                                        <div class="col-6 form-floating">
                                            <input name="memory_free_percentage" type="text" class="form-control" id="memory_free_percentage" placeholder="Free" disabled value="{{ old('memory_free_percentage', !empty($serverData) ? $serverData->memory_free_percentage : '') }}" required>
                                            <label for="memory_free_percentage">% Free</label>
                                        </div>
                                        @if ($errors->has('memory_free_percentage'))
                                        <p class="text-danger text-start">{{ $errors->first('memory_free_percentage') }}</p>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 ps-2" id="cpu_usage_section">
                        <div class="group-category p-3 mb-4 pt-3 rounded-3">
                            <h4 class="subheader">CPU (%)</h4>&nbsp;&nbsp;  
                            <div class="status_group fs-6 fw-bold">
                                @php
                                    $cpuStatus = !empty($serverData) ? $serverData->cpu_status : '';
                                @endphp
                                Status: <span id="cpu_status" class="{{ !empty(old('cpu_status', $cpuStatus)) ? config('constants.STATUS_CLASS.' .old('cpu_status', $cpuStatus)) : '' }}">{{ !empty(old('cpu_status', $cpuStatus)) ? config('constants.STATUS_NAMES.' .old('cpu_status', $cpuStatus)) : '' }}</span>
                                <input type="text" hidden name="cpu_status" value="{{ !empty(old('cpu_status', $cpuStatus)) ? old('cpu_status', $cpuStatus) : '' }}">
                            </div>
                            <div class="mt-2 pt-1 fw-semibold">
                                Select the Operating System:
                            </div>
                            <div class="row g-1 pt-3">
                                @if ($errors->has('os_type'))
                                <p class="text-danger text-start">{{ $errors->first('os_type') }}</p>
                                @endif
                                <div class="col-lg-4 col-md-6 col-4" id="linux_usage">
                                    <div class="form-check form-check-inline">
                                        <input type="radio" class="form-check-input" name="os_type" id="linux_radio" value="1" {{ old('os_type', !empty($serverData) ? $serverData->os_type : 1) == 1 ? 'checked' : '' }}>
                                        <label class="form-check-label text-start" for="linux_radio">Linux</label>
                                    </div>
                                    <div class="row g-0 pt-2 text-center">
                                        <div class="col-lg-8 col-md-6 col-8 form-floating">
                                            <input name="us" type="text" class="form-control" id="us" placeholder="us" value="{{ old('us', !empty($serverData) ? $serverData->linux_us_percentage : '') }}" required>
                                            <label for="us">% us</label>
                                        </div>
                                        @if ($errors->has('us'))
                                        <p class="text-danger text-start">{{ $errors->first('us') }}</p>
                                        @endif
                                    </div>
                                    <div class="row g-0 pt-2 text-center">
                                        <div class="col-lg-8 col-md-6 col-8 form-floating">
                                            <input name="ni" type="text" class="form-control" id="ni" placeholder="ni" value="{{ old('ni', !empty($serverData) ? $serverData->linux_ni_percentage : '') }}" required>
                                            <label for="ni">% ni</label>
                                        </div>
                                        @if ($errors->has('ni'))
                                        <p class="text-danger text-start">{{ $errors->first('ni') }}</p>
                                        @endif
                                    </div>
                                    <div class="row g-0 pt-2 text-center">
                                        <div class="col-lg-8 col-md-6 col-8 form-floating">
                                            <input name="sy" type="text" class="form-control" id="sy" placeholder="sy" value="{{ old('sy', !empty($serverData) ? $serverData->linux_sy_percentage : '') }}" required>
                                            <label for="sy">% sy</label>
                                        </div>
                                        @if ($errors->has('sy'))
                                        <p class="text-danger text-start">{{ $errors->first('sy') }}</p>
                                        @endif
                                    </div>
                                </div>
                                <div class="col-lg-4 col-md-6 col-4">
                                    <div class="form-check form-check-inline">
                                        <input type="radio" class="form-check-input" name="os_type" id="other_os_radio" value="2" {{ old('os_type', !empty($serverData) ? $serverData->os_type : 1) == 2 ? 'checked' : '' }}>
                                        <label class="form-check-label text-start" for="other_os_radio">Windows, Others</label>
                                    </div>
                                    <div class="row g-0 pt-2 text-center">
                                        <div class="col-lg-8 col-md-6 col-8 form-floating">
                                            <input name="other_os_percentage" type="text" class="form-control" disabled value="{{ old('other_os_percentage', !empty($serverData) ? $serverData->other_os_percentage : '') }}" required>
                                        </div>
                                        @if ($errors->has('other_os_percentage'))
                                        <p class="text-danger text-start">{{ $errors->first('other_os_percentage') }}</p>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
            <div class="text-center p-3">
                <button class="btn btn-primary btn-lg mb-5" id="server-reg-submit" type="submit">Submit</button>
            </div>
        </form>
    </div>
</div>

@include('footer')

