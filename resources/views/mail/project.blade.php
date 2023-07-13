@if (in_array($mailType, [config('constants.MAIL_PROJECT_EMPLOYEE_LINKAGE_REQUEST'), config('constants.MAIL_PROJECT_EMPLOYEE_LINKAGE_UPDATE_REQUEST')]))
Hi Managers,<br>
@else
Hi {{ $mailData['firstName'] }},<br>
@endif
<br>


{{-- ITP Mail Expected Format case: A --}}
@if ($mailType == config('constants.MAIL_PROJECT_EMPLOYEE_LINKAGE_BY_MANAGER'))

A manager has linked the project below to your account.<br>
&nbsp;&nbsp;Project name: {{ $mailData['project_name'] }}<br>
<br>
Check the details <a href="{{ url($mailData['link']) }}">here</a>.


{{-- ITP Mail Expected Format case: B --}}
@elseif ($mailType == config('constants.MAIL_PROJECT_EMPLOYEE_LINKAGE_REQUEST'))

There is a new request to link an employee to a project.<br>
<br>
Request summary:<br>
&nbsp;&nbsp;Project name: {{ $mailData['project_name'] }}<br>
&nbsp;&nbsp;Requestor: {{ $mailData['requestor'] }}<br>
&nbsp;&nbsp;Member: {{ $mailData['member'] }}<br>
<br>
Check the request <a href="{{ url($mailData['link']) }}">here</a>.


{{-- ITP Mail Expected Format case: C --}}
@elseif ($mailType == config('constants.MAIL_PROJECT_EMPLOYEE_LINKAGE_UPDATE_BY_MANAGER'))

A manager has updated your project linkage data for the project below:<br>
&nbsp;&nbsp;Project name: {{ $mailData['project_name'] }}<br>
<br>
Check the details <a href="{{ url($mailData['link']) }}">here</a>.


{{-- ITP Mail Expected Format case: D --}}

@elseif ($mailType == config('constants.MAIL_PROJECT_EMPLOYEE_LINKAGE_UPDATE_REQUEST'))

There is a new request to update the details of a project linkage.<br>
<br>
Request summary:<br>
&nbsp;&nbsp;Project name: {{ $mailData['project_name'] }}<br>
&nbsp;&nbsp;Requestor: {{ $mailData['requestor'] }}<br>
&nbsp;&nbsp;Member: {{ $mailData['member'] }}<br>
<br>
Check the request <a href="{{ url($mailData['link']) }}">here</a>.


{{-- ITP Mail Expected Format case: E --}}

@elseif ($mailType == config('constants.MAIL_PROJECT_DETAIL_UPDATE_REJECTION'))

The project linkage request below has been rejected:<br>
<br>
Request summary:<br>
&nbsp;&nbsp;Project name: {{ $mailData['project_name'] }}<br>
&nbsp;&nbsp;Requestor: {{ $mailData['requestor'] }}<br>
&nbsp;&nbsp;Member: {{ $mailData['assignee'] }}<br>
<br>
Reason for Rejection:  {{ $mailData['reason']}}


{{-- ITP Mail Expected Format case: F --}}

@elseif ($mailType == config('constants.MAIL_PROJECT_DETAIL_UPDATE_APPROVAL'))

The project linkage request below has been approved:<br>
<br>
Request summary:<br>
&nbsp;&nbsp;Project name: {{ $mailData['project_name'] }}<br>
&nbsp;&nbsp;Requestor: {{ $mailData['requestor'] }}<br>
&nbsp;&nbsp;Member: {{ $mailData['assignee'] }}<br>
<br>
Check the details <a href="{{ url($mailData['link']) }}">here</a>.


{{-- ITP Mail Expected Format case: G --}}

@elseif ($mailType == config('constants.MAIL_PROJECT_NEW_LINKAGE_BY_NON_MANAGER_REJECTION'))

The project linkage update request below has been rejected:<br>
<br>
Request summary:<br>
&nbsp;&nbsp;Project name: {{ $mailData['project_name'] }}<br>
&nbsp;&nbsp;Requestor: {{ $mailData['requestor'] }}<br>
&nbsp;&nbsp;Member: {{ $mailData['assignee'] }}<br>
<br>
Reason for Rejection:  {{ $mailData['reason']}}


{{-- ITP Mail Expected Format case: H --}}

@elseif ($mailType == config('constants.MAIL_PROJECT_NEW_LINKAGE_BY_NON_MANAGER_APPROVAL'))

The project linkage update request below has been approved:<br>
<br>
Request summary:<br>
&nbsp;&nbsp;Project name: {{ $mailData['project_name'] }}<br>
&nbsp;&nbsp;Requestor: {{ $mailData['requestor'] }}<br>
&nbsp;&nbsp;Member: {{ $mailData['assignee'] }}<br>
<br>
Check the details <a href="{{ url($mailData['link']) }}">here</a>.

@endif

<br><br>
Thank you!