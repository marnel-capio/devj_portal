
@if (in_array($mailType, [config('constants.MAIL_NEW_REGISTRATION_REQUEST'), config('constants.MAIL_EMPLOYEE_UPDATE_REQUEST'), config('constants.MAIL_EMPLOYEE_LAPTOP_LINK_REQUEST'), config('constants.MAIL_EMPLOYEE_PROJECT_LINK_REQUEST')]))
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
    You can now access the Dev J Portal.<br>
    <a href="{{ url(route('login')) }}">Request Link</a>

@elseif ($mailType == config('constants.MAIL_NEW_REGISTRATION_REJECTION'))

    Your registration has been rejected.<br>
    <br>
    Please see the reason below:<br>
    {{ $reasons }}<br>
    <br>
    Use the this <a href="{{ url($link) }}">link</a> to update your account details.


@elseif ($mailType == config('constants.MAIL_EMPLOYEE_UPDATE_REQUEST'))

    There has been a request for employee details update approval. Check the details on the link below:<br>
    <br>
    Requestor: {{ $requestor }}<br>
    <a href="{{ url($link) }}">Request Link</a>

@elseif ($mailType == config('constants.MAIL_EMPLOYEE_UPDATE_APPROVAL'))

    Your request update on your employee details has been approved.

@elseif ($mailType == config('constants.MAIL_EMPLOYEE_UPDATE_REJECTION'))

    Your request update on your employee details has been rejected.<br>
    <br>
    Please see the reason below:<br>
    {{ $reasons }}


@elseif ($mailType == config('constants.MAIL_EMPLOYEE_UPDATE_BY_MANAGER'))

Your details in Dev J Portal has been updated by {{ $updater }}.<br>
<br>
Check the details on the link below:<br>
<a href="{{ url($link) }}">Details Link</a>

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

    
@endif
<br>
<br>
Thank you!
