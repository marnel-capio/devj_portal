@include('header')
<script src="{{ asset(mix('js/bug.min.js')) }}" defer></script>
<link rel="stylesheet" href="{{ asset(mix('css/bug.min.css')) }}">
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



{{-- Notif for Alert Banner --}}
<div class="alert d-none" role="alert" id="header-alert">
	<div id="header-alert-content">&nbsp;.</div>
</div>

<div class="container container-list-table mt-3 ms-4 mb-5">
	<h3> Bugs List </h3>
    <div class="row row-list">
        <div class="col-lg-1 col-2" id="status-label">
            Status: 
        </div>
        <div class="col-lg-11 col-10">
            <input class="bug-status" type="radio" name="bugStatus" id="status-all" value="1" checked>
            <label class="form-check-label" for="status-all">
                All
            </label>
            &nbsp;&nbsp;
            <input class="bug-status" type="radio" name="bugStatus" id="status-open" value="2" >
            <label class="form-check-label" for="status-open">
                Open
            </label>
            &nbsp;&nbsp;
            <input class="bug-status" type="radio" name="bugStatus" id="status-closed" value="3" >
            <label class="form-check-label" for="status-closed">
                Closed
            </label>
        </div>
    </div>
    <div class="row row-list">
        <div class="col-lg-1 col-2" id="status-label">
            Type: 
        </div>
        <div class="col-lg-11 col-10">
            <input class="bug-status" type="radio" name="bugType" id="bug_type_1" value="1" checked>
            <label class="form-check-label" for="bug_type_1">
                Functional Bug
            </label>
            &nbsp;&nbsp;
            <input class="bug-status" type="radio" name="bugType" id="bug_type_2" value="2" >
            <label class="form-check-label" for="bug_type_2">
                Syntax/Design Error
            </label>
            &nbsp;&nbsp;
            <input class="bug-status" type="radio" name="bugType" id="bug_type_3" value="3" >
            <label class="form-check-label" for="bug_type_3">
                Suggestion
            </label>
        </div>
    </div>
    <div class="row row-list mb-2 mt-2 align-middle">
        <div class="col-8">
            <input type="text" name="searchInput" class="search-input-text form-control" id="search-input" placeholder="Search">
        </div>
    </div>
	<div class="row-list row">
	    <div class="col-12 table-avoid-overflow">
	    	<table id="bug_list" class="table table-striped" >
		        <thead>
		            <tr>
		                <th>Bug Type</th>
		                <th>Description</th>
		                <th>Reporter</th>
                        <th>Date Reported</th>
                        <th>Ticket</th>
                        <th>Status</th>
		            </tr>
		        </thead>
		        <tbody>
                    <tr data-bs-target="#bug_details" data-bs-toggle="modal">
                        <td>Functional Bug</td>
                        <td>[B-1] The link does not redirect to details page</td>
                        <td>Laserna, Justine</td>
                        <td>2024-05-28 18:30</td>
                        <td><a href="https://www.google.com/" target="_blank" rel="noopener noreferrer">DJP-5</a></td>
                        <td>Open</td>
                    </tr>
                    <tr>
                        <td>Syntax/Design Error</td>
                        <td>[B-2] The spelling of the table header is incorrect</td>
                        <td>Laserna, Justine</td>
                        <td>2024-05-29 09:30</td>
                        <td><a href="#">DJP-6</a></td>
                        <td>Open</td>
                    </tr>
                    <tr>
                        <td>Syntax/Design Error</td>
                        <td>[B-3] The data displayed in the table is clipped</td>
                        <td>Laserna, Justine</td>
                        <td>2024-05-29 10:30</td>
                        <td><a href="#">DJP-7</a></td>
                        <td>Open</td>
                    </tr>
                    <tr>
                        <td>Suggestion</td>
                        <td>[B-4] The button color is inappropriate</td>
                        <td>Laserna, Justine</td>
                        <td>2024-05-30 01:30</td>
                        <td><i>unassigned</i></td>
                        <td>Open</td>
                    </tr>
		        </tbody>
		    </table>
	    </div>
	</div>
	<button id="btnTop" class="button-floating"  title="Go to top"><i class="bi bi-arrow-up"></i></button> 
	<button id="btnReport" class="button-floating" title="Submit Report"  data-bs-target="#bug_report" data-bs-toggle="modal"><i class="bi bi-bug"></i></button> 

    {{-- Modal for Bug Report Button --}}
    <div class="modal modal fade" tabindex='-1' id="bug_report">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        Bug report
                    </h5>
                </div>
                <div class="modal-body">
                    <p class="mt-3 pt-3 text-center">
                    Report has been submitted!
                    </p>
                    <p class="text-success text-center fs-1"><i class="bi bi-check-circle-fill"></i> </p>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button class="btn btn-primary" type="submit"  id="pj_submit_btn" form="link_employee_form">Submit
                        <div id="link_create_spinner" class="spinner-border text-light spinner-border-sm" role="status" style="display: none">
                            <span class="sr-only"></span>
                        </div>
                    </button>
                </div>
            </div>
        </div>
    </div>

    
    {{-- Modal for Bug Details --}}
    <div class="modal modal fade" tabindex='-1' id="bug_details">
        <div class="modal-bug-details modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h6 class="display-6">
                        B-1: The link does not redirect to details page
                    </h6>
                </div>

                <div class="modal-body">
                    <div class="p-2">
                        <div id="ue_success_msg"></div>
                        <form action="#" id="update_employee_linkage_form">
                            @csrf
                            <input type="text" name="employee_role" value="{{ Auth::user()->roles }}" hidden>
                            <div class="row">
                                
                                <div class="col-12 col-lg-8 form-floating mb-4">
                                    <p class="w-auto mb-0">Details</p>
                                    <div class="w-auto border border-secondary rounded p-2 group-category">
                                        <div class="w-auto">
                                        {!! nl2br("Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.
                                            Lorem ipsum:
                                            1. Lorem ipsum
                                            2. Lorem ipsum
                                            3. Lorem ipsum
                                            Lorem ipsum dolor sit amet") !!}
                                        </div>
                                    </div>
                                </div>
                                <div class="col-12 col-lg-4">
                                    <div class="row mb-2">
                                        <div class="col-12 col-md-6 col-sm-4">
                                            Reporter
                                        </div>
                                        <div class="col-12 col-md-6 col-sm-8">
                                            <a href="#">Justine Laserna</a>
                                        </div>
                                    </div>
                                    <div class="row mb-2">
                                        <div class="col-12 col-md-6 col-sm-4">
                                            Date Reported
                                        </div>
                                        <div class="col-12 col-md-6 col-sm-8">
                                            May 31, 2024 11:50 AM
                                        </div>
                                    </div>
                                    <div class="row mb-2">
                                        <div class="col-12 col-md-6 col-sm-4">
                                            Bug Type
                                        </div>
                                        <div class="col-12 col-md-6 col-sm-8">
                                            Functional Bug
                                        </div>
                                    </div>
                                    <div class="row mb-2">
                                        <div class="col-12 col-md-6 col-sm-4">
                                            Ticket Number
                                        </div>
                                        <div class="col-12 col-md-6 col-sm-8">
                                            <a href="#">DJP-5</a>
                                        </div>
                                    </div>
                                    <div class="row mb-2">
                                        <div class="col-12 col-md-6 col-sm-4">
                                            Ticket Status
                                        </div>
                                        <div class="col-12 col-md-6 col-sm-8">
                                            <div class="form-check form-switch">
                                                <input class="form-check-input" type="checkbox" role="switch" id="flexSwitchCheckChecked" checked>
                                                <label class="form-check-label" for="flexSwitchCheckChecked">Open</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <!--<div class="col-4">
                                    <div class="row mb-2 ps-5 pe-3">
                                        <div class="col-12 g-3 form-floating">
                                            <input type="text" name="name" class="form-control" id="name" placeholder="Reporter" value="Justine Laserna" disabled>
                                            <label for="name" class="text-center">Reporter</label>
                                        </div>
                                    </div>
                                    <div class="row mb-2 ps-5 pe-3">
                                        <div class="col-6 g-3 form-floating">
                                            <input type="text" name="bug_type" class="form-control" id="bug_type" placeholder="Bug Type" value="Functional Bug" disabled>
                                            <label for="bug_type" class="text-center">Bug Type</label>
                                        </div>
                                        <div class="col-6 g-3 form-floating">
                                            <input type="date" name="date_reported" class="form-control" id="date_reported" placeholder="Date Reported" value="2024-05-28 18:30" disabled>
                                            <label for="date_reported" class="text-center">Date Reported</label>
                                        </div>
                                    </div>
                                    <div class="row mb-2 ps-5 pe-3">
                                        <div class="col-6 g-3 form-floating">
                                            <input type="text" name="ticket_number" class="form-control" id="ticket_number" placeholder="Ticket Number" value="DJP-5" disabled>
                                            <label for="ticket_number" class="text-center">Ticket Number</label>
                                        </div>
                                    </div>
                                    <div class="row mb-2 ps-5 pe-3">
                                        <div class="col-6 g-3 form-floating">
                                            <input type="text" name="ticket_status" class="form-control" id="ticket_status" placeholder="Ticket Status" value="Open" disabled>
                                            <label for="ticket_status" class="text-center">Ticket Status</label>
                                        </div>
                                    </div>
                                </div>-->
                            </div>
                        </form>
                    </div>
                </div>

                <div class="modal-footer">
                    <button class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button class="btn btn-primary" type="submit"  id="pj_submit_btn" form="link_employee_form">Save
                        <div id="link_create_spinner" class="spinner-border text-light spinner-border-sm" role="status" style="display: none">
                            <span class="sr-only"></span>
                        </div>
                    </button>
                </div>
            </div>
        </div>
    </div>


</div>
</div>
@include('footer')