<table>
    <thead>
        <tr ></tr>
        <tr >
            <th></th>
            <th> {{ $detail_note}} </th>
        </tr>
        <tr></tr>
        <tr>
            <th rowspan="1">Type</th>
            <th rowspan="1">Application Software</th>
            <th rowspan="1">Purpose</th>

        </tr>
    </thead>
    <tbody>
        @foreach ($detail as $item)
            @if($file_type !== config('constants.FILE_TYPE_PDF'))
                <tr>
                    <td>{{ $item['type'] }}</td>
                    <td>{{ $item['software_name'] }}</td>
                    <td>{{ $item['remarks'] }}</td>                
                </tr>
            @else
                <tr>
                    <td>{{ wordwrap($item['type'], 25, "\n", true) }}</td>
                    <td>{{ wordwrap($item['software_name'], 25, "\n", true) }}</td>
                    <td>{{ wordwrap($item['remarks'], 50, "\n", true)}}</td>                
                </tr>
            @endif
        @endforeach
    </tbody>
</table>
