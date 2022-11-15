@include('header')

@include('headerMenu')
<div class="container container-req-table">
  @if(auth()->user()->roles == 2)

	<div class="row-req-table row">
	    <div class="col">
	      <h5> Employee Request </h5>
			<table id="employee-request" class="table table-striped request-table" >
		        <thead>
		            <tr>
		                <th class="tbl-header-name">Name</th>
		                <th>Email Address</th>
		                <th>Position</th>
		                <th>Actions</th>
		            </tr>
		        </thead>
		        <tbody>
		        	@foreach ($employee_request as $user)
		            <tr>
		                <td>{{$user['last_name']}}, {{$user['first_name']}}</td>
		                <td>{{$user['email']}}</td>
		                <td>
		                @if ($user['position'] == 1) 
		                	{{config('constants.POSITION_1_NAME')}}
		                @elseif ($user['position'] == 2) 
		                	{{config('constants.POSITION_2_NAME')}}
		                @elseif ($user['position'] == 3) 
		                	{{config('constants.POSITION_3_NAME')}}
		                @elseif ($user['position'] == 4) 
		                	{{config('constants.POSITION_4_NAME')}}
		                @elseif ($user['position'] == 5) 
		                	{{config('constants.POSITION_5_NAME')}}
		                @elseif ($user['position'] == 6) 
		                	{{config('constants.POSITION_6_NAME')}}
		                @elseif ($user['position'] == 7) 
		                	{{config('constants.POSITION_7_NAME')}}
		                @elseif ($user['position'] == 8) 
		                	{{config('constants.POSITION_8_NAME')}}
		                @elseif ($user['position'] == 9) 
		                	{{config('constants.POSITION_9_NAME')}}
		                @else
		                	-
		                @endif
						</td>
		                <td>	
		                	<a href="{{ url("/employees/{$user['id']}/request") }}" alt="View"><i class="bi bi-eye"></i>View</a>
		                </td>
		            </tr>
		            @endforeach
		        </tbody>
		    </table>
	    </div>
	@endif
</div>


@include('footer')