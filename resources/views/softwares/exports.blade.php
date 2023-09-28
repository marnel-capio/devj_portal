<html>
<head>
    <link rel="stylesheet" href="{{ asset(mix('css/software_export.min.css')) }}">
</head>
<div>
    <p>{{$lastApproved}}</p>
</div>
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
        @endphp
        @foreach ($softwareList as $type => $names)
            @php
                $countRow = count($names) + 1;
            @endphp
            <tr>
                <td rowspan="{{$countRow}}">
                    {{$typeCount}}. {{$type}}
                </td>
            </tr>
            @foreach ($names as $softwareDetails => $details)
            <tr>
                <td>
                    {{$details['name']}}
                </td>
                <td>
                    {{$details['remarks']}}
                </td>
            </tr>
            @endforeach

            @php
                $typeCount = $typeCount+1;
            @endphp
        @endforeach
    </tbody>
</table>
</html>