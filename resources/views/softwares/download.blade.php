<table>
    <thead>
        <tr>
            <th rowspan="1">Type</th>
            <th rowspan="1">Application Software</th>
            <th rowspan="1">Remarks</th>

        </tr>
    </thead>
    <tbody>
        @foreach ($detail as $item)
            <tr>
                <td>{{ config('constants.SOFTWARE_TYPE_' . strVal($item['type']) . '_NAME') }}</td>
                <td>{{ $item['software_name'] }}</td>
                <td>{{ $item['remarks'] }}</td>                
            </tr>
        @endforeach
    </tbody>
</table>
