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
            <tr>
                <td>{{ $item['type'] }}</td>
                <td>{{ $item['software_name'] }}</td>
                <td>{{ $item['remarks'] }}</td>                
            </tr>
        @endforeach
    </tbody>
</table>
