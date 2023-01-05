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
	<h3> Employee List </h3>
	<form action='{!! url("/softwares/download"); !!}' method="POST">
                @csrf
        <div class="row row-list">
        	<div class="col-1 filter-software">
				Status: 
			</div>
			<div class="col-11">
				<input class="soft-search-status-rdb-input" type="radio" name="softwareStatus" id="status-all" value="1" checked>
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
				<input class="soft-search-status-rdb-input" type="radio" name="softwareStatus" id="status-rejected" value="5" >
				<label class="soft-search-status-rdb-label form-check-label" for="status-rejected">
				    Denied
				</label>
			</div>
		</div>
		<div class="row row-list">
			<div class="col-10">
				<input type="text" name="softSearchInput" class="search-input-text" id="search-input" placeholder="Search">
			</div>
			<div class="col">
				<button type="submit" class="btn btn-primary float-end me-1">Download</button>
			</div>
			<div class="col">
				<button type="submit" class="btn btn-primary float-end download-btn">Download</button>
			</div>
		</div>
	</form>
	<div class="row-list row">
	    <div class="col ">
	    	<table id="softwares-list" class="table table-striped" >
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
		        	<?php $id = $user["id"]; ?>
		            <tr>
		                <td><a href='{!! url("/softwares/$id"); !!}'>{{$software['software_name']}}</a></td>
		                <td>{{$software['type']}}</td>
		                <td>{{$software['approved_status']}}</td>
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