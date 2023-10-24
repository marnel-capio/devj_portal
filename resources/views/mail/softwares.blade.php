
@if (in_array($mailType, [config('constants.MAIL_SOFTWARE_NEW_REQUEST'), config('constants.MAIL_SOFTWARE_UPDATE_REQUEST'), config('constants.MAIL_SOFTWARE_PROJECT_LINK_REQUEST'), config('constants.MAIL_SOFTWARE_UPDATE_PROJECT_LINK_REQUEST'), config('constants.MAIL_SOFTWARE_REGIST_CANCEL')]))
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
Updated Details:<br>
    @foreach ($mailData['updatedDetails'] as $key => $data)
        &nbsp;&nbsp;&nbsp;{{$data}}<br>
    @endforeach
Check the request <a href="{{ url($mailData['link']) }}">here</a>.

@elseif ($mailType == config('constants.MAIL_SOFTWARE_UPDATE_APPROVAL'))
Your request for software detail update has been approved. <br>
Check the details <a href="{{ url($mailData['link']) }}">here</a>.

@elseif ($mailType == config('constants.MAIL_SOFTWARE_UPDATE_REJECT'))
Your request for software detail update has been rejected because of the reason below: <br>
{{ $mailData['reasons'] }}<br>
Check the details <a href="{{ url($mailData['link']) }}">here</a>.

@elseif ($mailType == config('constants.MAIL_SOFTWARE_PROJECT_LINK_REQUEST'))
There has been a request for project to be link on a software. 
Check the details <a href="{{ url($mailData['link']) }}">here</a>.

@elseif ($mailType == config('constants.MAIL_SOFTWARE_REGIST_CANCEL'))

This is to inform you that the request for software registration of {{ $mailData['softwareDetails']['software_name']}} by {{$mailData['employeeName']}} has been cancelled.


@endif

<br><br>
Thank you!
