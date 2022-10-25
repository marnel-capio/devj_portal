
// //idle timer
// var idleTime  = 0;

// $(document).ready(function(){
//     setInterval(idleTimer, 000);   //increment timer every 1 minute

//     $(this).mousemove(resetIdleTime);
//     $(this).mouseleave(resetIdleTime);
//     $(this).keypress(resetIdleTime);
//     $(this).load(resetIdleTime);
//     $(this).click(resetIdleTime);
//     $(window).unload(resetIdleTime);

// });

// function resetIdleTime(){
//     idleTime = 0;
// }

// function idleTimer(){
//     idleTime++;
//     if(idleTime > 10){
//         location.replace('/devj_portal/public/logout'); //need to replace
//     }
// }

$(document).ready(function () {
    $("#employee-request").DataTable({
		"stateSave": true,
		"pageLength": 10
	});


	//employee registration

	//password check
	$("#emp-confirm-password").keyup(function(){
		if($(this).val() != $("#emp-password").val()){
			$("#confirm-pass-text").html("Password does not match.").css("color", "red");
		}else{
			$("#confirm-pass-text").html("");
		}
	});
});

