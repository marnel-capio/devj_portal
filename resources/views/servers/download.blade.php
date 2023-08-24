<table class="table table-bordered">
    <thead>
        <tr></tr>
        <tr></tr>
        <tr></tr>
        <tr>
            <th></th>
            <th rowspan="4">Server Name /IP</th>
            <th rowspan="4">Function/Role</th>
            <th rowspan="4">Specification</th>
            <th colspan="6">HDD Utilization (GB/%)</th>
            <th colspan="5">Memory Utilization (GB/%)</th>
            <th colspan="4" rowspan="2">CPU Utilization (%)</th>
            <th rowspan="4">Updated By:</th>
            <th rowspan="3" colspan="3">Status</th>
            <th rowspan="4">Remarks</th>
            <th rowspan="4">Reviewer Name and Comments</th>
        </tr>
        <tr>
            <th></th>
            <th rowspan="3">Partition</th>
            <th colspan="2" rowspan="2">Used</th>
            <th colspan="2" rowspan="2">Free</th>
            <th rowspan="3">Total</th>
            <th colspan="2" rowspan="2">Used</th>
            <th colspan="2" rowspan="2">Free</th>
            <th rowspan="3">Total</th>
        </tr>
        <tr>
            <th></th>
            <th rowspan="2">Windows</th>
            <th colspan="3">Linux</th>
        </tr>
        <tr>
            <th></th>
            <th>Used Size</th>
            <th>% Used</th>
            <th>Free Size</th>
            <th>% Free</th>
            <th>Used Size</th>
            <th>% Used</th>
            <th>Free Size</th>
            <th>% Free</th>
            <th>% us</th>
            <th>% ni</th>
            <th>% sy</th>
            <th>HDD</th>
            <th>RAM</th>
            <th>CPU</th>
        </tr>
    </thead>
    <tbody>
            @foreach ($serverData as $id => $server)
            @php
                $pCount = count($server);
            @endphp
            @for ( $i = 0 ; $i < $pCount ; $i++ )
                @if ($i == 0)
                    {{-- set first row --}}
                    <tr>
                        <td></td>
                        <td rowspan="{{ $pCount }}">
                            ■{{ $server[$i]['server_name'] }} <br> 
                            ■{{ $server[$i]['server_ip'] }}
                        </td>
                        <td rowspan="{{ $pCount }}">{!! nl2br(e($server[$i]['function_role'])) !!}</td>
                        <td rowspan="{{ $pCount }}">
                            ■OS: {{ $server[$i]['os'] }} <br>
                            ■CPU: {{ $server[$i]['cpu'] }} <br>
                            ■Motherboard: {{ $server[$i]['motherboard'] }} <br>
                            ■Memory: {{ $server[$i]['memory'] }} <br>
                            ■HDD: {{ $server[$i]['hdd'] }}
                        </td>
                        <td>{{ $server[$i]['hdd_partition'] }}</td>
                        <td>{{ $server[$i]['hdd_used_size'] }} GB</td>
                        <td>{{ $server[$i]['hdd_used_percentage'] }}%</td>
                        <td>{{ $server[$i]['hdd_free_size'] }} GB</td>
                        <td>{{ $server[$i]['hdd_free_percentage'] }}%</td>
                        <td>{{ $server[$i]['hdd_total'] }} GB</td>
                        <td rowspan="{{ $pCount }}">{{ $server[$i]['memory_used_size'] }} GB</td>
                        <td rowspan="{{ $pCount }}">{{ $server[$i]['memory_used_percentage'] }}%</td>
                        <td rowspan="{{ $pCount }}">{{ $server[$i]['memory_free_size'] }} GB</td>
                        <td rowspan="{{ $pCount }}">{{ $server[$i]['memory_free_percentage'] }}%</td>
                        <td rowspan="{{ $pCount }}">{{ $server[$i]['memory_total'] }} GB</td>
                        <td rowspan="{{ $pCount }}">{{ !empty($server[$i]['other_os_percentage']) ? $server[$i]['other_os_percentage'] .'%' : 'N/A'}}</td>
                        <td rowspan="{{ $pCount }}">{{ !empty($server[$i]['linux_us_percentage']) ? $server[$i]['linux_us_percentage'] .'%' : 'N/A'}}</td>
                        <td rowspan="{{ $pCount }}">{{ !empty($server[$i]['linux_ni_percentage']) ? $server[$i]['linux_ni_percentage'] .'%' : 'N/A'}}</td>
                        <td rowspan="{{ $pCount }}">{{ !empty($server[$i]['linux_sy_percentage']) ? $server[$i]['linux_sy_percentage'] .'%' : 'N/A'}}</td>
                        <td rowspan="{{ $pCount }}">{{ $server[$i]['updater'] }}</td>
                        <td rowspan="{{ $pCount }}">{{ config('constants.STATUS_NAMES.' .$server[$i]['hdd_status']) }}</td>
                        <td rowspan="{{ $pCount }}">{{ config('constants.STATUS_NAMES.' .$server[$i]['ram_status']) }}</td>
                        <td rowspan="{{ $pCount }}">{{ config('constants.STATUS_NAMES.' .$server[$i]['cpu_status']) }}</td>
                        <td rowspan="{{ $pCount }}">{{ $server[$i]['remarks'] }}</td>
                        <td rowspan="{{ $pCount }}"></td>
                    </tr>
                @else
                    {{-- set other hdd partition data --}}
                    <tr>
                        <td></td>
                        <td>{{ $server[$i]['hdd_partition'] }}</td>
                        <td>{{ $server[$i]['hdd_used_size'] }} GB</td>
                        <td>{{ $server[$i]['hdd_used_percentage'] }}%</td>
                        <td>{{ $server[$i]['hdd_free_size'] }} GB</td>
                        <td>{{ $server[$i]['hdd_free_percentage'] }}%</td>
                        <td>{{ $server[$i]['hdd_total'] }} GB</td>
                    </tr>
                @endif
            @endfor       
        @endforeach
        <tr></tr>
        <tr></tr>
        <tr></tr>
        <tr><td></td><td>Status legend:</td></tr>
        <tr><td></td><td>■ … Normal 0% - 60%</td></tr>
        <tr><td></td><td>■ … Stable 61% - 89%</td></tr>
        <tr><td></td><td>■ … Critical 90% - 100%</td></tr>
    </tbody>
</table>
