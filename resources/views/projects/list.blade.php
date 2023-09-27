@include('header')
<link rel="stylesheet" href="{{ asset(mix('css/project.min.css')) }}">
<script src="{{ asset(mix('js/project.min.js')) }}" defer></script>
@include('headerMenu')
@if (session('success')) 
	<div class="alert alert-success " role="alert">
	  {{session('message')}}
	</div>
@endif
<div class="container container-list-table mt-3 ms-4 mb-5">
	<h3> Project List </h3>
	<div class="row row-list">
		<div class="col-1 filter-project">
			Status: 
		</div>
		<div class="col-11">
			<input class="project-search-status-rdb-input" type="radio" name="projectStatus" id="status-all" value={{config('constants.PROJECT_STATUS_FILTER_ALL')}} checked>
			<label class="project-search-status-rdb-label  form-check-label" for="status-all">
			    All
			</label>
			&nbsp;&nbsp;
			<input class="project-search-status-rdb-input" type="radio" name="projectStatus" id="status-finish" value={{config('constants.PROJECT_STATUS_FILTER_FINISH')}} >
			<label class="project-search-status-rdb-label  form-check-label" for="status-finish">
			    {{config('constants.PROJECT_STATUS_FINISH_TEXT')}}
			</label>
			&nbsp;&nbsp;
			<input class="project-search-status-rdb-input" type="radio" name="projectStatus" id="status-ongoing" value={{config('constants.PROJECT_STATUS_FILTER_ONGOING')}} >
			<label class="project-search-status-rdb-label  form-check-label" for="status-ongoing">
			    {{config('constants.PROJECT_STATUS_ONGOING_TEXT')}}
			</label>
			&nbsp;&nbsp;
		</div>
	</div>
	<div class="row row-list mb-2 mt-2 align-middle">
		<div class="col-4 ">
			<input type="text" name="projSearchInput" class=" form-control" id="proj-search-input" placeholder="Search">
		</div>
	@if(Auth::user()->roles == config('constants.MANAGER_ROLE_VALUE'))		
		<div class="col-8 text-end">
			<a href="{{ route('projects.create') }}" class="btn btn-success me-1" id='create-project'>
				Create
				<div id="create-project-spinner" class="spinner-border text-light spinner-border-sm" role="status" style="display: none">
  					<span class="sr-only"></span>
				</div>
			</a>
		</div>
	@endif
	</div>
	<div class="row-list row">
	    <div class="col table-avoid-overflow">
	    	<table id="project-list" class="table table-striped" >
		        <thead>
		            <tr>
		                <th style="width:20%"class="tbl-header-name">Project Name</th>
		                <th style="width:10%">Start Date</th>
		                <th style="width:10%">End Date</th>
		                <th style="width:10%">Status</th>	
					</tr>
		        </thead>
		        <tbody>
		        	@foreach ($project_list as $project)
		        	<?php $id = $project["id"]; ?>
		            <tr>
		                <td><a href='{!! url("/projects/$id"); !!}'>{{$project['name']}}</a></td>
		                <td>{{date("Y-m-d", strtotime($project['start_date']) )}}</td>
						@if(strlen( $project['end_date'] ) !== 0)
		              		<td>{{date("Y-m-d", strtotime($project['end_date']) )}}</td>
						@else
							<td>{{'-'}}</td>
						@endif
		                <td>{{$project['status']}}</td>
					</tr>
		            @endforeach
		        </tbody>
		    </table>
	    </div>
	</div>
<button id="btnTop" title="Go to top"><i class="bi bi-arrow-up"></i></button> 

</div>
@include('footer')