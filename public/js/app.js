
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


	//start for employee registration

	//disable submit button if not all required fields have value
	checkRequiredFields();

	$(":input[required]").change(function(){
		checkRequiredFields();
	});


	//password check
	$("#emp-confirm-password, #emp-password").keyup(function(){
		if($('#emp-confirm-password').val() != $("#emp-password").val()){
			$("#confirm-pass-text").html("Password does not match.").css("color", "red");
			$("#emp-reg-submit").prop('disabled', true);
		}else{
			$("#confirm-pass-text").html("");
			checkRequiredFields();
		}
	});

	function checkRequiredFields(){
		var empty = false;
		$(":input[required]").each(function(){
			if($(this).val() == ''){
				empty = true;
			}
		})
		if(empty){
			$("#emp-reg-submit").prop('disabled', true);
		}else{
			$("#emp-reg-submit").prop('disabled', false);
		}
	}

	$('.btn-prevent-multiple-submit').on('submit', function($e){
		e.preventDefault()
		$('.btn-prevent-multiple-submit').prop('disabled', true);
	});

	//end for employee registration


});

