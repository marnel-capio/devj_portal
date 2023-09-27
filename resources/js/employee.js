const CHANGE_PASSWORD_LINK = '/api/changePassword';
const LINK_LAPTOP_LINK = '/api/linkLaptop';
const LINK_EMPLOYEE_PROJECT_LINK = '/api/linkProjectToEmployee';
const DEACTIVATE_EMPLOYEE_LINK = '/api/deactivateEmployee';
const REACTIVATE_EMPLOYEE_LINK = '/api/reactivateEmployee';
const TRANSFER_EMPLOYEE_LINK = '/api/transferEmployee';
const REINSTATE_EMPLOYEE_LINK = '/api/reinstateEmployee';
const NOTIFY_SURRENDER_OF_LAPTOPS_LINK = '/api/notifySurrender';
const APPROVE_EMPLOYEE_LINK = '/employees/store';
const DOWNLOAD_EMPLOYEE_LINK = '/employees/download';
const SEND_NOTIFICATION_LINK = '/employees/sendNotification';
const BU_LIST = {
    '1'  : 'Dev A',
    '2'  : 'Dev B',
    '3'  : 'Dev C',
    '4'  : 'Dev D',
    '5'  : 'Dev E',
    '6'  : 'Dev F',
    '7'  : 'Dev G',
    '8'  : 'Dev H',
    '9'  : 'Dev I',
    '10'  : 'Dev K',
    '11'  : 'Dev L',
    '12'  : 'Dev M',
    '13'  : 'Dev N',
    '14'  : 'Dev 2',
    '15'  : 'Dev 3',
    '16'  : 'Dev 5',
    '17'  : 'Dev 6',
    '18'  : 'C4I',
};

$(document).ready(function () {

	var employee_list = $("#employee-list").DataTable({
		"stateSave": true,
		"bFilter": false,
		"pageLength": 25,
		"oLanguage": {
	        "sEmptyTable": "There is no record found"
	    }
	});

    /**
     * Set header alert 
	 * message   : message string
	 * alertType : [0: danger], [1: success], [2: info (default)]
	 * displayed : specifies if alert header is displayed or hidden 
	 * 
     * @param string message
     * @param int alertType [default:2 - info, 1 - success, 0 - danger]
     * @param bool displayed [default:true]
     * @return void
     */

    function setHeaderAlert(message, alertType = 2, displayed = true) {
		if(!displayed) {
			$("#header-alert").addClass("d-none");

			return;
		}

		$("#header-alert").removeClass("d-none");
		$("#header-alert").removeClass("alert-info");
		$("#header-alert").removeClass("alert-success");
		$("#header-alert").removeClass("alert-danger");

		let fadeout = true;

		switch(alertType) {
			case 1:
				$("#header-alert").addClass("alert-success");
				fadeout = true;
				break;
			case 0:
				$("#header-alert").addClass("alert-danger");
				fadeout = true;
				break;

			default:
				$("#header-alert").addClass("alert-info");
				fadeout = false;
				break;

		}


		$("#header-alert").html(`<div id='header-alert-content'>${message}</div>`);

		if(fadeout) {
			setTimeout(function(){
				$("#header-alert-content").fadeOut("slow", function() {
					$("#header-alert").removeClass("d-block");
					$("#header-alert").addClass("d-none");
				});
			}, 5000);
		}
		
	}

    $("#send-notif").on("click", function() {
        $("#send-notif-spinner").show();
		$("#send-notif").prop("disabled", true);
		$("#send-notif").prop("id", "send-notif-sending");
		setHeaderAlert("Notifications to employees are being sent . . .", 2, true);
		
		$.ajax({
			type: "GET",
			url: SEND_NOTIFICATION_LINK

		}).done(function(data){
			if(data.success) {
				setHeaderAlert(data.message, 1, true);
			} else {
				setHeaderAlert(data.message, 0, true);
			}

		}).fail(function (data, exception) {
			var msg = '';
			if (data.status === 0) {
				msg = 'Not connected.\n Verify Network.';
			} else if (data.status == 404) {
				msg = 'Requested page not found. [404]';
			} else if (data.status == 500) {
				msg = 'Internal Server Error [500].';
			} else if (exception === 'parsererror') {
				msg = 'Requested JSON parse failed.';
			} else if (exception === 'timeout') {
				msg = 'Time out error.';
			} else if (exception === 'abort') {
				msg = 'Ajax request aborted.';
			} else {
				msg = 'Uncaught Error.\n' + data.responseText;
			}
			console.log('error: ' + msg);
			setHeaderAlert(msg + ". Consider refreshing the page", 0, true);

		}).always(function() {
			$("#send-notif-sending").prop("id", "send-notif");
			$("#send-notif").removeAttr("disabled");
			$("#send-notif-spinner").hide();
		});

		e.preventDefault();
    });

	// Employee Download button is clicked
	$("#employee-download").on("click", function() {
		$("#employee-download-spinner").show();
		$("#employee-download").prop("disabled", true);
		
		setTimeout(function(){
			$("#employee-download-spinner").hide();
			$("#employee-download").prop("disabled", false);
		}, 5000);

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
    var passport = $("input[name='passportStatus']:checked").val();
    
    if (passport == undefined) {
        passport = "";
    }

    $.ajax({
      type: "get",
      url: "api/employees/search",
      data: {
        'keyword': keyword,
        'filter': filter,
        'status': status,
        'passport' : passport
        // 'token' : $('meta[name="csrf-token"]').attr('content'),           
      },

      success: function success(res) {
        employee_list.clear().draw();
        var result = JSON.parse(res);
        // console.log(result);
        result.forEach(function (employee) {
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
          var buAssignment = '';
          if (employee['bu_transfer_flag']) {
            buAssignment = BU_LIST[employee['bu_transfer_assignment']];
          }
          url = window.location.href + "/" + employee['id'];

          if (passport == "") {
              employee_list.row.add(['<a href="' + url + '">' + employee['last_name'] + ', ' + employee['first_name'] + ' (' + employee['middle_name'] + ')</a>', employee['email'], employee['cellphone_number'], employee['current_address_city'], employee['current_address_province'], buAssignment, status]).draw(false);
          } else {

              employee_list.row.add(['<a href="' + url + '">' + employee['last_name'] + ', ' + employee['first_name'] + ' (' + employee['middle_name'] + ')</a>', employee['email'], employee['cellphone_number'], employee['current_address_city'], employee['current_address_province'], buAssignment, status, employee['passport_expiration_date'],employee['date_of_appointment'],employee['no_appointment_reason']]).draw(false);
          }
          
        });
      }
    });
    }


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

	$("#copy-permanent-address").click(function(){
		var isSame = $("#copy-permanent-address").prop('checked');
		
		$("#cur-add-strt").val($("#perm-add-strt").val());
		$("#cur-add-town").val($("#perm-add-town").val());
		$("#cur-add-prov").val($("#perm-add-prov").val());
		$("#cur-add-postal").val($("#perm-add-postal").val());
		
		$("#cur-add-strt").prop("readonly", isSame);
		$("#cur-add-town").prop("readonly", isSame);
		$("#cur-add-prov").prop("readonly", isSame);
		$("#cur-add-postal").prop("readonly", isSame);
		
		if(isSame)
		{
			$("#cur-add-strt").addClass("is-disabled");
			$("#cur-add-town").addClass("is-disabled");
			$("#cur-add-prov").addClass("is-disabled");
			$("#cur-add-postal").addClass("is-disabled");
		} else {
			$("#cur-add-strt").removeClass("is-disabled");
			$("#cur-add-town").removeClass("is-disabled");
			$("#cur-add-prov").removeClass("is-disabled");
			$("#cur-add-postal").removeClass("is-disabled");
		}
		checkRequiredFields();
	});


	$("input[name *= 'permanent_address'").change(function(){
		var isSame = $("#copy-permanent-address").prop('checked');
		if(isSame) {
			$("#cur-add-strt").val($("#perm-add-strt").val());
			$("#cur-add-town").val($("#perm-add-town").val());
			$("#cur-add-prov").val($("#perm-add-prov").val());
			$("#cur-add-postal").val($("#perm-add-postal").val());
		}
	})

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
		"ordering": false,
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
			remarks: $("#link_remarks").val()
		};

		$.ajax({
			type: "POST",
			url: LINK_EMPLOYEE_PROJECT_LINK,
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
				$("#error-lp-remarks").empty();
				//error-lp-remarks
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

				var remarksError = data.data.remarks;
				if(remarksError && remarksError.length > 0 ){
					$("#error-lp-remarks").html(remarksError[0]).addClass('text-danger text-start');
				}
			}else{
				$("#linkProjectForm").trigger('reset');
				$("#error-lp-proj-name").empty();
				$("#error-lp-proj-role").empty();
				$("#error-lp-proj-start").empty();
				$("#error-lp-proj-end").empty();
				$("#error-lp-remarks").empty();
				$("#projectList > option[value=" + postData.project_id + "]").remove();
				$("#lp-success-msg").html('<i class="bi bi-check-circle-fill"></i>&nbsp;' + data.message + '.').addClass("text-success mb-2 text-start");

				//update projects table
				projectTable.clear().draw();
				data.update.forEach(function(project){
					let url = window.location.origin + '/projects/' + project.project_id;
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
		}).fail(function (data, exception) {
			var msg = '';
			if (data.status === 0) {
				msg = 'Not connected.\n Verify Network.';
			} else if (data.status == 404) {
				msg = 'Requested page not found. [404]';
			} else if (data.status == 500) {
				msg = 'Internal Server Error [500].';
			} else if (exception === 'parsererror') {
				msg = 'Requested JSON parse failed.';
			} else if (exception === 'timeout') {
				msg = 'Time out error.';
			} else if (exception === 'abort') {
				msg = 'Ajax request aborted.';
			} else {
				msg = 'Uncaught Error.\n' + data.responseText;
			}
			console.log('error: ' + msg);
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
		$("#ll-link-spinner").show();
		$('#ll-submit-btn').prop('disabled', true);
		var postData = {
			_token: $("#linkLaptopForm > input[name=_token]").val(),
			employee_id: $("#linkLaptopForm > input[name=ll_employee_id]").val(),
			laptop_id: $("#laptopList > option:selected").val(),
			brought_home_flag: $("#ll-brought-home").is(':checked') ? 1 : 0,
			vpn_access_flag: $("#ll-vpn").is(':checked') ? 1 : 0,
			remarks: $("#ll-remarks").val()
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
				$("#error-ll-remarks").empty();
				var laptopIdError = data.data.laptop_id;
				if(laptopIdError && laptopIdError.length > 0 ){
					$("#error-laptop-id").html(laptopIdError[0]).addClass('text-danger text-start');
				}
				var laptopRemarksError = data.data.remarks;
				if(laptopRemarksError && laptopRemarksError.length > 0 ){
					$("#error-ll-remarks").html(laptopRemarksError[0]).addClass('text-danger text-start');
				}
			}else{
				$("#linkLaptopForm").trigger('reset');
				$("#error-laptop-id").empty();
				$("#error-ll-remarks").empty();
				$("#laptopList > option[value=" + postData.laptop_id + "]").remove();
				$("#ll-success-msg").html('<i class="bi bi-check-circle-fill"></i>&nbsp;' + data.message + '.').addClass("text-success mb-2 text-start");

				//update laptops table
				laptopTable.clear().draw();
				data.update.forEach(function(laptop){
					let url = window.location.origin + '/laptops/' + laptop.id;
					laptopTable.row.add([
						'<a href="' + url + '" class="text-decoration-none">' + laptop.tag_number + '</a>',
						laptop.brought_home,
						laptop.laptop_make,
						laptop.laptop_model,
						laptop.use_vpn,
						laptop.remarks
					])
					.draw(false);
				});
			}
		$("#ll-link-spinner").hide();
		$('#ll-submit-btn').prop('disabled', false);
		}).fail(function(){
			console.log('error');
		});
		
		e.preventDefault();
	});


	// Click Reject button to display Reject Modal
	$("#reject-request").click(function(){
        $("#employee_reject_spinner").show();
		$('#reject-request').prop('disabled', true);
	})

	// Submit the rejection reason 
	$("#reject-request-form").submit(function(){
        $("#employee_reject_submit_spinner").show();
		if($("#reject-reason").val() == ""){
			$("#reject-reason-error").html('The reason field is required.').addClass("text-danger text-start");
			return false;
		}else if($("#reject-reason").val().length > 1024){
			$("#reject-reason-error").html('The reason must not be greater than 1024 characters.').addClass("text-danger text-start");
			return false;
		}else {
			$('#reject-sub').prop('disabled', true);
		}
	});

	// Approve Employee Detail Update/Creation request
	$("#approve-request-form").submit(function(){
        $("#employee_approve_spinner").show();
		$('#approve-request').prop('disabled', true);
	});

	// edit submit
	$("#emp-update-form").submit(function(){
        $("#employee-update-submit-spinner").show();

		if($("#active-status").is(':checked')){
			$("#active-status-hidden").prop('disabled', true);
		}
		if($("#server-manage-flag").is(':checked')){
			$("#server-manage-flag-hidden").prop('disabled', true);
		}
		if($("#is-admin").is(':checked')){
			$("#is-admin-hidden").prop('disabled', true);
		}
		$('#emp-update-submit').prop('disabled', true);
	});

	$('#linkProjectModal').on('hidden.bs.modal', function(){
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

	//start for employee deactivation/reactivation

	$("#employee-deactivate").click(function(e){
		$(".alert").remove();
		if (!confirm("Continue with employee deactivation?")) {
			return false;
		}
		$("#react-deact-spinner").show();
		$.ajax({
			type: "POST",
			url: DEACTIVATE_EMPLOYEE_LINK,
			data: {id: $("#deact-react-form > input[name=id").val(), _token: $("#deact-react-form > input[name=_token").val()},
			dataType: "json",
			encode: true,
		}).done(function(data){
			if(data.success){
				location.reload();
			}else{
				//display error 
				$("#deact-react-alert").remove();
				$("#alert-div").append('<div id="deact-react-alert" class="alert alert-danger" role="alert"><span class="ms-2">' + data.message + '</span></div>');
			}
			$("#react-deact-spinner").hide();
		}).fail(function(){
			console.log('error');
		})
	});

	$("#employee-reactivate").click(function(e){
		if (!confirm("Continue with employee reactivation?")) {
			return false;
		}
		$("#react-deact-spinner").show();
		$.ajax({
			type: "POST",
			url: REACTIVATE_EMPLOYEE_LINK,
			data: {id: $("#deact-react-form > input[name=id").val(), _token: $("#deact-react-form > input[name=_token").val()},
			dataType: "json",
			encode: true,
		}).done(function(data){
			if(data.success){
				location.reload();
			}else{
				//display error 
				$("#deact-react-alert").remove();
				$("#alert-div").append('<div id="deact-react-alert" class="alert alert-danger" role="alert"><span class="ms-2">' + data.message + '</span></div>');
			}
			$("#react-deact-spinner").hide();
		}).fail(function(){
			console.log('error');
		})
	});

	//end for for employee deactivation/reactivation

	//start for employee bu transfer/reinstate

	//bu transfer - form submission
	$("#transferEmployeeForm").on('submit', function (e) {
		$("#transfer_reinstate_spinner").show();
		$("#bu_transfer_msg").empty();

		//form submission
		var formData = $(this).serializeArray();
        var arrData = [];
        formData.forEach(function(data){
            arrData[data['name']] = data['value'];
        });
		var jsonData = JSON.stringify(Object.assign({}, arrData));
        jsonData = JSON.parse(jsonData);

		$.ajax({
			type: "POST",
			url: TRANSFER_EMPLOYEE_LINK,
			data: jsonData,
			dataType: "json",
			encode: true,
		}).done( function (data) {
			$("#transfer_reinstate_spinner").hide();
			if (data.success) {
				location.reload();
			} else {
				//display error message
				if (typeof data.data === 'undefined') {
					$("#bu_transfer_msg").text(data.message).addClass("text-danger text-start");
				} else {
					$("#bu_transfer_msg").text(data.data.bu_transfer_assignment[0]).addClass("text-danger text-start");
				}

			}
		}).fail(function(){
			console.log('error');
		})

		e.preventDefault();
	})

	$('#buTransferModal').on('hidden.bs.modal', function(){
		$("#bu_transfer_msg").empty();
	})

	$("#employee_reinstate").click(function(e){
		if (!confirm("Continue to reinstate employee to Dev J?")) {
			return false;
		}
		$("#transfer_reinstate_spinner").show();
		$.ajax({
			type: "POST",
			url: REINSTATE_EMPLOYEE_LINK,
			data: {id: $("#deact-react-form > input[name=id").val(), _token: $("#deact-react-form > input[name=_token").val()},
			dataType: "json",
			encode: true,
		}).done(function(data){
			$("#transfer_reinstate_spinner").hide();
			if(data.success){
				location.reload();
			}else{
				//display error 
				$("#deact-react-alert").remove();
				$("#alert-div").append('<div id="reinstate_alert" class="alert alert-danger" role="alert"><span class="ms-2">' + data.message + '</span></div>');
			}
		}).fail(function(){
			console.log('error');
		})
	});


	
	$("#emp-reg-submit").click(function(e){
		$("#employee-reg-submit-spinner").show();
	});

	//end for employee bu transfer/reinstate

    /**
     * Passport details display
     */

	var passport_status = $("input[name=passport_status]:checked").prop("value");
	enableDisablePassportInputs(passport_status);

    $("input[name=passport_status]").change(function () {
		var val = $(this).attr("value");
        enableDisablePassportInputs(val);
    });
	
    function enableDisablePassportInputs(passport_status = 1) {
		$("#withPassport").addClass("d-none");
		$("#withAppointment").addClass("d-none");
		$("#withoutAppointment").addClass("d-none");
		$("#waitingDelivery").addClass("d-none");
		$("#passport-status-switch-spinner").show();

		if(passport_status == 1) {
			$("#withPassport").removeClass("d-none");

		} else if(passport_status == 2) {
			$("#withAppointment").removeClass("d-none");

		} else if(passport_status == 3) {
			$("#withoutAppointment").removeClass("d-none");

		} else if(passport_status == 4) {
			$("#waitingDelivery").removeClass("d-none");

		} else {
			alert("Wrong passport status detected. Please refresh the page");
		}
		$("#passport-status-switch-spinner").hide();

    }

});