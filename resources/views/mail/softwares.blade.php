
@if (in_array($mailType, [config('constants.MAIL_SOFTWARE_NEW_REQUEST'), config('constants.MAIL_SOFTWARE_UPDATE_REQUEST'), config('constants.MAIL_SOFTWARE_PROJECT_LINK_REQUEST'), config('constants.MAIL_SOFTWARE_UPDATE_PROJECT_LINK_REQUEST')]))
Hi Managers,<br>
@else
Hi {{ $mailData['first_name'] }},<br>
@endif
<br>

@if ($mailType == config('constants.MAIL_SOFTWARE_NEW_REQUEST'))
There has been a request for software registration approval. <br>
Check the request <a href="{{ url($mailData['link']) }}">here</a>.


@elseif ($mailType == config('constants.MAIL_SOFTWARE_NEW_APPROVAL'))
Your request for software registration has been approved. <br>
Check the details <a href="{{ url($mailData['link']) }}">here</a>.

@elseif ($mailType == config('constants.MAIL_SOFTWARE_NEW_REJECTION'))
Your software registration has been rejected because of the reason below: <br>
{{ $mailData['reasons'] }} <br><br>
You can update the registration <a href="{{ url($mailData['link']) }}">here</a>.

@elseif ($mailType == config('constants.MAIL_SOFTWARE_UPDATE_REQUEST'))
There has been a request for software detail update approval. <br>
Check the request <a href="{{ url($mailData['link']) }}">here</a>.

@elseif ($mailType == config('constants.MAIL_SOFTWARE_UPDATE_APPROVAL'))
Your request for software detail update has been approved. <br>
Check the details <a href="{{ url($mailData['link']) }}">here</a>.

@elseif ($mailType == config('constants.MAIL_SOFTWARE_UPDATE_REJECT'))
Your request for laptop detail update has been rejected because of the reason below: <br>
{{ $mailData['reasons'] }}
Check the details <a href="{{ url($mailData['link']) }}">here</a>.

@elseif ($mailType == config('constants.MAIL_SOFTWARE_PROJECT_LINK_REQUEST'))
There has been a request for project to be link on a software. 
Check the details <a href="{{ url($mailData['link']) }}">here</a>.

@endif

<br><br>
Thank you!
