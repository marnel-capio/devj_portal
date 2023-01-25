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
                @if($item['type'] == 1)
                    <td>Productivity Tools</td>
                @elseif($item['type'] == 2)
                    <td>Messaging/Collaboration</td>
                @elseif($item['type'] == 3)
                    <td>Browser</td>                     
                @elseif($item['type'] == 4)
                    <td>System Utilities</td>
                @elseif($item['type'] == 5)
                    <td>Project Specific Softwares</td>                     
                @elseif($item['type'] == 6)
                    <td>Phone Drivers</td>                      
                @endif 
                <td>{{ $item['software_name'] }}</td>
                <td>{{ $item['remarks'] }}</td>
            </tr>
        @endforeach
    </tbody>
</table>
