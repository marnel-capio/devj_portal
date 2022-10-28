
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
    <br>

@elseif ($mailType == config('constants.MAIL_NEW_REGISTRATION_REJECTION'))

@elseif ($mailType == config('constants.MAIL_EMPLOYEE_UPDATE_REQUEST'))

@elseif ($mailType == config('constants.MAIL_EMPLOYEE_UPDATE_APPROVAL'))

@elseif ($mailType == config('constants.MAIL_EMPLOYEE_UPDATE_REJECTION'))

@elseif ($mailType == config('constants.MAIL_EMPLOYEE_UPDATE_BY_MANAGER'))

@elseif ($mailType == config('constants.MAIL_EMPLOYEE_PROJECT_LINK_REQUEST'))

There has been a request for employee to link on a project. Check the details on the link below:<br>
<br>
<a href="{{ url($mailData['link']) }}">Request Link</a>

@elseif ($mailType == config('constants.MAIL_EMPLOYEE_LAPTOP_LINK_REQUEST'))

There has been a request for employee to link on a laptop. Check the details on the link below:<br>
<br>
<a href="{{ url($mailData['link']) }}">Request Link</a>

    
@endif
<br>
<br>
Thank you!
