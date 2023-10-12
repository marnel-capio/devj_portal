const CHANGE_PASSWORD_LINK = '/api/changePassword';
const LINK_LAPTOP_LINK = '/api/linkLaptop';
const LINK_EMPLOYEE_PROJECT_LINK = '/api/linkProjectToEmployee';
const DEACTIVATE_EMPLOYEE_LINK = '/api/deactivateEmployee';
const REACTIVATE_EMPLOYEE_LINK = '/api/reactivateEmployee';
const TRANSFER_EMPLOYEE_LINK = '/api/transferEmployee';
const REINSTATE_EMPLOYEE_LINK = '/api/reinstateEmployee';
const NOTIFY_SURRENDER_OF_LAPTOPS_LINK = '/api/notifySurrender';
const APPROVE_EMPLOYEE_LINK = '/employees/store';
const SEND_NOTIFICATION_LINK = '/employees/sendNotification';
const EMPLOYEE_DOWNLOAD_LINK = '/employees/download';
const GET_CITY = '/api/getCities';
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

	function getCity(addressType="permanent",province,city=null,isSameAddress=false) {
		if (province != null && province != "") {
			$.ajax({
	            type: "GET",
	            url: GET_CITY,
	            data: {
	                province: province,
	            },
	            dataType: "json",
	            encode: true
	        }).done(function(data){
	        		if (addressType == "permanent") {
	        			$('#perm-add-town').empty();
				    	$('#perm-add-town').append(`<option value=""></option>`);
	        		}
	        		if (addressType == "current" || isSameAddress) {
	        			$('#cur-add-town').empty();
				    	$('#cur-add-town').append(`<option value=""></option>`);
	        		}
	        		
	        	$.each(data.cities, function (i, item) {
	        		var selected = "";
	        		if (city != null && item == city) {
	        			selected = "selected";		
	        		}
	        		if (addressType == "permanent") {
	        			 $('#perm-add-town').append('<option value="'+i+'" '+selected+'>'+item+'</option>');
	        		} 
	        		if (addressType == "current" || isSameAddress) {
	        			 $('#cur-add-town').append('<option value="'+i+'" '+selected+'>'+item+'</option>');
	        		}
				   
				});
	        }).fail(function(){
	            // console.log('error');
	        });
		}
	}
	getCity("permanent",$("#perm-prov").val(),$("#perm-city").val());
	getCity("current",$("#cur-prov").val(),$("#cur-city").val());
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

    function setHeaderAlert(message, alertType = 2, displayed = true, custom = "default") {
		let header_identifier;
		if(custom === "default") {
			header_identifier = "#header-alert";
		} else {
			header_identifier = custom;
		}
		if(!displayed) {
			$(header_identifier).addClass("d-none");

			return;
		}

		$(header_identifier).removeClass("d-none");
		$(header_identifier).removeClass("alert-info");
		$(header_identifier).removeClass("alert-success");
		$(header_identifier).removeClass("alert-danger");

		let fadeout = true;

		switch(alertType) {
			case 1:
				$(header_identifier).addClass("alert-success");
				fadeout = true;
				break;
			case 0:
				$(header_identifier).addClass("alert-danger");
				fadeout = true;
				break;

			default:
				$(header_identifier).addClass("alert-info");
				fadeout = false;
				break;

		}


		$(header_identifier).html(`<div id='header-alert-content'>${message}</div>`);

		if(fadeout) {
			setTimeout(function(){
				$(header_identifier + " > #header-alert-content").fadeOut("slow", function() {
					$(header_identifier).removeClass("d-block");
					$(header_identifier).addClass("d-none");
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
			// console.log('error: ' + msg);
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
        
		setHeaderAlert("Requesting download current list", 2, true);
		$("#employee-download-spinner").show();
		$("#employee-download").prop("disabled", true);


		var postData = {
			_token: $("#employee-list-form > input[name=_token]").val(),
            searchFilter: $('#employee-list-form > input[name="searchFilter"]:checked').val(),
            searchInput: $('input[name="searchInput"]').val(),
		};

		$.ajax({
			type: "POST",
			url: EMPLOYEE_DOWNLOAD_LINK,
			data: postData,
			encode: true,
		}).done(function(){
			$("#employee-list-form").submit();
            setHeaderAlert("Download request sent", 1, true);
			setTimeout(function(){
				setHeaderAlert("Download request sent", 1, true);
				$("#employee-download-spinner").hide();
				$("#employee-download").prop("disabled", false);
			}, 3000);
		}).fail(function (data, exception) {
			var msg = '';
			if (data.status === 0) {
				msg = 'Not connected.\n Verify Network.';
			} else if (data.status == 404) {
				msg = '404 Requested page not found.';
			} else if (data.status == 500) {
				msg = '500 Internal Server Error.';
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
			
			setHeaderAlert('Error: ' + msg + ' Refreshing the page.', 0, true);
			$("#employee-download-spinner").hide();
			$("#employee-download").prop("disabled", false);
			setTimeout(function(){
				location.reload(true);
			}, 5000);

		});

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

              employee_list.row.add(['<a href="' + url + '">' + employee['last_name'] + ', ' + employee['first_name'] + ' (' + employee['middle_name'] + ')</a>', employee['email'], employee['cellphone_number'], employee['current_address_city'], employee['current_address_province'], buAssignment, status, employee['passport_expiration_date'],employee['date_of_appointment'],employee['date_of_delivery'],employee['no_appointment_reason']]).draw(false);
          }
          
        });
      }
    });
    }


	//start for employee registration

	//disable submit button if not all required fields have value



	$("#reg-form > :input").change(function(){
		checkRequiredFields();
	});


	// Password check : 1. on Key up
	$("#emp-confirm-password, #emp-password").keyup(() => {
		checkRequiredFields();
	});

	// Password check : 2. on change
	$("#emp-confirm-password, #emp-password").change(() => {
		checkRequiredFields();
	});

	
    /**
     * Test for password complexity 
	 * @param purpose = register			DEFAULT		\\ if purpose is for employee register
	 * @param purpose = change_password					\\ if purpose is for change password
     * @return boolean
     */
	function checkPasswordComplexity(purpose = "register"){
		var upperCase= new RegExp('[A-Z]');
		var lowerCase= new RegExp('[a-z]');
		var numbers = new RegExp('[0-9]');
		var specialChars = new RegExp('[!@#$%&*_.]');
		switch(purpose) {
			case "change_password" :
				password_value = $("#cp-new-pw").val();
				confirm_password_value = $('#cp-confirm-pw').val();
				break;

			case "register" :
			default:
				password_value = $("#emp-password").val();
				confirm_password_value = $('#emp-confirm-password').val();
				break;
				
		}
	
		let  isPasswordValid = true;
		if(password_value != "") {
			// Match
			if(confirm_password_value != password_value){
				$(".err-pass-match").css("display", "inline");
				$(".correct-pass-match").css("display", "none");
				isPasswordValid = false;
			}else{
				$(".correct-pass-match").css("display", "inline");
				$(".err-pass-match").css("display", "none");
			}

			// Minimum
			if (password_value.length < 8) {
				$(".err-pass-min").css("display", "inline");
				$(".correct-pass-min").css("display", "none");
				isPasswordValid = false;
			} else {
				$(".correct-pass-min").css("display", "inline");
				$(".err-pass-min").css("display", "none");
			}

			// Lower
			if (password_value.match(lowerCase)) {
				$(".correct-pass-lower").css("display", "inline");
				$(".err-pass-lower").css("display", "none");
			} else {
				$(".err-pass-lower").css("display", "inline");
				$(".correct-pass-lower").css("display", "none");
				isPasswordValid = false;
			}

			// Upper
			if (password_value.match(upperCase)) {
				$(".correct-pass-upper").css("display", "inline");
				$(".err-pass-upper").css("display", "none");
			} else {
				$(".err-pass-upper").css("display", "inline");
				$(".correct-pass-upper").css("display", "none");
				isPasswordValid = false;
			}

			// Special chars
			if (password_value.match(specialChars)) {
				$(".correct-pass-char").css("display", "inline");
				$(".err-pass-char").css("display", "none");
			} else {
				$(".err-pass-char").css("display", "inline");
				$(".correct-pass-char").css("display", "none");
				isPasswordValid = false;
			}

			// Number
			if (password_value.match(numbers)) {
				$(".correct-pass-number").css("display", "inline");
				$(".err-pass-number").css("display", "none");
			} else {
				$(".err-pass-number").css("display", "inline");
				$(".correct-pass-number").css("display", "none");
				isPasswordValid = false;
			}
		} else {
			clearPasswordComplexityStatus();
			isPasswordValid = false;
		}

		return isPasswordValid;
	}

    /**
     * Clears all notification of password complexity status
	 * 
     * @return void
     */
	function clearPasswordComplexityStatus () {
		
		$(".correct-pass-match").css("display", "none");
		$(".err-pass-match").css("display", "none");
		$(".err-pass-min").css("display", "none");
		$(".correct-pass-min").css("display", "none");
		$(".err-pass-lower").css("display", "none");
		$(".correct-pass-lower").css("display", "none");
		$(".err-pass-upper").css("display", "none");
		$(".correct-pass-upper").css("display", "none");
		$(".err-pass-char").css("display", "none");
		$(".correct-pass-char").css("display", "none");
		$(".err-pass-number").css("display", "none");
		$(".correct-pass-number").css("display", "none");
	}

	currUrl = window.location.href;
	if(currUrl.includes('employees/create')) {
		checkRequiredFields();
	}
    /**
     * Test if all fields with property: 'required' are not empty.
	 * Edit: Test if entries are valid
     * @param boolean entriesValid = DEFAULT true
	 * 
     * @return void
     */
	function checkRequiredFields(){
		let entriesValid = checkPasswordComplexity();

		var empty = false;
		$(":input[required]").each(function(){
			if($(this).val() == ''){
				empty = true;
			}
		})

		// If required inputs are empty OR if entries are not valid
		// entriesValid is utilized on: checkPasswordComplexity()
		if(empty || entriesValid === false){
			$("#emp-reg-submit").prop('disabled', true);
		}else{
			$("#emp-reg-submit").prop('disabled', false);
		}
	}

	$('.btn-prevent-multiple-submit').on('submit', function(){
		e.preventDefault()
		$('.btn-prevent-multiple-submit').prop('disabled', true);
	});

	
	function isSame_PermanentAndCurrentAddress () {
		var isSame = $("#copy-permanent-address").prop('checked');
		
		$("#cur-add-strt").prop("disabled", isSame);
		$("#cur-add-town").prop("disabled", isSame);
		$("#cur-add-prov").prop("disabled", isSame);
		$("#cur-add-postal").prop("disabled", isSame);
		if(isSame)
		{
			$("#cur-add-strt").addClass("is-disabled");
			$("#cur-add-town").addClass("is-disabled");
			$("#cur-add-prov").addClass("is-disabled");
			$("#cur-add-postal").addClass("is-disabled");
		
			$("#cur-add-strt").val($("#perm-add-strt").val());
			$("#cur-add-town").val($("#perm-add-town").val());
			$("#cur-add-prov").val($("#perm-add-prov").val());
			$("#cur-add-postal").val($("#perm-add-postal").val());
		} else {

			$("#cur-add-strt").removeClass("is-disabled");
			$("#cur-add-town").removeClass("is-disabled");
			$("#cur-add-prov").removeClass("is-disabled");
			$("#cur-add-postal").removeClass("is-disabled");
		}
		
	}

	
	isSame_PermanentAndCurrentAddress();

	$("#copy-permanent-address").click(function(){
		
		isSame_PermanentAndCurrentAddress();
		getCity("current",$("#perm-add-prov").val(),$("#perm-add-town").val());
	});


	$(".permanent-address").change(function(){
		var isSame = $("#copy-permanent-address").prop('checked');
		if(isSame) {
			$("#cur-add-strt").val($("#perm-add-strt").val());
			$("#cur-add-town").val($("#perm-add-town").val());
			$("#cur-add-prov").val($("#perm-add-prov").val());
			$("#cur-add-postal").val($("#perm-add-postal").val());
		}
		checkRequiredFields();
	})

	$(".permanent-address").keyup(function(){
		var isSame = $("#copy-permanent-address").prop('checked');
		if(isSame) {
			$("#cur-add-strt").val($("#perm-add-strt").val());
			$("#cur-add-town").val($("#perm-add-town").val());
			$("#cur-add-prov").val($("#perm-add-prov").val());
			$("#cur-add-postal").val($("#perm-add-postal").val());
		}
		checkRequiredFields();
	})

	//end for employee registration

	//start for employee details/request

	// Change password
	$("#cp-confirm-pw, #cp-new-pw").keyup(function(){
		checkPasswordComplexity("change_password");
	});

	$("#cp-confirm-pw, #cp-new-pw").change(function(){
		checkPasswordComplexity("change_password");
	});

	$('#cp-submit-btn').click(function(e){
		$("#cp-success-msg").empty();
		$("#current-pass-error").empty();
		$("#new-pass-error").empty();
		$("#confirm-pass-text").empty();
		$("#change_password_spinner").show();

		var postData = {
			_token: $("#changePasswordForm > input[name=_token]").val(),
			current_password: $("#cp-current-pw").val(),
			new_password: $("#cp-new-pw").val(),
			confirm_password: $("#cp-confirm-pw").val(),
			id: $("#changePasswordForm > input[name=cp_id]").val(),
		};
		
		$.ajax({
			type: "POST",
			url: CHANGE_PASSWORD_LINK,
			data: postData,
			dataType: "json",
			encode: true,
		}).done(function(data){
	
			if(!data.success){
				//display error
				var currentPasswordErrors = data.data.current_password;
				if(currentPasswordErrors && currentPasswordErrors.length > 0 ){
					$("#current-pass-error").addClass('text-danger text-start');
					currentPasswordErrors.forEach((value) => {
						$("#current-pass-error").append(value + "<br>");
					});
				}

				var newPasswordErrors = data.data.new_password;
				if(newPasswordErrors && newPasswordErrors.length > 0 ){
					$("#new-pass-error").addClass('text-danger text-start');
					newPasswordErrors.forEach((value) => {
						$("#new-pass-error").append(value + "<br>");
					});
				}
				
				var confirmPasswordErrors = data.data.confirm_password;
				if(confirmPasswordErrors && confirmPasswordErrors.length > 0 ){
					$("#confirm-pass-text").addClass('text-danger text-start');
					confirmPasswordErrors.forEach((value) => {
						$("#confirm-pass-text").append(value + "<br>");
					});
				}
			}else{
				$("#changePasswordForm").trigger('reset');
				$("#current-pass-error").empty();
				$("#new-pass-error").empty();
				
				setHeaderAlert("You have successfully changed your account password!", 1, true);
				setHeaderAlert("You have successfully changed your account password!", 1, true, "#changePasswordModal_HeaderAlert");
				clearPasswordComplexityStatus();
			}

		}).fail(function(){
			// console.log('error');
		}).always(() => {
			$("#change_password_spinner").hide();

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
				location.reload();
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
			// console.log('error: ' + msg);
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
				location.reload();
			}
		$("#ll-link-spinner").hide();
		$('#ll-submit-btn').prop('disabled', false);
		}).fail(function(){
			// console.log('error');
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
			// console.log('error');
		})
	});


	
	$("#emp-reg-submit").click(function(e){
		$("#employee-reg-submit-spinner").show();
		$("#emp-reg-submit").prop('disabled', true);
		$("#emp-reg-back").prop('disabled', true);
		$(".text-danger").hide();
		let isSame = $("#copy-permanent-address").prop('checked');
		if (isSame) {
			$("#cur-add-strt").prop("disabled", false);
			$("#cur-add-town").prop("disabled", false);
			$("#cur-add-prov").prop("disabled", false);
			$("#cur-add-postal").prop("disabled", false);
			$("#cur-add-strt").prop("readonly", true);
			$("#cur-add-town").prop("readonly", true);
			$("#cur-add-prov").prop("readonly", true);
			$("#cur-add-postal").prop("readonly", true);
		}
		
		$("#reg-form").submit();
	});

	$("#emp-update-submit").click(function(e){
		$("#emp-update-submit").prop('disabled', true);
		$(".text-danger").hide();

    	var isSame = $("#copy-permanent-address").prop('checked');
		if (isSame) {
			$("#cur-add-strt").prop("disabled", false);
			$("#cur-add-town").prop("disabled", false);
			$("#cur-add-prov").prop("disabled", false);
			$("#cur-add-postal").prop("disabled", false);
			$("#cur-add-strt").prop("readonly", true);
			$("#cur-add-town").prop("readonly", true);
			$("#cur-add-prov").prop("readonly", true);
			$("#cur-add-postal").prop("readonly", true);
		}
		
		$("#emp-update-form").submit();
	});

	$("#emp-reg-back").click(function(e){
		e.preventDefault();

		const response = confirm("Are you sure you want to return to Log-in page?\nYour changes will be lost.");

		if (response) {
			window.location.href = window.location.origin + "/login";
		}
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

    $("#perm-add-prov").on("change",function() {
    	var isSame = $("#copy-permanent-address").prop('checked');
    	getCity("permanent",$(this).val(),null,isSame);
    });

    $("#cur-add-prov").on("change",function() {
    	getCity("current",$(this).val());
    });
});