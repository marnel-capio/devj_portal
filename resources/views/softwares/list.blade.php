@include('header')
<link rel="stylesheet" href="{{ asset(mix('css/software.min.css')) }}">
<script src="{{ asset(mix('js/software.min.js')) }}" defer></script>
@include('headerMenu')
@if (session('success')) 
	<div class="alert alert-success " role="alert">
	  {{session('message')}}
	</div>
@endif
<div class="container container-list-table mt-3 ms-4 mb-5">
	<div class="text-primary d-flex align-items-center">
		@if (!empty($list_note))
		<i class="bi bi-info-circle-fill"></i>&nbsp;{{ $list_note }}
		@endif
	</div>
	<br/>
	<h3> Software List </h3>
    <div class="row row-list">
        <div class="col-1 filter-software">
			Type: 
		</div>
		<div class="col-11">
			<input class="soft-search-type-rdb-input" type="radio" name="softwaretype" id="type-all" value="7" checked>
			<label class="soft-search-status-rdb-label  form-check-label" for="type-all">
			    All
			</label>
			&nbsp;&nbsp;			
			<input class="soft-search-type-rdb-input" type="radio" name="softwaretype" id="type-productivity-tools" value="1">
			<label class="soft-search-status-rdb-label  form-check-label" for="type-productivity-tools">
			    Productivity Tools
			</label>
			&nbsp;&nbsp;
			<input class="soft-search-type-rdb-input" type="radio" name="softwaretype" id="type-messaging" value="2" >
			<label class="soft-search-status-rdb-label form-check-label" for="type-messaging">
			    Messaging
			</label>
			&nbsp;&nbsp;
			<input class="soft-search-type-rdb-input" type="radio" name="softwaretype" id="type-browser" value="3" >
			<label class="soft-search-status-rdb-label form-check-label" for="type-browser">
			    Browser
			</label>
			&nbsp;&nbsp;
			<input class="soft-search-type-rdb-input" type="radio" name="softwaretype" id="type-system-tilities" value="4" >
			<label class="soft-search-status-rdb-label form-check-label" for="type-system-tilities">
			    System Utility
			</label>
			&nbsp;&nbsp;
			<input class="soft-search-type-rdb-input" type="radio" name="softwaretype" id="type-project-specific" value="5" >
			<label class="soft-search-status-rdb-label form-check-label" for="type-project-specific">
			    Project Specific Software
			</label>
			&nbsp;&nbsp;
			<input class="soft-search-type-rdb-input" type="radio" name="softwaretype" id="type-phone-drivers" value="6" >
			<label class="soft-search-status-rdb-label form-check-label" for="type-phone-drivers">
			    Phone Driver
			</label>			
		</div>
	</div>
	<div class="row row-list">
		<div class="col-1 filter-software">
			Status: 
		</div>
		<div class="col-11">
			<input class="soft-search-status-rdb-input" type="radio" name="softwareStatus" id="status-all" value="5" checked>
			<label class="soft-search-status-rdb-label  form-check-label" for="status-all">
			    All
			</label>
			&nbsp;&nbsp;
			<input class="soft-search-status-rdb-input" type="radio" name="softwareStatus" id="status-approved" value="2" >
			<label class="soft-search-status-rdb-label form-check-label" for="status-approved">
			    Approved
			</label>
			&nbsp;&nbsp;
			<input class="soft-search-status-rdb-input" type="radio" name="softwareStatus" id="status-pending-new" value="3" >
			<label class="soft-search-status-rdb-label form-check-label" for="status-pending-new">
			    Pending Approval
			</label>
			&nbsp;&nbsp;
			<input class="soft-search-status-rdb-input" type="radio" name="softwareStatus" id="status-pending-update" value="4" >
			<label class="soft-search-status-rdb-label form-check-label" for="status-pending-update">
			    Pending Update Approval
			</label>
			&nbsp;&nbsp;
			<input class="soft-search-status-rdb-input" type="radio" name="softwareStatus" id="status-rejected" value="1" >
			<label class="soft-search-status-rdb-label form-check-label" for="status-rejected">
			    Denied
			</label>
		</div>
	</div>
	<div class="row row-list">
		<div class="col-10">
			<input type="text" name="softSearchInput" class="search-input-text" id="soft-search-input" placeholder="Search">
		</div>
		<div class="col">
			<button type="submit" class="btn btn-primary ms-1" form="download" >Download</button>
			<form action="{{  route('softwares.download')  }}" method="GET" id="download">
                @csrf
			</form>
		</div>
	</div>
	<div class="row-list row">
	    <div class="col ">
	    	<table id="software-list" class="table table-striped" >
		        <thead>
		            <tr>
		                <th class="tbl-header-name">Software Name</th>
		                <th>Type</th>
		                <th>Status</th>
		                <th>Rejection Reason</th>
		                <th>Purpose</th>
		            </tr>
		        </thead>
		        <tbody>
		        	@foreach ($software_request as $software)
		        	<?php $id = $software["id"]; ?>
		            <tr>
		                <td><a href='{!! url("/softwares/$id"); !!}'>{{$software['software_name']}}</a></td>
		                <td>
		                	@if($software['type'] == config('constants.SOFTWARE_TYPE_1'))
								{{  config('constants.SOFTWARE_TYPE_1_NAME')}}
							@elseif($software['type'] == config('constants.SOFTWARE_TYPE_2'))
								{{  config('constants.SOFTWARE_TYPE_2_NAME')}}
							@elseif($software['type'] == config('constants.SOFTWARE_TYPE_3'))
								{{  config('constants.SOFTWARE_TYPE_3_NAME')}}
							@elseif($software['type'] == config('constants.SOFTWARE_TYPE_4'))
								{{  config('constants.SOFTWARE_TYPE_4_NAME')}}
							@elseif($software['type'] == config('constants.SOFTWARE_TYPE_5'))
								{{  config('constants.SOFTWARE_TYPE_5_NAME')}}
							@elseif($software['type'] == config('constants.SOFTWARE_TYPE_6'))
								{{  config('constants.SOFTWARE_TYPE_6_NAME')}}
		                	@endif
		                </td>
		                <td>
		                	@if($software['approved_status'] == config('constants.APPROVED_STATUS_REJECTED'))
								{{  config('constants.APPROVED_STATUS_REJECTED_TEXT')}}
							@elseif($software['approved_status'] == config('constants.APPROVED_STATUS_APPROVED'))
								{{  config('constants.APPROVED_STATUS_APPROVED_TEXT')}}
							@elseif($software['approved_status'] == config('constants.APPROVED_STATUS_PENDING'))
								{{  config('constants.APPROVED_STATUS_PENDING_TEXT')}}
							@elseif($software['approved_status'] == config('constants.APPROVED_STATUS_PENDING_APPROVAL_FOR_UPDATE'))
								{{  config('constants.APPROVED_STATUS_PENDING_APPROVAL_FOR_UPDATE_TEXT')}}
		                	@endif
		                </td>						
		                <td>{{$software['reasons']}}</td>
		                <td>{{$software['remarks']}}</td>
		            </tr>
		            @endforeach
		        </tbody>
		    </table>
	    </div>
	</div>
</div>
@include('footer')