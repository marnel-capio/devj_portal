@include('header')
<link rel="stylesheet" href="{{ asset(mix('css/software.min.css')) }}">
<script src="{{ asset(mix('js/software.min.js')) }}" defer></script>
@include('headerMenu')
@if (session('success')) 
	<div class="alert alert-success " role="alert">
	  {{session('message')}}
	</div>
@endif

{{-- Notif for Alert Banner --}}
<div class="alert d-none" role="alert" id="header-alert">
	<div id="header-alert-content">&nbsp;.</div>
</div>

<div class="container container-list-table mt-3 ms-4 mb-5">
	<div class="text-primary d-flex align-items-center">
		@if (!empty($list_note_approve_by))
		<i class="bi bi-info-circle-fill"></i>&nbsp;{{ $list_note_approve_by }}
		@endif
	</div >
	<div class="text-primary d-flex align-items-center">
		@if (!empty($list_note_approve_on))
		<i class="bi bi-info-circle-fill"></i>&nbsp;{{ $list_note_approve_on }}
		@endif
	</div>
	<br>
	<h3> Software List </h3>
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
	<div class="row row-list mb-2 mt-2 align-middle">
		<div class="col-1">
			<label for="software_type">Type:</label>
		</div>
		<div class="col-3">
			<select name="software_type" id="software_type" class="soft-search-type-rdb-input form-select form-control">
				<option {{ old('type') == config('constants.SOFTWARE_TYPE_999') ? "selected" : "" }} value={{config('constants.SOFTWARE_TYPE_999')}}>{{ config('constants.SOFTWARE_FILTER_TYPE_ALL_NAME') }}</option>
				@foreach ($software_types as $software_type)
					<option {{ old('type') == $software_type['id'] ? "selected" : "" }} value={{ $software_type['id']}}>{{ $software_type['type_name'] }}</option>
				@endforeach
			</select>
		</div>
	</div>	
	<div class="row row-list mb-2 mt-2 align-middle">
		<div class="col-4 ">
			<input type="text" name="softSearchInput" class=" form-control" id="soft-search-input" placeholder="Search">
		</div>
		<div class="col-8 text-end">
			<a href="#" class="btn btn-success me-1" id='create-software'>
				Create
				<div id="create-software-spinner" class="spinner-border text-light spinner-border-sm" role="status" style="display: none">
  					<span class="sr-only"></span>
				</div>
			</a>
			<button class="btn btn-primary ms-1" id="software-download">
				Download
				<div id="software-download-spinner" class="spinner-border text-light spinner-border-sm" role="status" style="display: none">
  					<span class="sr-only"></span>
				</div>
			</button>
			<form action='{!! url("/softwares/download"); !!}' method="POST" id="software-download-form">
		        @csrf
		    </form>
			<form method="GET" id="software-list-form">
				@csrf
			</form>
		</div>
	</div>
	<div class="row-list row">
	    <div class="col table-avoid-overflow">
	    	<table id="software-list" class="table table-striped" >
		        <thead>
		            <tr>
		                <th style="width:15%"class="tbl-header-name">Software Name</th>
		                <th style="width:16%">Type</th>
		                <th style="width:11%">Status</th>
		                <th style="width:17%">Rejection Reason</th>	
		                <th style="width:17%">Purpose</th>
		                <th style="width:8%">Create Date</th>
		                <th style="width:8%">Update Date</th>
		                <th style="width:8%">Approve/Reject Date</th>
					</tr>
		        </thead>
		        <tbody>
		        	@foreach ($software_request as $software)
		        	<?php $id = $software["id"]; ?>
		            <tr>
		                <td><a href='{!! url("/softwares/$id"); !!}'>{{$software['software_name']}}</a></td>
						<td>{{$software['type']}}</td>
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
		                <td>{{date("Y-m-d", strtotime($software['create_time']) )}}</td>
		                <td>{{date("Y-m-d", strtotime($software['update_time']) )}}</td>
						@if(strlen( $software['approve_time'] ) !== 0)
			                <td>{{date("Y-m-d", strtotime($software['approve_time']) )}}</td>
						@else
							<td>{{'-'}}</td>
						@endif
					</tr>
		            @endforeach
		        </tbody>
		    </table>
	    </div>
	</div>
	<button id="btnTop" title="Go to top"><i class="bi bi-arrow-up"></i></button> 
</div>
@include('footer')