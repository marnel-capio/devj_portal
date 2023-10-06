
@if (in_array($mailType, [config('constants.MAIL_LAPTOP_NEW_REGISTRATION_REQUEST'), config('constants.MAIL_LAPTOP_DETAIL_UPDATE_REQUEST'), config('constants.MAIL_LAPTOP_NEW_LINKAGE_BY_NON_MANAGER_REQUEST'), config('constants.MAIL_LAPTOP_LINKAGE_UPDATE_BY_NON_MANAGER_REQUEST')]))
Hi Managers,<br>
@else
Hi {{ $mailData['firstName'] }},<br>
@endif
<br>

@if ($mailType == config('constants.MAIL_LAPTOP_NEW_REGISTRATION_REQUEST'))
There is a new request for laptop registration approval.  <br>
Check the request <a href="{{ url($mailData['link']) }}">here</a>.

@elseif ($mailType == config('constants.MAIL_LAPTOP_NEW_REGISTRATION_APPROVAL'))
Your request for the registration of the laptop below has been approved. <br>
&nbsp;&nbsp;Laptop Tag Number: {{ $mailData['tagNumber'] }} <br>
<br>
Check the details <a href="{{ url($mailData['link']) }}">here</a>.

@elseif ($mailType == config('constants.MAIL_LAPTOP_NEW_REGISTRATION_REJECTION'))
Your request for the  registration of the laptop below has been rejected.<br>
&nbsp;&nbsp;Laptop Tag Number: {{ $mailData['tagNumber'] }} <br>
<br>
Rejection Reason: {{ $mailData['reason'] }} <br>
<br>
You can update the registration <a href="{{ url($mailData['link']) }}">here</a>.

@elseif ($mailType == config('constants.MAIL_LAPTOP_DETAIL_UPDATE_REQUEST'))
There is a new request for laptop detail update approval.  <br>
Updated Details:<br>
    @foreach ($mailData['updatedDetails'] as $key => $data)
        &nbsp;&nbsp;&nbsp;{{$data}}<br>
    @endforeach
Check the request <a href="{{ url($mailData['link']) }}">here</a>.

@elseif ($mailType == config('constants.MAIL_LAPTOP_DETAIL_UPDATE_APPROVAL'))
Your request for the detail update of the laptop below has been approved. <br>
&nbsp;&nbsp;Laptop Tag Number: {{ $mailData['tagNumber'] }} <br>
<br>
Check the details <a href="{{ url($mailData['link']) }}">here</a>.

@elseif ($mailType == config('constants.MAIL_LAPTOP_DETAIL_UPDATE_REJECTION'))
Your request for the detail update of the laptop below has been rejected. <br>
&nbsp;&nbsp;Laptop Tag Number: {{ $mailData['tagNumber'] }} <br>
<br>
Rejection Reason: {{ $mailData['reason'] }}

@elseif ($mailType == config('constants.MAIL_LAPTOP_NEW_LINKAGE_BY_NON_MANAGER_REQUEST'))
There is a new request for laptop linkage.<br>
<br>
Request Summary: <br>
&nbsp;&nbsp;Requestor: {{ $mailData['requestor'] }} <br>
&nbsp;&nbsp;Assignee: {{ $mailData['assignee'] }} <br>
&nbsp;&nbsp;Laptop Tag Number: {{ $mailData['tagNumber'] }} <br>
<br>
Check the request <a href="{{ url($mailData['link']) . "#link-req-tbl" }}">here</a>.

@elseif ($mailType == config('constants.MAIL_LAPTOP_NEW_LINKAGE_BY_NON_MANAGER_APPROVAL'))
The laptop linkage request below has been approved.<br>
<br>
Request Summary: <br>
&nbsp;&nbsp;Requestor: {{ $mailData['requestor'] }} <br>
&nbsp;&nbsp;Assignee: {{ $mailData['assignee'] }} <br>
&nbsp;&nbsp;Laptop Tag Number: {{ $mailData['tagNumber'] }} <br>
<br>
Check the details <a href="{{ url($mailData['link']) }}">here</a>.

@elseif ($mailType == config('constants.MAIL_LAPTOP_NEW_LINKAGE_BY_NON_MANAGER_REJECTION'))
The laptop linkage request below has been rejected.<br>
<br>
Request Summary: <br>
&nbsp;&nbsp;Requestor: {{ $mailData['requestor'] }} <br>
&nbsp;&nbsp;Assignee: {{ $mailData['assignee'] }} <br>
&nbsp;&nbsp;Laptop Tag Number: {{ $mailData['tagNumber'] }} <br>
<br>
Reason for rejection: {{ $mailData['reason'] }}

@elseif ($mailType == config('constants.MAIL_LAPTOP_NEW_LINKAGE_BY_MANAGER_NOTIF'))
A manager has linked the laptop below to your account.<br>
&nbsp;&nbsp;Laptop Tag Number: {{ $mailData['tagNumber'] }} <br>
<br>
Check the details <a href="{{ url($mailData['link']) }}">here</a>.

@elseif ($mailType == config('constants.MAIL_LAPTOP_LINKAGE_UPDATE_BY_NON_MANAGER_REQUEST'))
There is a new request to update the details of a laptop linkage.<br>
<br>
Request Summary: <br>
&nbsp;&nbsp;Requestor: {{ $mailData['requestor'] }} <br>
&nbsp;&nbsp;Assignee: {{ $mailData['assignee'] }} <br>
&nbsp;&nbsp;Laptop Tag Number: {{ $mailData['tagNumber'] }} <br>
&nbsp;&nbsp;Updated Details:<br>
    @foreach ($mailData['updatedDetails'] as $key => $data)
        &nbsp;&nbsp;&nbsp;{{$data}}<br>
    @endforeach
<br>
<br>
Check the request <a href="{{ url($mailData['link']) . "#link-req-tbl" }}">here</a>.

@elseif ($mailType == config('constants.MAIL_LAPTOP_LINKAGE_UPDATE_BY_NON_MANAGER_APPROVAL'))
The laptop linkage update request below has been approved.<br>
<br>
Request Summary: <br>
&nbsp;&nbsp;Requestor: {{ $mailData['requestor'] }} <br>
&nbsp;&nbsp;Assignee: {{ $mailData['assignee'] }} <br>
&nbsp;&nbsp;Laptop Tag Number: {{ $mailData['tagNumber'] }} <br>
<br>
Check the details <a href="{{ url($mailData['link']) }}">here</a>.

@elseif ($mailType == config('constants.MAIL_LAPTOP_LINKAGE_UPDATE_BY_MANAGER_NOTIF'))
A manager has updated your laptop linkage data for the laptop below:<br>
&nbsp;&nbsp;Laptop Tag Number: {{ $mailData['tagNumber'] }} <br>
&nbsp;&nbsp;Updated Details:<br>
    @foreach ($mailData['updatedDetails'] as $key => $data)
        &nbsp;&nbsp;&nbsp;{{$data}}<br>
    @endforeach
<br>
Check the details <a href="{{ url($mailData['link']) }}">here</a>.

@elseif ($mailType == config('constants.MAIL_LAPTOP_LINKAGE_UPDATE_BY_NON_MANAGER_REJECTION'))
The laptop linkage update request below has been rejected.<br>
<br>
Request Summary: <br>
&nbsp;&nbsp;Requestor: {{ $mailData['requestor'] }} <br>
&nbsp;&nbsp;Assignee: {{ $mailData['assignee'] }} <br>
&nbsp;&nbsp;Laptop Tag Number: {{ $mailData['tagNumber'] }} <br>
<br>
Reason for rejection: {{ $mailData['reason'] }}

@endif

<br><br>
Thank you!
