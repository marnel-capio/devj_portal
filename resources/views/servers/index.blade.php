@include('header')
<script src="{{ asset(mix('js/server.min.js')) }}" defer></script>
<link rel="stylesheet" href="{{ asset(mix('css/server.min.css')) }}">
@include('headerMenu')
@if (session()->pull('success')) 
	<div class="alert alert-success " role="alert">
	  {{session()->pull('message')}}
	</div>
@endif
@if(!empty(session('download_alert')))
<div class="alert alert-danger" role="alert">
    {{session()->pull('download_alert')}}
</div>
<div class="container-md ps-md-3 pe-md-3 pt-2 mb-3">
@endif
<div class="container container-list-table mt-3 ms-4 mb-5">
	<h3> Server List </h3>
    <div class="row row-list">
        <div class="col-lg-1 col-2" id="status-label">
            Status: 
        </div>
        <div class="col-lg-11 col-10">
            <input class="server-status" type="radio" name="serverStatus" id="status-all" value="1" checked>
            <label class="form-check-label" for="status-all">
                All
            </label>
            &nbsp;&nbsp;
            <input class="server-status" type="radio" name="serverStatus" id="status-active" value="2" >
            <label class="form-check-label" for="status-active">
                Active
            </label>
            &nbsp;&nbsp;
            <input class="server-status" type="radio" name="serverStatus" id="status-inactive" value="3" >
            <label class="form-check-label" for="status-inactive">
                Inactive
            </label>
        </div>
    </div>
    <div class="row row-list mb-2 mt-2 align-middle">
        <div class="col-8">
            <input type="text" name="searchInput" class="search-input-text form-control" id="search-input" placeholder="Search">
        </div>
        <div class=" col-4 text-end">
            <a href="{{ route('servers.create') }}" class="btn btn-success me-1">Create</a>
			<button type="submit" class="btn btn-primary ms-1" form="download">Download</button>
            <form action="{{  route('servers.download')  }}" method="GET" id="download">
                @csrf
            </form>
        </div>
    </div>
	<div class="row-list row">
	    <div class="col-12 table-avoid-overflow">
	    	<table id="server_list" class="table table-striped" >
		        <thead>
		            <tr>
		                <th class="w-20">Server</th>
		                <th>IP Address</th>
		                <th class="w-20">Function/Role</th>
                        <th>HDD Status</th>
                        <th>Memory Status</th>
                        <th>CPU Status</th>
		                <th>Status</th>
                        <th class="w-10 text-center">Action</th>
		            </tr>
		        </thead>
		        <tbody>
                    @if (!empty($serverData))
                        @foreach ($serverData as $server)
                            <tr>
                                <td><a href="{{ route('servers.details', ['id' => $server['id']]) }}">{{ $server['server_name'] }}</a></td>
                                <td>{{ $server['server_ip'] }}</td>
                                <td>{!! nl2br(e($server['function_role'])) !!}</td>
                                <td class="{{ config('constants.STATUS_CLASS.' .$server['hdd_status']) }}">{{ config('constants.STATUS_NAMES.' .$server['hdd_status']) }}</td>
                                <td class="{{ config('constants.STATUS_CLASS.' .$server['ram_status']) }}">{{ config('constants.STATUS_NAMES.' .$server['ram_status']) }}</td>
                                <td class="{{ config('constants.STATUS_CLASS.' .$server['cpu_status']) }}">{{ config('constants.STATUS_NAMES.' .$server['cpu_status']) }}</td>
                                <td>{{ $server['status'] }}</td>
                                <td class="text-center"><button class="btn btn-sm btn-danger delete_server" data-id="{{ $server['id'] }}" data-server_name="{{ $server['server_name'] }}">Delete</button></td>
                            </tr>
                        @endforeach
                    @endif
		        </tbody>
		    </table>
	    </div>
	</div>
</div>
@include('footer')