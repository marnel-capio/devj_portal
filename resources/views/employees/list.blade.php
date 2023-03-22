@include('header')
<link rel="stylesheet" href="{{ asset(mix('css/employee.min.css')) }}">
<script src="{{ asset(mix('js/employee.min.js')) }}" defer></script>
@include('headerMenu')
@if (session('success')) 
	<div class="alert alert-success " role="alert">
	  {{session('message')}}
	</div>
@endif
<div class="container container-list-table mt-3 ms-4 mb-5">
	<h3> Employee List </h3>
	@if(auth()->user()->roles != 3)
	<div class="row row-list">
		<div class="col">
			<a href='{!! url("/employees/sendNotification"); !!}' class="btn btn-primary float-end " id='send-notif'>
				<div class="spinner-border text-light spinner-border-sm" role="status" style="display: none">
  					<span class="sr-only"></span>
				</div>
				Send Notification
			</a>
		</div>
	</div>
	@endif
	<form action='{!! url("/employees/download"); !!}' method="POST">
        @csrf
		@if(auth()->user()->roles == config('constants.MANAGER_ROLE_VALUE'))
        <div class="row row-list">
        	<div class="col-1 filter-employee">
				Status: 
			</div>
			<div class="col-11">
				<input class="search-status-rdb-input" type="radio" name="employeeStatus" id="status-all" value="1" checked>
				<label class="search-status-rdb-label  form-check-label" for="status-all">
				    All
				</label>
				&nbsp;&nbsp;
				<input class="search-status-rdb-input" type="radio" name="employeeStatus" id="status-active" value="2" >
				<label class="search-status-rdb-label form-check-label" for="status-active">
				    Active
				</label>
				&nbsp;&nbsp;
				<input class="search-status-rdb-input" type="radio" name="employeeStatus" id="status-deactivated" value="3" >
				<label class="search-status-rdb-label form-check-label" for="status-deactivated">
				    Deactivated
				</label>
				&nbsp;&nbsp;
				<input class="search-status-rdb-input" type="radio" name="employeeStatus" id="status-transfer" value="4" >
				<label class="search-status-rdb-label form-check-label" for="status-transfer">
				    Transferred
				</label>
			</div>
		</div>
		@endif
		<div class="row row-list">
        	<div class="col-1 filter-employee">
				Filter: 
			</div>
			<div class="col">
				<input class="search-filter-rdb-input" type="radio" name="searchFilter" id="filter-name" value="1" checked>
				<label class="search-filter-rdb-label  form-check-label" for="filter-name">
				    Name
				</label>
				&nbsp;&nbsp;
				<input class="search-filter-rdb-input" type="radio" name="searchFilter" id="filter-city" value="2" >
				<label class="search-filter-rdb-label  form-check-label" for="filter-city">
				    City
				</label>
				&nbsp;&nbsp;
				<input class="search-filter-rdb-input" type="radio" name="searchFilter" id="filter-province" value="3" >
				<label class="search-filter-rdb-label  form-check-label" for="filter-province">
				    Province
				</label>
			</div>
		</div>
		<div class="row row-list">
			<div class="col-10">
				<input type="text" name="searchInput" class="search-input-text form-control" id="search-input" placeholder="Search">
			</div>

			<div class="col">
				<button type="submit" class="btn btn-primary float-end download-btn">Download</button>
			</div>
		</div>
	</form>
	<div class="row-list row">
	    <div class="col table-avoid-overflow">
	    	<table id="employee-list" class="table table-striped" >
		        <thead>
		            <tr>
		                <th class="tbl-header-name">Name</th>
		                <th>Email Address</th>
		                <th>Phone Number</th>
		                <th>City</th>
		                <th>Province</th>
						<th>BU Assignment</th>
		                <th>Status</th>
		            </tr>
		        </thead>
		        <tbody>
		        	@foreach ($employee_request as $user)
		        	<?php $id = $user["id"]; ?>
		            <tr>
		                <td><a href='{!! url("/employees/$id"); !!}'>{{$user['last_name']}}, {{$user['first_name']}} ({{$user['middle_name']}})</a></td>
		                <td>{{$user['email']}}</td>
		                <td>{{$user['cellphone_number']}}</td>
		                <td>{{$user['current_address_city']}}</td>
		                <td>{{$user['current_address_province']}}</td>
						<td>{{ $user['bu_transfer_flag'] ? $user['bu_transfer_assignment'] : "" }}</td>
		                <td>
		                	@if($user['active_status'] == 0)
		                		@if($user['approved_status'] == 1 || $user['approved_status'] == 2 || $user['approved_status'] == 4)
		                			Deactivated
		                		@else
		                			Pending for Approval
		                		@endif
		                	@else
		                		@if ($user['approved_status'] == 1)
		                			Deactivated
		                		@elseif ($user['approved_status'] == 2 || $user['approved_status'] == 4) 
		                			Active
		                		@else
		                			Pending for Approval
		                		@endif
		                	@endif
		                </td>
		            </tr>
		            @endforeach
		        </tbody>
		    </table>
	    </div>
	</div>
</div>
@include('footer')