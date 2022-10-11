Hi {{ $mailData['first_name'] }},<br>
<br>
There has been a request to reset your password. Refer to the information below for your new login details:<br>
<br>
Username: {{ $mailData['email'] }}<br>
Password: {{ $mailData['password'] }}<br>
<br>
Thank you!
