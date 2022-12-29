
$(document).ready(function () {

	var employee_list = $("#employee-list").DataTable({
		"stateSave": true,
		"bFilter": false,
		"pageLength": 25,
		"oLanguage": {
	        "sEmptyTable": "There is no record found"
	    }
	});

    $("#send-notif").on("click", function() {
        $(".spinner-border").show();

    });

    $(".search-status-rdb-input").on("click", function(){
        filterEmployeeList();
    });

    $(".search-filter-rdb-input").on("click", function(){
        filterEmployeeList();
    });

    $("#search-input").on("input",function(){
        filterEmployeeList();
    });

    function filterEmployeeList() {
      var keyword = $("input[name='searchInput']").val();
        var filter = $("input[name='searchFilter']:checked").val();
        var status = $("input[name='employeeStatus']:checked").val();
        $.ajax({
            type:"get",
            url:"api/employees/search",
            data :{
                    'keyword' : keyword , 
                    'filter' : filter ,   
                    'status' : status ,   
                    // 'token' : $('meta[name="csrf-token"]').attr('content'),           
                },          
            success:function(res){
                employee_list.clear().draw();
                var result = JSON.parse(res);
                // console.log(result);
                result.forEach(function(employee) {
                    var status = "";
                    if (employee['active_status'] == 0) {
                        if (employee['approved_status'] == 1 || employee['approved_status'] == 2 || employee['approved_status'] == 4) {
                            status = "Deactivated";
                        } else {
                            status = "Pending for Approval";
                        }
                    } else if (employee['active_status'] == 1) {
                        if (employee['approved_status'] == 1) {
                            status = "Deactivated";
                        } else if (employee['approved_status'] == 2 || employee['approved_status'] == 4) {
                            status = "Active";
                        } else {
                            status = "Pending for Approval";
                        }
                    }
                    url = window.location.href+"/"+employee['id'];
                    employee_list.row.add(['<a href="'+url+'">'+employee['last_name']+', '+employee['first_name']+' ('+employee['middle_name']+')</a>', employee['email'], employee['cellphone_number'],employee['current_address_city'],employee['current_address_province'],status]).draw(false);
                });
            }
       });
    }

    const CHANGE_PASSWORD_LINK = '/api/changePassword';
	const LINK_LAPTOP_LINK = '/api/linkLaptop';
	const LINK_PROJECT_LINK = '/api/linkProject'

	//start for employee registration

	//disable submit button if not all required fields have value
	checkRequiredFields();

	$(":input[required]").change(function(){
		checkRequiredFields();
	});


	//password check
	$("#emp-confirm-password, #emp-password").keyup(function(){
		if($('#emp-confirm-password').val() != $("#emp-password").val()){
			$("#confirm-pass-text").html("Password does not match.").addClass('text-danger text-start');
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

	//start for employee details/request

	//change password
	$("#cp-confirm-pw, #cp-new-pw").keyup(function(){
		if($('#cp-confirm-pw').val() != $("#cp-new-pw").val()){
			$("#confirm-pass-text").html("Password does not match.").addClass('text-danger text-start');
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
				$("#cp-success-msg").empty();
				$("#current-pass-error").empty();
				$("#new-pass-error").empty();
				//display error
				var currentPasswordErrors = data.data.current_password;
				if(currentPasswordErrors && currentPasswordErrors.length > 0 ){
					$("#current-pass-error").html(currentPasswordErrors[0]).addClass('text-danger text-start');
				}

				var newPasswordErrors = data.data.new_password;
				if(newPasswordErrors && newPasswordErrors.length > 0 ){
					$("#new-pass-error").html(newPasswordErrors[0]).addClass('text-danger text-start');
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

	var projectTable = $("#project-tbl").DataTable({
		"stateSave": true,
		"bFilter": false,
		"bPaginate": false,
		"bInfo": false,
		"oLanguage": {
	        "sEmptyTable": "No Data"
	    }
	});

	$("#lp-submit-btn").click(function(e){
		$('#lp-submit-btn').prop('disabled', true);
		var postData = {
			_token: $("#linkProjectForm > input[name=_token]").val(),
			employee_id: $("#linkProjectForm > input[name=lp_employee_id]").val(),
			project_id: $("#projectList > option:selected").val(),
			project_start: $("#project-start").val(),
			project_end: $("#project-end").val(),
			project_role: $("#projectRoleList > option:selected").val(),
			project_onsite: $("#project-onsite").is(':checked') ? 1 : 0,
		};

		$.ajax({
			type: "POST",
			url: LINK_PROJECT_LINK,
			data: postData,
			dataType: "json",
			encode: true,
		}).done(function(data){
			// display error
			if(!data.success){
				$("#lp-success-msg").empty();
				$("#error-lp-proj-name").empty();
				$("#error-lp-proj-role").empty();
				$("#error-lp-proj-start").empty();
				$("#error-lp-proj-end").empty();
				var projectError = data.data.project_id;
				if(projectError && projectError.length > 0 ){
					$("#error-lp-proj-name").html(projectError[0]).addClass('text-danger text-start');
				}

				var projectRoleError = data.data.project_role;
				if(projectRoleError && projectRoleError.length > 0 ){
					$("#error-lp-proj-role").html(projectRoleError[0]).addClass('text-danger text-start');
				}

				var projectStartError = data.data.project_start;
				if(projectStartError && projectStartError.length > 0 ){
					$("#error-lp-proj-start").html(projectStartError[0]).addClass('text-danger text-start');
				}

				var projectEndError = data.data.project_end;
				if(projectEndError && projectEndError.length > 0 ){
					$("#error-lp-proj-end").html(projectEndError[0]).addClass('text-danger text-start');
				}
			}else{
				$("#linkProjectForm").trigger('reset');
				$("#error-lp-proj-name").empty();
				$("#error-lp-proj-role").empty();
				$("#error-lp-proj-start").empty();
				$("#error-lp-proj-end").empty();
				$("#projectList > option[value=" + postData.project_id + "]").remove();
				$("#lp-success-msg").html('<i class="bi bi-check-circle-fill"></i>&nbsp;' + data.message + '.').addClass("text-success mb-2 text-start");

				//update projects table
				projectTable.clear().draw();
				data.update.forEach(function(project){
					let url = window.location.origin + '/devj_portal/projects/' + project.project_id;
					// console.log(project);
					sDate = new Date(project.start_date);
					spanStart = sDate.getFullYear() + '/' + sDate.getMonth() + '/' + sDate.getDate() + ' - ';
					spanEnd = '';
					if(project.end_date != '' && project.end_date != null){
						eDate = new Date(project.end_date);
						spanEnd = eDate.getFullYear() + '/' + eDate.getMonth() + '/' + eDate.getDate();
					}
					projectTable.row.add([
						'<a href="' + url + '" class="text-decoration-none">' + project.name + '</a>',
						spanStart + spanEnd,
						project.project_status
					])
					.draw(false);
				});
			}
			$('#lp-submit-btn').prop('disabled', false);
		}).fail(function(){
			console.log('error');
		});
		
		e.preventDefault();
	});

	//link laptop

	var laptopTable = $("#laptop-tbl").DataTable({
		"stateSave": true,
		"bFilter": false,
		"bPaginate": false,
		"bInfo": false,
		"oLanguage": {
	        "sEmptyTable": "No Data"
	    }
	});

	$("#ll-submit-btn").click(function(e){
		$('#ll-submit-btn').prop('disabled', true);
		var postData = {
			_token: $("#linkLaptopForm > input[name=_token]").val(),
			employee_id: $("#linkLaptopForm > input[name=ll_employee_id]").val(),
			laptop_id: $("#laptopList > option:selected").val(),
			brought_home_flag: $("#ll-brought-home").is(':checked') ? 1 : 0,
			vpn_access_flag: $("#ll-vpn").is(':checked') ? 1 : 0,
			// surrender_flag: $("#ll-surrender").is(':checked') ? 1 : 0,
			// surrender_date: $("#ll-surrender-date").val(),
		};


		$.ajax({
			type: "POST",
			url: LINK_LAPTOP_LINK,
			data: postData,
			dataType: "json",
			encode: true,
		}).done(function(data){
			if(!data.success){
				//display error
				$("#ll-success-msg").empty();
				$("#error-laptop-id").empty();
				var laptopIdError = data.data.laptop_id;
				if(laptopIdError && laptopIdError.length > 0 ){
					$("#error-laptop-id").html(laptopIdError[0]).addClass('text-danger text-start');
				}

				// var surrenderDateError = data.data.surrender_date;
				// if(surrenderDateError && surrenderDateError.length > 0 ){
				// 	$("#error-surrender-date").html(surrenderDateError[0]).addClass('text-danger text-start');
				// }
			}else{
				$("#linkLaptopForm").trigger('reset');
				$("#error-laptop-id").empty();
				// $("#error-surrender-date").empty();
				$("#laptopList > option[value=" + postData.laptop_id + "]").remove();
				$("#ll-success-msg").html('<i class="bi bi-check-circle-fill"></i>&nbsp;' + data.message + '.').addClass("text-success mb-2 text-start");

				//update laptops table
				laptopTable.clear().draw();
				data.update.forEach(function(laptop){
					let url = window.location.origin + '/devj_portal/laptops/' + laptop.id;
					laptopTable.row.add([
						'<a href="' + url + '" class="text-decoration-none">' + laptop.tag_number + '</a>',
						laptop.brought_home,
						laptop.laptop_make,
						laptop.laptop_model,
						laptop.use_vpn
					])
					.draw(false);
				});
			}
		$('#ll-submit-btn').prop('disabled', false);
		}).fail(function(){
			console.log('error');
		});
		
		e.preventDefault();
	});

	//reject modal
	$("#reject-request-form").submit(function(){
		if($("#reject-reason").val() == ""){
			console.log("hello");
			$("#reject-reason-error").html('The reason field is required.').addClass("text-danger text-start");
			return false;
		}else if($("#reject-reason").val().length > 1024){
			$("#reject-reason-error").html('The reason must not be greater than 1024 characters.').addClass("text-danger text-start");
			return false;
		}else {
			$('#reject-sub').prop('disabled', true);
		}
	});

	//approve
	$("#approve-request-form").submit(function(){
		$('#approve-request').prop('disabled', true);
	})

	// edit submit
	$("#emp-update-form").submit(function(){
		if($("#active-status").is(':checked')){
			$("#active-status-hidden").prop('disabled', true);
		}
		if($("#server-manage-flag").is(':checked')){
			$("#server-manage-flag-hidden").prop('disabled', true);
		}
		$('#emp-update-submit').prop('disabled', true);
	});

	$('linkProjectModal').on('hidden.bs.modal', function(){
		$("#lp-success-msg").empty();
	});

	$('#linkLaptopModal').on('hidden.bs.modal', function(){
		$("#ll-success-msg").empty();
	})
	
	$('#changePasswordModal').on('hidden.bs.modal', function(){
		$("#cp-success-msg").empty();
	})


	hideAdminCheck();
	$('#position').change(function(){
		hideAdminCheck();
	});

	function hideAdminCheck(){
		if($('#position').val() == 8 || $('#position').val() == 9){
			$('#admin-check').hide();
			$('#admin-detail').hide();
			$('#is-admin').prop('disabled', true);
		}else{
			$('#admin-check').show();
			$('#admin-detail').show();
			$('#is-admin').prop('disabled', false);
		}
	}

	//end for employee details/request

	//project dropdown max min date
	updateProjectCalendar();
	$("#projectList").change(function(e){
		updateProjectCalendar();
	});

	function updateProjectCalendar(){
		var minDate = $("#projectList :selected").data("mindate");
		var maxDate = $("#projectList :selected").data("maxdate");
		
		$("#project-start").attr({
			min:minDate,
			max:maxDate
		});
		$("#project-end").attr({
			min:minDate,
			max:maxDate
		});
	}
});