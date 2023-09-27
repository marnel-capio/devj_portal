@include('header')
{{-- <link rel="stylesheet" href="{{ asset(mix('css/employee.min.css')) }}"> --}}
<script src="{{ asset(mix('js/laptop.min.js')) }}" defer></script>
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
	<h3> Laptop List </h3>
    <form action="{{  route('laptops.download')  }}" method="POST" id="download">
        @csrf
        <div class="row row-list">
            <div class="col-lg-1 col-2">
                Availability: 
            </div>
            <div class="col-lg-11 col-10">
                <input class="laptop-search-availability" type="radio" name="laptopAvailability" id="filter-all" value="1" checked>
                <label class="form-check-label" for="filter-all">
                    All
                </label>
                &nbsp;&nbsp;
                <input class="laptop-search-availability" type="radio" name="laptopAvailability" id="filter-owned" value="2" >
                <label class="form-check-label" for="filter-owned">
                    Owned
                </label>
                &nbsp;&nbsp;
                <input class="laptop-search-availability" type="radio" name="laptopAvailability" id="filter-not-owned" value="3" >
                <label class="form-check-label" for="filter-not-owned">
                    Not Owned
                </label>
            </div>
        </div>
        <div class="row row-list">
            <div class="col-lg-1 col-2">
                Status: 
            </div>
            <div class="col-lg-11 col-10">
                <input class="laptop-status" type="radio" name="laptopStatus" id="status-all" value="1" checked>
                <label class="form-check-label" for="status-all">
                    All
                </label>
                &nbsp;&nbsp;
                <input class="laptop-status" type="radio" name="laptopStatus" id="status-active" value="2" >
                <label class="form-check-label" for="status-active">
                    Active
                </label>
                &nbsp;&nbsp;
                <input class="laptop-status" type="radio" name="laptopStatus" id="status-inactive" value="3" >
                <label class="form-check-label" for="status-inactive">
                    Inactive
                </label>
            </div>
        </div>
        <div class="row row-list">
            <div class="col-lg-1 col-2">
                Filter: 
            </div>
            <div class="col-lg-11 col-10">
                <input class="laptop-filter" type="radio" name="searchFilter" id="tag_number" value="1" checked>
                <label class="form-check-label" for="tag_number">
                    Tag Number
                </label>
                &nbsp;&nbsp;
                <input class="laptop-filter" type="radio" name="searchFilter" id="make" value="2">
                <label class="form-check-label" for="make">
                    Make
                </label>
                &nbsp;&nbsp;
                <input class="laptop-filter" type="radio" name="searchFilter" id="model" value="3" >
                <label class="form-check-label" for="model">
                    Model
                </label>
                &nbsp;&nbsp;
                <input class="laptop-filter" type="radio" name="searchFilter" id="processor" value="4" >
                <label class="form-check-label" for="processor">
                    Processor
                </label>
                &nbsp;&nbsp;
                <input class="laptop-filter" type="radio" name="searchFilter" id="clock_speed" value="5" >
                <label class="form-check-label" for="clock_speed">
                    Clock Speed
                </label>
                &nbsp;&nbsp;
                <input class="laptop-filter" type="radio" name="searchFilter" id="ram" value="6" >
                <label class="form-check-label" for="ram">
                    RAM
                </label>
            </div>
        </div>
        <div class="row row-list mb-2 mt-2 align-middle">
            <div class="col-8">
                <input type="text" name="searchInput" class="search-input-text form-control" id="search-input" placeholder="Search">
            </div>
            <div class="col-4 text-end">
                <a class="btn btn-success me-1" id='create-server' href="{{ route('laptops.create') }}" >
                    Create
                    <div id="create-server-spinner" class="spinner-border text-light spinner-border-sm" role="status" style="display: none">
                        <span class="sr-only"></span>
                    </div>
                </a>
                <button id="laptops-download" type="submit" class="btn btn-primary ms-1" form="download">
                    Download
                    <div id="laptops-download-spinner" class="spinner-border text-light spinner-border-sm" role="status" style="display: none">
                        <span class="sr-only"></span>
                    </div>
                
                </button>
            </div>
        </div>
    </form>

	<div class="row-list row">
	    <div class="col table-avoid-overflow">
	    	<table id="laptop-list" class="table table-striped" >
		        <thead>
		            <tr>
		                <th>Tag Number</th>
		                <th>Make</th>
		                <th>Model</th>
                        <th>Processor</th>
                        <th>Clock Speed (GHz)</th>
                        <th>RAM (GB)</th>
                        <th>Assignee</th>
		                <th>Status</th>
		            </tr>
		        </thead>
		        <tbody>
                    @if (!empty($laptopList))
                        @foreach ($laptopList as $laptop)
                        <tr>
                            <td><a href="{{ route('laptops.details', ['id' => $laptop['id']]) }}">{{ $laptop['tag_number'] }}</a></td>
                            <td>{{ $laptop['laptop_make'] }}</td>
                            <td>{{ $laptop['laptop_model'] }}</td>
                            <td>{{ $laptop['laptop_cpu'] }}</td>
                            <td>{{ $laptop['laptop_clock_speed'] }}</td>
                            <td>{{ $laptop['laptop_ram'] }}</td>
                            <td>{{ !empty($laptop['owner']) ? $laptop['owner'] : '' }}</td>
                            <td>{{ $laptop['status'] }}</td>
                        </tr>
                        @endforeach
                    @endif
		        </tbody>
		    </table>
	    </div>
	</div>
	<button id="btnTop" title="Go to top"><i class="bi bi-arrow-up"></i></button> 
</div>
@include('footer')