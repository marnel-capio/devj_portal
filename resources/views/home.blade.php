@include('header')

@include('headerMenu')
<div class="container container-req-table  mt-3 ms-4 mb-5">
  @if(auth()->user()->roles == 2)

	<div class="row-req-table row">
	    <div class="col">
	      <h3 class="mb-4"> Employee Request </h3>
			<table id="employee-request" class="table table-striped request-table" >
		        <thead>
		            <tr>
		                <th class="tbl-header-name">Name</th>
		                <th>Email Address</th>
		                <th>Position</th>
		                <th>Actions</th>
		            </tr>
		        </thead>
		        <tbody>
		        	@foreach ($employee_request as $user)
		            <tr>
		                <td>{{$user['last_name']}}, {{$user['first_name']}}</td>
		                <td>{{$user['email']}}</td>
		                <td>
		                @if ($user['position'] == 1) 
		                	{{config('constants.POSITION_1_NAME')}}
		                @elseif ($user['position'] == 2) 
		                	{{config('constants.POSITION_2_NAME')}}
		                @elseif ($user['position'] == 3) 
		                	{{config('constants.POSITION_3_NAME')}}
		                @elseif ($user['position'] == 4) 
		                	{{config('constants.POSITION_4_NAME')}}
		                @elseif ($user['position'] == 5) 
		                	{{config('constants.POSITION_5_NAME')}}
		                @elseif ($user['position'] == 6) 
		                	{{config('constants.POSITION_6_NAME')}}
		                @elseif ($user['position'] == 7) 
		                	{{config('constants.POSITION_7_NAME')}}
		                @elseif ($user['position'] == 8) 
		                	{{config('constants.POSITION_8_NAME')}}
		                @elseif ($user['position'] == 9) 
		                	{{config('constants.POSITION_9_NAME')}}
		                @else
		                	-
		                @endif
						</td>
		                <td>	
		                	<a href="{{ url("/employees/{$user['id']}/request") }}" alt="View"><i class="bi bi-eye"></i>View</a>
		                </td>
		            </tr>
		            @endforeach
		        </tbody>
		    </table>
	    </div>
	@endif
	{{-- This section is for Software list --}}
	<div class="row-req-table row group-category-home p-2"> 
		<div class="col">
		  <h3 class="mb-4"> Software Request </h3>
			<table id="softwares-request" class="table table-striped request-table" >
				<thead>
					<tr>
						<th style="width:18%">Software Name</th>
						<th style="width:18%">Type</th>
						<th style="width:18%">Status</th>
						<th style="width:12%">Purpose</th>
					</tr>
				</thead>
				<tbody>
					@foreach ($softwareRequest as $request)
						<tr>
							<td>{{ $request['software_name'] }}</td>
							<td>{{ $request['type'] }}</td>
							<td>{{ $request['approved_status'] }}</td>	
							<td>{{ $request['remarks'] }}</td>
							<td><a href="{{ route('softwares.request', ['id' => $request['id']]) }}"><i class="bi bi-eye"></i>View</a></td>
						</tr>
					@endforeach
				</tbody>
			</table>
		</div>
	</div>
</div>


@include('footer')