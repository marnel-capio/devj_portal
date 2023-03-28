@if (in_array($mailType, [config('constants.MAIL_PROJECT_EMPLOYEE_LINKAGE_REQUEST'), config('constants.MAIL_PROJECT_EMPLOYEE_LINKAGE_UPDATE_REQUEST')]))
Hi Managers,<br>
@else
Hi {{ $mailData['firstName'] }},<br>
@endif
<br>

@if ($mailType == config('constants.MAIL_PROJECT_EMPLOYEE_LINKAGE_BY_MANAGER'))

A manager has linked the project below to your account. <br>
&nbsp;&nbsp;Project name: {{ $mailData['project_name'] }} <br>
<br>
Check the details <a href="{{ url($mailData['link']) }}">here</a>.


@elseif ($mailType == config('constants.MAIL_PROJECT_EMPLOYEE_LINKAGE_REQUEST'))

There is a new request to link an employee to a project. <br>
&nbsp;&nbsp;Project name: {{ $mailData['project_name'] }} <br>
&nbsp;&nbsp;Requestor: {{ $mailData['requestor'] }} <br>
&nbsp;&nbsp;Member: {{ $mailData['member'] }} <br>
<br>
Check the request <a href="{{ url($mailData['link']) }}">here</a>.

@elseif ($mailType == config('constants.MAIL_PROJECT_EMPLOYEE_LINKAGE_UPDATE_BY_MANAGER'))

A manager has updated your project linkage data for the project below: <br>
&nbsp;&nbsp;Project name: {{ $mailData['project_name'] }} <br>
<br>
Check the details <a href="{{ url($mailData['link']) }}">here</a>.

@elseif ($mailType == config('constants.MAIL_PROJECT_EMPLOYEE_LINKAGE_UPDATE_REQUEST'))

There is a new request to update the details of a project linkage. <br>
&nbsp;&nbsp;Project name: {{ $mailData['project_name'] }} <br>
&nbsp;&nbsp;Requestor: {{ $mailData['requestor'] }} <br>
&nbsp;&nbsp;Member: {{ $mailData['member'] }} <br>
<br>
Check the request <a href="{{ url($mailData['link']) }}">here</a>.

@endif

<br><br>
Thank you!