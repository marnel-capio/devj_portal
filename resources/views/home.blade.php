@include('header')

@include('headerMenu')
<div class="container container-req-table  mt-4 ms-4 mb-4">
  @if(auth()->user()->roles == 2)

	<div class="row-req-table row group-category-home p-2">
	    <div class="col table-avoid-overflow">
	      <h3 class="mb-4"> Employee Request </h3>
			<table id="employee-request" class="table table-striped request-table" >
		        <thead>
		            <tr>
		                <th>Name</th>
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
	</div>
	@endif
	<div class="row-req-table row group-category-home p-2"> 
		<div class="col table-avoid-overflow">
		  <h3 class="mb-4"> Laptop Request </h3>
			<table id="laptop-request" class="table table-striped request-table" >
				<thead>
					<tr>
						<th style="width:18%">Tag Number</th>
						<th style="width:18%">PEZA Form Number</th>
						<th style="width:18%">PEZA Permit Number</th>
						<th style="width:12%">Make</th>
						<th style="width:12%">Model</th>
						<th style="width:11%">Status</th>
						<th style="width:11%">Action</th>
					</tr>
				</thead>
				<tbody>
					@foreach ($laptopRequest as $request)
						<tr>
							<td>{{ $request['tag_number'] }}</a></td>
							<td>{{ $request['peza_form_number'] }}</td>
							<td>{{ $request['peza_permit_number'] }}</td>
							<td>{{ $request['laptop_make'] }}</td>
							<td>{{ $request['laptop_model'] }}</td>
							<td>{{ $request['status'] }}</td>
							<td><a href="{{ route('laptops.request', ['id' => $request['id']]) }}"><i class="bi bi-eye"></i>View</a></td>
						</tr>
					@endforeach
				</tbody>
			</table>
		</div>
	</div>
	<div class="row-req-table row group-category-home p-2">
		<div class="col table-avoid-overflow">
		  <h3 class="mb-4"> Laptop Link Request </h3>
			<table id="laptop-link-request" class="table table-striped request-table" >
				<thead>
					<tr>
						<th style="width:26%">Employee Name</th>
						<th style="width:21%">Tag Number</th>
						<th style="width:21%">Make</th>
						<th style="width:21%">Model</th>
						<th style="width:11%">Action</th>
					</tr>
				</thead>
				<tbody>
					@foreach ($laptopLinkRequest as $request)
						<tr>
							<td>{{ $request['employee_name'] }}</a></td>
							<td>{{ $request['tag_number'] }}</a></td>
							<td>{{ $request['laptop_make'] }}</td>
							<td>{{ $request['laptop_model'] }}</td>
							<td><a href="{{ route('laptops.details', ['id' => $request['laptop_id']]) ."#link-req-tbl" }}"><i class="bi bi-eye"></i>View</a></td>
						</tr>
					@endforeach
				</tbody>
			</table>
		</div>
	</div>
	{{-- This section is for Software list --}}
	<div class="row-req-table row group-category-home p-2"> 
		<div class="col table-avoid-overflow">
			<h3 class="mb-4"> Software Request </h3>
			<table id="software-request" class="table table-striped request-table" >
				<thead>
					<tr>
						<th style="width:23%">Software Name</th>
						<th style="width:23%">Type</th>
						<th style="width:29%">Purpose</th>
						<th style="width:16%">Action</th>
					</tr>
				</thead>
				<tbody>
					@foreach ($softwareRequest as $software)
						<tr>
							<td>{{ $software['software_name'] }}</td>
							<td>{{ $software['type'] }}</td>
							<td>{{ $software['remarks'] }}</td>
							<td><a href="{{ route('softwares.request', ['id' => $software['id']]) }}"><i class="bi bi-eye"></i>View</a></td>
						</tr>
					@endforeach
				</tbody>
			</table>
		</div>
	</div>	
	{{-- This section is for Project Link list --}}
	<div class="row-req-table row group-category-home p-2"> 
		<div class="col table-avoid-overflow">
			<h3 class="mb-4"> Project Link Request </h3>
			<table id="project-link-request" class="table table-striped request-table" >
				<thead>
					<tr>
						<th style="width:30%">Project Name</th>
						<th style="width:30%">Linked Employee</th>
						<th style="width:14%">Start Date</th>
						<th style="width:14%">End Date</th>
						<th style="width:12%">Action</th>
					</tr>
				</thead>
				<tbody>
					@foreach ($projectLinkRequest as $projectlink)
						<tr>
							<td>{{ $projectlink['project_name'] }}</td>
							<td>{{ $projectlink['linked_employee'] }}</td>
							<td>{{date("Y-m-d", strtotime($projectlink['start_date']) )}}</td>
							@if(strlen( $projectlink['end_date'] ) !== 0)
								<td>{{date("Y-m-d", strtotime($projectlink['end_date']) )}}</td>
							@else
								<td>{{'-'}}</td>
							@endif
							<td><a href="{{ route('projects.details', ['id' => $projectlink['project_id']]) ."#link_request_tbl" }}"><i class="bi bi-eye"></i>View</a></td>
						</tr>
					@endforeach
				</tbody>
			</table>
		</div>
	</div>
</div>



@include('footer')