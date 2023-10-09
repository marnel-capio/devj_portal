
@if (in_array($mailType, [config('constants.MAIL_NEW_REGISTRATION_REQUEST'), config('constants.MAIL_EMPLOYEE_UPDATE_REQUEST'), config('constants.MAIL_EMPLOYEE_LAPTOP_LINK_REQUEST'), config('constants.MAIL_EMPLOYEE_PROJECT_LINK_REQUEST'), config('constants.MAIL_EMPLOYEE_SURRENDER_LAPTOP_WHEN_USER_IS_DEACTIVATED')]))
    Hi Managers,<br>
@else
    Hi {{ $mailData['first_name'] }},<br>
@endif
<br>


@if ($mailType == config('constants.MAIL_NEW_REGISTRATION_REQUEST'))

    There is a new request for employee registration approval. <br>
    <br>
    Request Summary: <br>
    &nbsp;&nbsp;Employee name: {{ $mailData['employeeName'] }} <br>
    &nbsp;&nbsp;Position: {{ $mailData['position'] }} <br>
    <br>
    Check the request <a href="{{ url($mailData['link']) }}">here</a>.

@elseif ($mailType == config('constants.MAIL_NEW_REGISTRATION_APPROVAL'))

    Your account has been approved by the manager.<br>
    You can now access the <a href="{{ url(route('login')) }}">Dev J Portal</a>.
    

@elseif ($mailType == config('constants.MAIL_NEW_REGISTRATION_REJECTION'))

    Your registration has been rejected.<br>
    <br>
    Please see the reason below:<br>
    {{ $mailData['reasons'] }}<br>
    <br>
    Use this <a href="{{ url($mailData['link']) }}">link</a> to update your account details.


@elseif ($mailType == config('constants.MAIL_EMPLOYEE_UPDATE_REQUEST'))

    There is a new request for employee details update approval.<br>
    <br>
    Request Summary: <br>
    &nbsp;&nbsp;Requestor: {{ $mailData['requestor'] }} <br>
    &nbsp;&nbsp;Employee name: {{ $mailData['employeeName'] }} <br>
    &nbsp;&nbsp;Updated Details:<br>
    @foreach ($mailData['updatedDetails'] as $key => $data)
        &nbsp;&nbsp;&nbsp;{{$data}}<br>
    @endforeach
    <br>
    Check the request <a href="{{ url($mailData['link']) }}">here</a>.

@elseif ($mailType == config('constants.MAIL_EMPLOYEE_UPDATE_APPROVAL'))

    @if ($mailData['ownAccount'])
        Your request to update your employee details has been approved. <br>
    @else
        The request to update your employee details made by {{ $mailData['updater'] }} has been approved. <br>
    @endif
    Check the details <a href="{{ url($mailData['link']) }}">here</a>.

@elseif ($mailType == config('constants.MAIL_EMPLOYEE_UPDATE_REJECTION'))

    @if ($mailData['ownAccount'])
        Your request to update your employee details has been rejected. <br>
    @else
        The request to update your employee details made by {{ $mailData['updater'] }} has been rejected. <br>
    @endif
    <br>
    Please see the reason below:<br>
    {{ $mailData['reasons'] }}


@elseif ($mailType == config('constants.MAIL_EMPLOYEE_UPDATE_BY_MANAGER'))

    Your details in Dev J Portal has been updated by {{ $mailData['updater'] }}.<br>
    Updated Details:<br>
    @foreach ($mailData['updatedDetails'] as $key => $data)
       &nbsp;&nbsp;{{$data}}<br>
    @endforeach
    Check the details <a href="{{ url($mailData['link']) }}">here</a>.
    

@elseif ($mailType == config('constants.MAIL_EMPLOYEE_PROJECT_LINK_REQUEST'))

    There is a new request to link an employee to a project. <br>
    <br>
    Request Summary: <br>
    &nbsp;&nbsp;Project Name: {{ $mailData['projectName'] }} <br>
    &nbsp;&nbsp;Requestor: {{ $mailData['requestor'] }} <br>
    &nbsp;&nbsp;Assignee: {{ $mailData['assignee'] }} <br>
    <br>
    Check the request <a href="{{ url($mailData['link']) }}">here</a>.

@elseif ($mailType == config('constants.MAIL_EMPLOYEE_LAPTOP_LINK_REQUEST'))

    There is a new request to link a laptop to an employee.<br>
    <br>
    Request Summary: <br>
    &nbsp;&nbsp;Laptop Tag Number: {{ $mailData['tagNumber'] }} <br>
    &nbsp;&nbsp;Requestor: {{ $mailData['requestor'] }} <br>
    &nbsp;&nbsp;Assignee: {{ $mailData['assignee'] }} <br>
    <br>
    Check the request <a href="{{ url($mailData['link']) }}">here</a>.

@elseif ($mailType == config('constants.MAIL_EMPLOYEE_PROJECT_LINK_BY_MANAGER'))

    The manager has linked the project below to your account. <br>
    &nbsp;&nbsp;Project name: {{ $mailData['projectName'] }} <br>
    <br>
    Check the details <a href="{{ url($mailData['link']) }}">here</a>.

@elseif ($mailType == config('constants.MAIL_EMPLOYEE_LAPTOP_LINK_BY_MANAGER'))

    The manager has linked the laptop below to your account.  <br>
    &nbsp;&nbsp;Laptop tag number: {{ $mailData['tagNumber'] }} <br>
    <br>
    Check the details <a href="{{ url($mailData['link']) }}">here</a>.

@elseif ($mailType == config('constants.MAIL_EMPLOYEE_DEACTIVATION'))

    This is to inform you that your Dev J Portal account has been deactivated.<br>
    For any concerns, please contact your manager.

@elseif ($mailType == config('constants.MAIL_EMPLOYEE_SURRENDER_LAPTOP_NOTIFICATION'))

    Your manager requests to have your assets surrendered.<br>
    Please surrender the laptops below and update the status in the Dev J Portal.<br>
    <ol style="list-style-type: square">
    @foreach ($mailData['laptops'] as $laptop)
        <li>{{ $laptop['tag_number'] }}</li>
    @endforeach
    </ol>

@elseif ($mailType == config('constants.MAIL_EMPLOYEE_REACTIVATION'))

    This is to inform you that your Dev J Portal account has been reactivated.

@endif
@if ($mailType != config('constants.MAIL_EMPLOYEE_SURRENDER_LAPTOP_NOTIFICATION'))
<br>
@endif
<br>
Thank you!
