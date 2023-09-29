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
                <td>
                    {{$typeCount}}. {{$type}}
                </td>

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
                            <td>
                                &nbsp;
                            </td>
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