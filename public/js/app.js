
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
	const CHANGE_PASSWORD_LINK = '/devj_portal/public/api/changePassword';
	const LINK_LAPTOP_LINK = '/devj_portal/public/api/linkLaptop';
	const LINK_PROJECT_LINK = '/devj_portal/public/api/linkProject'

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

	//start for employee details

	//change password
	$("#cp-confirm-pw, #cp-new-pw").keyup(function(){
		if($('#cp-confirm-pw').val() != $("#cp-new-pw").val()){
			$("#confirm-pass-text").html("Password does not match.").css({"color": "red", "text-align": "left"});
			$("#ecp-submit-btn").prop('disabled', true);
		}else{
			$("#confirm-pass-text").html("");
		}
	});

	$('#cp-submit-btn').click(function(e){
		var postData = {
			_token: $("#changePasswordForm > input[name=_token]").val(),
			current_password: $("#cp-current-pw").val(),
			new_password: $("#cp-new-pw").val(),
			id: $("#changePasswordForm > input[name=cp_id]").val(),
		};
		
		$.ajax({
			type: "POST",
			url: CHANGE_PASSWORD_LINK,	//update later
			data: postData,
			dataType: "json",
			encode: true,
		}).done(function(data){
	
			if(!data.success){
				//display error
				var currentPasswordErrors = data.data.current_password;
				if(currentPasswordErrors.length > 0 ){
					$("#current-pass-error").html(currentPasswordErrors[0]).css('color', 'red');
				}

				var newPasswordErrors = data.data.new_password;
				if(newPasswordErrors.length > 0 ){
					$("#new-pass-error").html(newPasswordErrors[0]).css('color', 'red');
				}
			}else{
				$("#changePasswordForm").trigger('reset');
				$("#current-pass-error").empty();
				$("#new-pass-error").empty();
				$("#cp-success-msg").html('<i class="bi bi-check-circle-fill"></i>&nbsp;You have successfully changed your account password.').addClass("text-success mb-4 text-start");
			}

		}).fail(function(){
			console.log('error');
		});

		e.preventDefault();
	});

	//link project
	$("#lp-submit-btn").click(function(e){
		var postData = {
			_token: $("#linkProjectForm > input[name=_token]").val(),
			employee_id: $("#linkProjectForm > input[name=lp_employee_id]").val(),
			project_id: $("#projectList > option:selected").val(),
			project_start: $("#project-start").val(),
			project_end: $("#project-end").val(),
			project_role: $("#projectRoleList > option:selected").val(),
		};

		$.ajax({
			type: "POST",
			url: LINK_PROJECT_LINK,
			data: postData,
			dataType: "json",
			encode: true,
		}).done(function(data){
			console.log(data)
		}).fail(function(){
			console.log('error');
		});
		
		e.preventDefault();
	});

	//link laptop
	$("#ll-submit-btn").click(function(e){
		var postData = {
			_token: $("#linkLaptopForm > input[name=_token]").val(),
			employee_id: $("#linkLaptopForm > input[name=ll_employee_id]").val(),
			laptop_id: $("#laptopList > option:selected").val(),
			brought_home_flag: $("#ll-brought-home").val(),
			vpn_access_flag: $("#ll-vpn").val(),
			surrender_flag: $("#ll-surrender").val(),
			surrender_date: $("#ll-surrender-date").val(),
		};


		$.ajax({
			type: "POST",
			url: LINK_LAPTOP_LINK,
			data: postData,
			dataType: "json",
			encode: true,
		}).done(function(data){
			console.log(data);
		}).fail(function(){
			console.log('error');
		});
		
		e.preventDefault();
	});

	//end for employee details


});

