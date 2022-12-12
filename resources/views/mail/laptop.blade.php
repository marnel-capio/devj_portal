
@if (in_array($mailType, [config('constants.MAIL_LAPTOP_NEW_REGISTRATION_REQUEST'), config('constants.MAIL_LAPTOP_DETAIL_UPDATE_REQUEST')]))
Hi Managers,<br>
@else
Hi {{ $mailData['firstName'] }},<br>
@endif
<br>

@if ($mailType == config('constants.MAIL_NEW_REGISTRATION_REQUEST'))
There has been a request for laptop registration approval. <br>
Check the request <a href="{{ url($mailData['link']) }}">here</a>.

@elseif ($mailType == config('constants.MAIL_LAPTOP_DETAIL_UPDATE_REQUEST'))
There has been a request for laptop detail update approval. <br>
Check the request <a href="{{ url($mailData['link']) }}">here</a>.

@elseif ($mailType == config('constants.MAIL_LAPTOP_NEW_LINKAGE_BY_NON_MANAGER_REQUEST'))
There has been a request for laptop linkage.<br>
Check the request <a href="{{ url($mailData['link']) }}">here</a>.

@elseif ($mailType == config('constants.MAIL_LAPTOP_NEW_LINKAGE_BY_MANAGER_NOTIF'))
A manager has linked a laptop to your account.<br>
Check the details <a href="{{ url($mailData['link']) }}">here</a>.

@elseif ($mailType == config('constants.MAIL_LAPTOP_LINKAGE_UPDATE_BY_NON_MANAGER_REQUEST'))
There has been a request to update the details of a laptop linkage.<br>
Check the request <a href="{{ url($mailData['link']) }}">here</a>.

@elseif ($mailType == config('constants.MAIL_LAPTOP_DETAIL_UPDATE_REQUEST'))
A manager has updated the laptop data linked to your account.<br>
Check the details <a href="{{ url($mailData['link']) }}">here</a>.

@endif

<br><br>
Thank you!
