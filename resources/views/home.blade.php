@include('header')

@include('headerMenu')

<div class="container container-req-table">
	<div class="row-req-table row">
		<div class="container">
			<div class="row dash-head ">
				{{-- Contains Urgent / Latest notifications --}}
				<div class="group-category-home dash-notification-container col col-12 col-sm-12 col-lg-7 py-2 px-4 rounded">
					<div class="row mb-3">
						<div class="col col-12  col-md-12 col-lg-7">
							<h3> Welcome, {{ auth()->user()->first_name }}</h3>
						</div>
						<div class="col col-12  col-md-12 col-lg-5">
							<span> Today is {{ $date }} </span>
						</div>
					</div>
					<!--div>
						<h4 class="mt-1">Notifications</h4>
					</div-->
					<div class="dash-notifications">
						@if($user["passport_isWarning"])
							<div class="alert alert-danger" role="alert">
						@else
							<div class="alert alert-info" role="alert">
						@endif
						@if($user['passport_status'] == 3)
								Please set a passport appointment!
						@elseif($user['passport_status'] == 2)
								Your passport appointment is in: {{$user['duration']}}
						@else
								Your passport expires in: {{$user['duration']}}
								@if($user["passport_isWarning"])
								<br> Please update your passport immediately.
								@endif
						@endif
						</div>

						@if(isset($softwareRejectedCount)  && !empty($softwareRejectedCount))
							<div class="alert alert-danger" role="alert">
								Your software registration/update is rejected. Please see the details.
							</div>
						@endif

						@if(isset($laptopRejectedCount)  && !empty($laptopRejectedCount))
							<div class="alert alert-danger" role="alert">
								Your laptop registration/update is rejected. Please see the details.
							</div>
						@endif

						@if(isset($laptopLinkRejectedCount)  && !empty($laptopLinkRejectedCount))
							<div class="alert alert-danger" role="alert">
								Your laptop link registration/update is rejected. Please see the details or <a href="{{ route('laptops.clearRejectedLinkage') }}">clear</a> the rejected data
							</div>
						@endif

						@if(isset($projectLinkRejectedCount)  && !empty($projectLinkRejectedCount))
							<div class="alert alert-danger" role="alert">
								Your project link registration/update is rejected. Please see the details or <a href="{{ route('projects.clearRejectedLinkage') }}">clear</a> the rejected data 
							</div>
						@endif
						<?php /*
						@if(count($logs) > 0 || !empty($logs))
							@foreach ($logs as $key => $log)
								<div class="notif-entry">
									<span class="notif-content">{{ $log['activity'] }}</span>
									<span class="notif-time">
										{{ app\Http\Controllers\HomeController::getTimePassed($log['create_time']) }} ago
									</span>
								</div>
							@endforeach
						@else
							<i>No new notification found</i>
						@endif
						*/?>
					</div>
				</div>
				{{-- Request Summary --}}
				<div class="group-category-home dash-request-container col col-12 col-sm-12 col-lg-4 py-2 px-4 rounded">
					<h4 class="mt-1">Requests</h4>
					<div class="container">
						<div class="dash-summary-row row mb-1">
							<div class="col col-md-8 col-sm-8">
								<a href="#div-employee-request"><span class="dash-summary-items">Employee Request</span></a>
							</div>
							<div class="dash-summary-count col col-md-4 col-sm-4">
								{{$employee_request != null ? count($employee_request) : "0"}}
							</div>
						</div>
						<div class="dash-summary-row row mb-1">
							<div class="col col-md-8 col-sm-8">
								<a href="#div-laptop-request"><span class="dash-summary-items">Laptop Request</span></a>
							</div>
							<div class="dash-summary-count col col-md-4 col-sm-4">
								{{$laptopRequest != null ? count($laptopRequest) : "0"}}
							</div>
						</div>
						<div class="dash-summary-row row mb-1">
							<div class="col col-md-8 col-sm-8">
								<a href="#div-laptop-link-request"><span class="dash-summary-items">Laptop Link</span></a>
							</div>
							<div class="dash-summary-count col col-md-4 col-sm-4">
								{{$laptopLinkRequest != null ? count($laptopLinkRequest) : "0"}}</div>
						</div>
						<div class="dash-summary-row row mb-1">
							<div class="col col-md-8 col-sm-8"><a href="#div-software-request">
								<span class="dash-summary-items">Software Request</span></a>
							</div>
							<div class="dash-summary-count col col-md-4 col-sm-4">
								{{$softwareRequest != null ?count($softwareRequest) : "0"}}
							</div>
						</div>
						<div class="dash-summary-row row mb-1">
							<div class="col col-md-8 col-sm-8">
								<a href="#div-project-link-request"><span class="dash-summary-items">Project Link</span></a>
							</div>
							<div class="dash-summary dash-summary-count col col-md-4 col-sm-4">
								{{$projectLinkRequest != null ? count($projectLinkRequest) : "0"}}
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
	<button id="btnTop" title="Go to top"><i class="bi bi-arrow-up"></i></button> 

	@if(auth()->user()->roles == 2)
	<div id="div-employee-request" class="row-req-table row group-category-home p-2">
		<div class="col table-avoid-overflow">
		<h3 class="mb-4"> Employee Request </h3>
			@if(count($employee_request) > 0)
			<table id="employee-request" class="table table-striped request-table" >
				<thead>
					<tr>
						<th>Name</th>
						<th>Email Address</th>
						<th>Position</th>
		                <th>Request Status</th>
		                <th>Remarks</th>
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
		                	@if ($user['approved_status'] == 1) 
		                		<p style="color: red">Rejected</p>
		                	@elseif ($user['approved_status'] == 3 || $user['approved_status'] == 4) 
		                		<p style="color: green">Pending</p>
		                	@endif
		                </td>
		                <td>
		                	{{ $user['reasons'] }}
		                </td>
						<td>	
							@if ($user['approved_status'] == 3 || $user['approved_status'] == 4) 
		                		<a href="{{ url("/employees/{$user['id']}/request") }}" class="action-view" alt="View"><i class="bi bi-eye"></i>View</a>
		                	@endif
						</td>
					</tr>
				@endforeach
				</tbody>
			</table>
			@else
				<div>
					<p class="text-center">No data available in table</p>
				</div>
			@endif
		</div>
	</div>
	@endif
	<div id="div-laptop-request" class="row-req-table row group-category-home p-2"> 
		<div class="col table-avoid-overflow">
		<h3 class="mb-4"> Laptop Request </h3>
			@if(count($laptopRequest) > 0)
			<table id="laptop-request" class="table table-striped request-table" >
					<thead>
						<tr>
							<th style="width:18%">Tag Number</th>
							<th style="width:18%">PEZA Form Number</th>
							<th style="width:18%">PEZA Permit Number</th>
							<th style="width:12%">Make</th>
							<th style="width:12%">Model</th>
							<th style="width:11%">Status</th>
							<th style="width:11%">Request Status</th>
							<th style="width:11%">Remarks</th>
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
			                <td>
			                	@if ($request['approved_status'] == 1 || !empty($request['reasons'])) 
			                		<p style="color: red">Rejected</p>
			                	@elseif ($request['approved_status'] == 3 || $request['approved_status'] == 4) 
			                		<p style="color: green">Pending</p>
			                	@endif
			                </td>
			                <td>
			                	{{ $request['reasons'] }}
			                </td>
							<td>
								@if ($request['approved_status'] == 1 && $user->id == $request['prev_updated_by']) 
			                		<a href="{{ route('laptops.create', [$request['reject_code']]) }}" class="action-view"><i class="bi bi-eye"></i>View</a>
			                	@elseif ($request['approved_status'] == 3 || $request['approved_status'] == 4) 
			                		<a href="{{ route('laptops.request', ['id' => $request['id']]) }}" class="action-view"><i class="bi bi-eye"></i>View</a>
			                	@endif
							</td>
						</tr>
					@endforeach
					</tbody>
			</table>
			@else
				<div class="col">
					<p class="text-center">No data available in table</p>
				</div>
			@endif
		</div>
	</div>
	{{-- This section is for Laptop Link list --}}
	<div id="div-laptop-link-request" class="row-req-table row group-category-home p-2">
		<div class="col table-avoid-overflow">
		<h3 class="mb-4"> Laptop Link Request </h3>
			@if(count($laptopLinkRequest) > 0)
			<table id="laptop-link-request" class="table table-striped request-table" >
				<thead>
					<tr>
						<th style="width:26%">Employee Name</th>
						<th style="width:21%">Tag Number</th>
						<th style="width:21%">Make</th>
						<th style="width:21%">Model</th>
						<th style="width:11%">Request Status</th>
						<th style="width:11%">Remarks</th>
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
			                <td>
			                	@if ($request['approved_status'] == 1 || !empty($request['reasons'])) 
			                		<p style="color: red">Rejected</p>
			                	@elseif ($request['approved_status'] == 3 || $request['approved_status'] == 4) 
			                		<p style="color: green">Pending</p>
			                	@endif
			                </td>
			                <td>
			                	{{ $request['reasons'] }}
			                </td>
							<td>
								@if ($request['approved_status'] == 3 || $request['approved_status'] == 4) 
								<a href="{{ route('laptops.details', ['id' => $request['laptop_id']]) ."#link-req-tbl" }}" class="action-view"><i class="bi bi-eye"></i>View</a>
								@endif
							</td>
						</tr>
					@endforeach
				</tbody>
			</table>
			@else
				<div>
					<p class="text-center">No data available in table</p>
				</div>
			@endif
		</div>
	</div>
	{{-- This section is for Software list --}}
	<div id="div-software-request" class="row-req-table row group-category-home p-2"> 
		<div class="col table-avoid-overflow">
			<h3 class="mb-4"> Software Request </h3>
			@if(count($softwareRequest) > 0)
			<table id="software-request" class="table table-striped request-table" >
				<thead>
					<tr>
						<th style="width:23%">Software Name</th>
						<th style="width:23%">Type</th>
						<th style="width:29%">Purpose</th>
						<th style="width:11%">Request Status</th>
						<th style="width:11%">Remarks</th>
						<th style="width:16%">Action</th>
					</tr>
				</thead>
				<tbody>
					@foreach ($softwareRequest as $software)
						<tr>
							<td>{{ $software['software_name'] }}</td>
							<td>{{ $software['type'] }}</td>
							<td>{{ $software['remarks'] }}</td>
							<td>
			                	@if ($software['approved_status'] == 1 || !empty($software['reasons'])) 
			                		<p style="color: red">Rejected</p>
			                	@elseif ($software['approved_status'] == 3 || $software['approved_status'] == 4) 
			                		<p style="color: green">Pending</p>
			                	@endif
			                </td>
			                <td>
			                	{{ $software['reasons'] }}
			                </td>
							<td>
								@if ($software['approved_status'] == 1 && $user->id == $software['prev_updated_by']) 
			                		<a href="{{ route('softwares.create', [$software['reject_code']]) }}" class="action-view"><i class="bi bi-eye"></i>View</a>
			                	@elseif ($software['approved_status'] == 3 || $software['approved_status'] == 4) 
			                		<a href="{{ route('softwares.request', ['id' => $software['id']]) }}" class="action-view"><i class="bi bi-eye"></i>View</a>
			                	@endif
								
							</td>
						</tr>
					@endforeach
				</tbody>
			</table>
			@else
				<div>
					<p class="text-center">No data available in table</p>
				</div>
			@endif
		</div>
	</div>	
	{{-- This section is for Project Link list --}}
	<div id="div-project-link-request" class="row-req-table row group-category-home p-2"> 
		<div class="col table-avoid-overflow">
			<h3 class="mb-4"> Project Link Request </h3>
			@if(count($projectLinkRequest) > 0)
			<table id="project-link-request" class="table table-striped request-table" >
				<thead>
					<tr>
						<th style="width:30%">Project Name</th>
						<th style="width:30%">Linked Employee</th>
						<th style="width:14%">Start Date</th>
						<th style="width:14%">End Date</th>
						<th style="width:11%">Request Status</th>
						<th style="width:11%">Remarks</th>
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

							<td>
			                	@if ($projectlink['approved_status'] == 1 || !empty($projectlink['reasons'])) 
			                		<p style="color: red">Rejected</p>
			                	@elseif ($projectlink['approved_status'] == 3 || $projectlink['approved_status'] == 4) 
			                		<p style="color: green">Pending</p>
			                	@endif
			                </td>
			                <td>
			                	{{ $projectlink['reasons'] }}
			                </td>
							<td>
								@if ($projectlink['approved_status'] == 3 || $projectlink['approved_status'] == 4) 

									<a href="{{ route('projects.details', ['id' => $projectlink['project_id']]) ."#link_request_tbl" }}" class="action-view"><i class="bi bi-eye"></i>View</a>
								@endif
						</td>
						</tr>
					@endforeach
				</tbody>
			</table>
			@else
				<div>
					<p class="text-center">No data available in table</p>
				</div>
			@endif
		</div>
	</div>
</div>



@include('footer')