@include('header')

@include('headerMenu')

<div class="container container-req-table">
	<div class="row-req-table row">
		<div class="container">
			<div class="row dash-head">
				{{-- Contains Urgent / Latest notifications --}}
				<div class="group-category-home dash-notification-container col col-12 col-lg-7 py-2 px-4 mb-3">
					<div class="row mb-3">
						<div class="col col-12  col-md-12 col-lg-7">
							<h3> Welcome, {{ auth()->user()->first_name }}</h3>
						</div>
						<div class="col col-12  col-md-12 col-lg-5 text-muted small">
							<span> Today is {{ $date }} </span>
						</div>
					</div>
					<!--div>
						<h4 class="mt-1">Notifications</h4>
					</div-->
					<div class="dash-notifications">
						@if($user['passport_isAlertDisplayed'] && $user['approved_status'] != 4)
							{{-- Set the Passport notification alert type --}}
							@if($user["passport_isWarning"])
								<div class="alert alert-danger" role="alert">
							@else
								<div class="alert alert-info" role="alert">
							@endif

								{{-- Set the Passport notification content --}}
								{{ $user['passport_message'] }}

								@if($user['passport_status'] == config('constants.PASSPORT_STATUS_WITH_PASSPORT_VALUE') && $user['is_date_passed']) 
								<br>
								Please consider renewing your Passport
								@endif

								<br>
								<i><a href="{{route('employees.edit', ['id' => auth()->user()->id]) }}#passport-details">Update passport details</a></i>

							</div>
						@endif

						@if(isset($employee_details)  && !empty($employee_details['reasons']))
							<div class="alert alert-danger" role="alert">
								Your update in account details has been rejected. <a href="{{ route('employees.clearRejectedUpdate') }}">Clear</a> the rejected data. </br>
								Reasons: {{$employee_details['reasons']}}.
							</div>
						@endif

						@if(isset($softwareRejectedCount)  && !empty($softwareRejectedCount))
							<div class="alert alert-danger" role="alert">
								Your software registration is rejected. Please see the details.
							</div>
						@endif

						@if(isset($softwareUpdateRejectedCount)  && !empty($softwareUpdateRejectedCount))
							<div class="alert alert-danger" role="alert">
								Your software update is rejected. Please see the details or <a href="{{ route('softwares.clearRejectedUpdate') }}">clear</a> the rejected data.
							</div>
						@endif

						@if(isset($laptopRejectedCount)  && !empty($laptopRejectedCount))
							<div class="alert alert-danger" role="alert">
								Your laptop registration is rejected. Please see the details.
							</div>
						@endif

						@if(isset($laptopUpdateRejectedCount)  && !empty($laptopUpdateRejectedCount))
							<div class="alert alert-danger" role="alert">
								Your laptop update is rejected. Please see the details or <a href="{{ route('laptops.clearRejectedUpdate') }}">clear</a> the rejected data.
							</div>
						@endif

						@if(isset($laptopLinkRejectedCount)  && !empty($laptopLinkRejectedCount))
							<div class="alert alert-danger" role="alert">
								Your laptop link registration/update is rejected. Please see the details or <a href="{{ route('laptops.clearRejectedLinkage') }}">clear</a> the rejected data.
							</div>
						@endif

						@if(isset($projectLinkRejectedCount)  && !empty($projectLinkRejectedCount))
							<div class="alert alert-danger" role="alert">
								Your project link registration/update is rejected. Please see the details or <a href="{{ route('projects.clearRejectedLinkage') }}">clear</a> the rejected data.
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
				<div class="group-category-home dash-request-container col col-12 col-lg-5 py-2 px-4  mb-3">
					<h4 class="mt-1">Requests</h4>
					<div class="container">
						
						@if(auth()->user()->roles == config('constants.MANAGER_ROLE_VALUE') )
						<div class="dash-summary-row row mb-1">
							<div class="col col-md-8 col-sm-8">
								<a href="#div-employee-request"><span class="dash-summary-items">Employee Request</span></a>
							</div>
							<div class="dash-summary-count col col-md-4 col-sm-4">
								{{$employee_request != null ? count($employee_request) : "0"}}
							</div>
						</div>
						@endif
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
								<a href="#div-laptop-link-request"><span class="dash-summary-items">Laptop Link Request</span></a>
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
								<a href="#div-project-link-request"><span class="dash-summary-items">Project Link Request</span></a>
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

	@if(auth()->user()->roles == config('constants.MANAGER_ROLE_VALUE') && count($employee_request) > 0)
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
							@foreach (config('constants.POSITIONS') as $value => $name)
								@if ($user['position'] == $value) 
									{{ $name }}
								@endif
                            @endforeach
						</td>
		                <td>
		                	@if ($user['approved_status'] == config('constants.APPROVED_STATUS_REJECTED')) 
		                		<p style="color: red">Rejected</p>
		                	@elseif ($user['approved_status'] == config('constants.APPROVED_STATUS_PENDING') || $user['approved_status'] == config('constants.APPROVED_STATUS_PENDING_APPROVAL_FOR_UPDATE')) 
		                		<p style="color: green">Pending</p>
		                	@endif
		                </td>
		                <td>
		                	{{ $user['reasons'] }}
		                </td>
						<td>	
							@if ($user['approved_status'] == config('constants.APPROVED_STATUS_PENDING') || $user['approved_status'] == config('constants.APPROVED_STATUS_PENDING_APPROVAL_FOR_UPDATE')) 
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
	@if($laptopRequest != null)
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
			                	@if ($request['approved_status'] == config('constants.APPROVED_STATUS_REJECTED')  || !empty($request['reasons'])) 
			                		<p style="color: red">Rejected</p>
			                	@elseif ($request['approved_status'] == config('constants.APPROVED_STATUS_PENDING') || $request['approved_status'] == config('constants.APPROVED_STATUS_PENDING_APPROVAL_FOR_UPDATE')) 
			                		<p style="color: green">Pending</p>
			                	@endif
			                </td>
			                <td>
			                	{{ $request['reasons'] }}
			                </td>
							<td>
								@if ($request['approved_status'] == config('constants.APPROVED_STATUS_REJECTED')  && $user->id == $request['prev_updated_by']) 
			                		<a href="{{ route('laptops.create', [$request['reject_code']]) }}" class="action-view"><i class="bi bi-eye"></i>View</a>
			                	@elseif ($request['approved_status'] == config('constants.APPROVED_STATUS_PENDING') || $request['approved_status'] == config('constants.APPROVED_STATUS_PENDING_APPROVAL_FOR_UPDATE')) 
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
	@endif
	@if($laptopLinkRequest != null)
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
			                	@if ($request['approved_status'] == config('constants.APPROVED_STATUS_REJECTED')  || !empty($request['reasons'])) 
			                		<p style="color: red">Rejected</p>
			                	@elseif ($request['approved_status'] == config('constants.APPROVED_STATUS_PENDING') || $request['approved_status'] == config('constants.APPROVED_STATUS_PENDING_APPROVAL_FOR_UPDATE')) 
			                		<p style="color: green">Pending</p>
			                	@endif
			                </td>
			                <td>
			                	{{ $request['reasons'] }}
			                </td>
							<td>
								@if ($request['approved_status'] == config('constants.APPROVED_STATUS_PENDING') || $request['approved_status'] == config('constants.APPROVED_STATUS_PENDING_APPROVAL_FOR_UPDATE')) 
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
	@endif
	@if($softwareRequest != null)
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
			                	@if ($software['approved_status'] == config('constants.APPROVED_STATUS_REJECTED')  || !empty($software['reasons'])) 
			                		<p style="color: red">Rejected</p>
			                	@elseif ($software['approved_status'] == config('constants.APPROVED_STATUS_PENDING') || $software['approved_status'] == config('constants.APPROVED_STATUS_PENDING_APPROVAL_FOR_UPDATE')) 
			                		<p style="color: green">Pending</p>
			                	@endif
			                </td>
			                <td>
			                	{{ $software['reasons'] }}
			                </td>
							<td>
								@if ($software['approved_status'] == config('constants.APPROVED_STATUS_REJECTED')  && $user->id == $software['prev_updated_by']) 
			                		<a href="{{ route('softwares.create', [$software['reject_code']]) }}" class="action-view"><i class="bi bi-eye"></i>View</a>
			                	@elseif ($software['approved_status'] == config('constants.APPROVED_STATUS_PENDING') || $software['approved_status'] == config('constants.APPROVED_STATUS_PENDING_APPROVAL_FOR_UPDATE')) 
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
	@endif
	@if($projectLinkRequest != null)	
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
			                	@if ($projectlink['approved_status'] == config('constants.APPROVED_STATUS_REJECTED')  || !empty($projectlink['reasons'])) 
			                		<p style="color: red">Rejected</p>
			                	@elseif ($projectlink['approved_status'] == config('constants.APPROVED_STATUS_PENDING') || $projectlink['approved_status'] == config('constants.APPROVED_STATUS_PENDING_APPROVAL_FOR_UPDATE')) 
			                		<p style="color: green">Pending</p>
			                	@endif
			                </td>
			                <td>
			                	{{ $projectlink['reasons'] }}
			                </td>
							<td>
								@if ($projectlink['approved_status'] == config('constants.APPROVED_STATUS_PENDING') || $projectlink['approved_status'] == config('constants.APPROVED_STATUS_PENDING_APPROVAL_FOR_UPDATE')) 

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
	@endif
</div>



@include('footer')