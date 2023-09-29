<html>
<head>
    <link rel="stylesheet" href="{{ asset(mix('css/software_export.min.css')) }}">
</head>
<div>
    <p>{{$lastApproved}}</p>
</div>
<br/>
<table width="100%">
    <thead>
    <tr>
        <th >Type</th>
        <th >Application Software</th>
        <th >Remarks</th>
    </tr>
    </thead>
    <tbody>
        @php
            $typeCount = 1;
            $ctrName = 1;
        @endphp
        @foreach ($softwareList as $type => $names)
            @php
                $countRow = count($names);
            @endphp
            <tr>
                @if (auth()->user()->roles != config('constants.MANAGER_ROLE_VALUE')) 
                    <td>
                        {{$typeCount}}. {{$type}}
                    </td>
                @else
                    <td rowspan="{{$countRow}}">
                        {{$typeCount}}. {{$type}}
                    </td>
                @endif
                

                @foreach ($names as $softwareDetails => $details)
                    @if ($ctrName == 1)
                            <td>
                                {{$details['name']}}
                            </td>
                            <td>
                                {{$details['remarks']}}
                            </td>
                        </tr>
                    @else
                        <tr>
                            @if (auth()->user()->roles != config('constants.MANAGER_ROLE_VALUE'))
                                <td>
                                    &nbsp;
                                </td>
                            @endif
                            <td>
                                {{$details['name']}}
                            </td>
                            <td>
                                {{$details['remarks']}}
                            </td>
                        </tr>
                    @endif
                    @php
                        $ctrName = $ctrName+1;
                    @endphp
                @endforeach
            @php
                $typeCount = $typeCount+1;
                $ctrName = 1;
            @endphp
        @endforeach
    </tbody>
</table>
</html>