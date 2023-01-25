
@if (in_array($mailType, [config('constants.MAIL_LAPTOP_NEW_REGISTRATION_REQUEST'), config('constants.MAIL_LAPTOP_DETAIL_UPDATE_REQUEST'), config('constants.MAIL_LAPTOP_NEW_LINKAGE_BY_NON_MANAGER_REQUEST'), config('constants.MAIL_LAPTOP_LINKAGE_UPDATE_BY_NON_MANAGER_REQUEST')]))
Hi Managers,<br>
@else
Hi {{ $mailData['firstName'] }},<br>
@endif
<br>

@if ($mailType == config('constants.MAIL_NEW_REGISTRATION_REQUEST'))
There has been a request for laptop registration approval. <br>
Check the request <a href="{{ url($mailData['link']) }}">here</a>.

@elseif ($mailType == config('constants.MAIL_LAPTOP_NEW_REGISTRATION_APPROVAL'))
Your request for laptop registration has been approved. <br>
Check the details <a href="{{ url($mailData['link']) }}">here</a>.

@elseif ($mailType == config('constants.MAIL_LAPTOP_NEW_REGISTRATION_REJECTION'))
Your laptop registration has been rejected because of the reason below: <br>
{{ $mailData['reason'] }} <br><br>
You can update the registration <a href="{{ url($mailData['link']) }}">here</a>.

@elseif ($mailType == config('constants.MAIL_LAPTOP_DETAIL_UPDATE_REQUEST'))
There has been a request for laptop detail update approval. <br>
Check the request <a href="{{ url($mailData['link']) }}">here</a>.

@elseif ($mailType == config('constants.MAIL_LAPTOP_DETAIL_UPDATE_APPROVAL'))
Your request for laptop detail update has been approved. <br>
Check the details <a href="{{ url($mailData['link']) }}">here</a>.

@elseif ($mailType == config('constants.MAIL_LAPTOP_DETAIL_UPDATE_REJECTION'))
Your request for laptop detail update has been rejected because of the reason below: <br>
{{ $mailData['reason'] }}

@elseif ($mailType == config('constants.MAIL_LAPTOP_NEW_LINKAGE_BY_NON_MANAGER_REQUEST'))
There has been a request for laptop linkage.<br>
Check the request <a href="{{ url($mailData['link']) . "#link-req-tbl" }}">here</a>.

@elseif ($mailType == config('constants.MAIL_LAPTOP_NEW_LINKAGE_BY_NON_MANAGER_APPROVAL'))
Your request for laptop linkage has been approved.<br>
Check the details <a href="{{ url($mailData['link']) }}">here</a>.

@elseif ($mailType == config('constants.MAIL_LAPTOP_NEW_LINKAGE_BY_NON_MANAGER_REJECTION'))
Your request for laptop linkage has been rejected for the reason below:<br>
{{ $mailData['reason'] }}

@elseif ($mailType == config('constants.MAIL_LAPTOP_NEW_LINKAGE_BY_MANAGER_NOTIF'))
A manager has linked a laptop to your account.<br>
Check the details <a href="{{ url($mailData['link']) }}">here</a>.

@elseif ($mailType == config('constants.MAIL_LAPTOP_LINKAGE_UPDATE_BY_NON_MANAGER_REQUEST'))
There has been a request to update the details of a laptop linkage.<br>
Check the request <a href="{{ url($mailData['link']) . "#link-req-tbl" }}">here</a>.

@elseif ($mailType == config('constants.MAIL_LAPTOP_LINKAGE_UPDATE_BY_NON_MANAGER_APPROVAL'))
Your request for laptop linkage detail update has been approved.<br>
Check the details <a href="{{ url($mailData['link']) }}">here</a>.

@elseif ($mailType == config('constants.MAIL_LAPTOP_LINKAGE_UPDATE_BY_MANAGER_NOTIF'))
A manager has updated the laptop data linked to your account.<br>
Check the details <a href="{{ url($mailData['link']) }}">here</a>.

@elseif ($mailType == config('constants.MAIL_LAPTOP_LINKAGE_UPDATE_BY_NON_MANAGER_REJECTION'))
Your request for laptop linkage update has been rejected for the reason below:<br>
{{ $mailData['reason'] }}

@endif

<br><br>
Thank you!
