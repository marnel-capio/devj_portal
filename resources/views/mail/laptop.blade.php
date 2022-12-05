
@if (in_array($mailType, [config('constants.MAIL_LAPTOP_NEW_REGISTRATION_REQUEST')]))
Hi Managers,<br>
@else
Hi {{ $mailData['first_name'] }},<br>
@endif
<br>

@if ($mailType == config('constants.MAIL_NEW_REGISTRATION_REQUEST'))
There has been a request for laptop registration approval. <br>
Check the request <a href="{{ url($mailData['link']) }}">here</a>.

@endif

<br><br>
Thank you!
