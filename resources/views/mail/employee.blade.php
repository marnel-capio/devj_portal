
@if (in_array($mailType, [config('constants.MAIL_NEW_REGISTRATION_REQUEST'), config('constants.MAIL_EMPLOYEE_UPDATE_REQUEST'), config('constants.MAIL_EMPLOYEE_LAPTOP_LINK_REQUEST'), config('constants.MAIL_EMPLOYEE_PROJECT_LINK_REQUEST'), config('constants.MAIL_EMPLOYEE_SURRENDER_LAPTOP_WHEN_USER_IS_DEACTIVATED')]))
    Hi Managers,<br>
@else
    Hi {{ $mailData['first_name'] }},<br>
@endif
<br>


@if ($mailType == config('constants.MAIL_NEW_REGISTRATION_REQUEST'))

    There has been a request for new registration approval. Check the details on the link below:<br>
    <br>
    <a href="{{ url($mailData['link']) }}">Request Link</a>

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

    There has been a request for employee details update approval. Check the details on the link below:<br>
    <br>
    Requestor: {{ $mailData['requestor'] }}<br>
    <a href="{{ url($mailData['link']) }}">Request Link</a>

@elseif ($mailType == config('constants.MAIL_EMPLOYEE_UPDATE_APPROVAL'))

    Your request update on your employee details has been approved.

@elseif ($mailType == config('constants.MAIL_EMPLOYEE_UPDATE_REJECTION'))

    Your request update on your employee details has been rejected.<br>
    <br>
    Please see the reason below:<br>
    {{ $mailData['reasons'] }}


@elseif ($mailType == config('constants.MAIL_EMPLOYEE_UPDATE_BY_MANAGER'))

    Your details in Dev J Portal has been updated by {{ $mailData['updater'] }}.<br>
    <br>
    Check the details on the link below:<br>
    <a href="{{ url($mailData['link']) }}">Details Link</a>

@elseif ($mailType == config('constants.MAIL_EMPLOYEE_PROJECT_LINK_REQUEST'))

    There has been a request for employee to link on a project. Check the details on the link below:<br>
    <br>
    Requestor: {{ $mailData['requestor'] }}<br>
    <a href="{{ url($mailData['link']) }}">Request Link</a>

@elseif ($mailType == config('constants.MAIL_EMPLOYEE_LAPTOP_LINK_REQUEST'))

    There has been a request for employee to link on a laptop. Check the details on the link below:<br>
    <br>
    Requestor: {{ $mailData['requestor'] }}<br>
    <a href="{{ url($mailData['link']) }}">Request Link</a>

@elseif ($mailType == config('constants.MAIL_EMPLOYEE_PROJECT_LINK_BY_MANAGER'))

    The manager has linked a project to your account. <br>
    Check the details <a href="{{ url($mailData['link']) }}">here</a>.


@elseif ($mailType == config('constants.MAIL_EMPLOYEE_LAPTOP_LINK_BY_MANAGER'))

    The manager has linked a laptop to your account. <br>
    Check the details <a href="{{ url($mailData['link']) }}">here</a>.

@elseif ($mailType == config('constants.MAIL_EMPLOYEE_SURRENDER_LAPTOP_WHEN_USER_IS_DEACTIVATED'))

    {{ $mailData['employeeName'] }}'s account has been deactivated.<br>
    A request to surrenderthe laptops assigned to {{ $mailData['employeeName'] }} has been created.<br>
    Check the request <a href="{{ url($mailData['link']) }}">here</a>.
    
@endif
<br>
<br>
Thank you!
