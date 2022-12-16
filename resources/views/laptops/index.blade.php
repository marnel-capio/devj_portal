@include('header')
{{-- <link rel="stylesheet" href="{{ asset(mix('css/employee.min.css')) }}"> --}}
<script src="{{ asset(mix('js/laptop.min.js')) }}" defer></script>
@include('headerMenu')
@if (session('success')) 
	<div class="alert alert-success " role="alert">
	  {{session('message')}}
	</div>
@endif
<div class="container container-list-table mt-3 ms-4 mb-5">
	<h3> Laptop List </h3>
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
    <div class="row row-list mb-2 mt-2 align-middle">
        <div class="col-8">
            <input type="text" name="searchInput" class="search-input-text" id="search-input" placeholder="Search">
        </div>
        <div class="col-4 text-end">
            <a href="{{ route('laptops.create') }}" class="btn btn-success me-1" id='send-notif'>Create</a>
			<button type="submit" class="btn btn-primary ms-1" form="download">Download</button>
            <form action="{{  route('laptops.download')  }}" method="GET" id="download">
                @csrf
            </form>
        </div>
    </div>
	<div class="row-list row">
	    <div class="col ">
	    	<table id="laptop-list" class="table table-striped" >
		        <thead>
		            <tr>
		                <th>Tag Number</th>
		                <th>PEZA Form No</th>
		                <th>PEZA Permit No</th>
		                <th>Make</th>
		                <th>Model</th>
		                <th>Status</th>
		            </tr>
		        </thead>
		        <tbody>
                    @if (!empty($laptopList))
                        @foreach ($laptopList as $laptop)
                        <tr>
                            <td><a href="{{ route('laptops.details', ['id' => $laptop['id']]) }}">{{ $laptop['tag_number'] }}</a></td>
                            <td>{{ $laptop['peza_form_number'] }}</td>
                            <td>{{ $laptop['peza_permit_number'] }}</td>
                            <td>{{ $laptop['laptop_make'] }}</td>
                            <td>{{ $laptop['laptop_model'] }}</td>
                            <td>{{ $laptop['status'] }}</td>
                        </tr>
                        @endforeach
                    @endif
		        </tbody>
		    </table>
	    </div>
	</div>
</div>
@include('footer')