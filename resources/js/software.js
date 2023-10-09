const STATUS_REJECTED_TEXT = "Denied";
const STATUS_APPROVED_TEXT = "Approved";
const STATUS_PENDING_TEXT = "Pending Approval";
const STATUS_PENDING_APPROVAL_FOR_UPDATE_TEXT = "Pending Update Approval";

const SOFTWARE_TYPE_PRODUCTIVITY = "Productivity Tools";
const SOFTWARE_TYPE_MESSAGING = "Messaging/Collaboration";
const SOFTWARE_TYPE_BROWSER = "Browser";
const SOFTWARE_TYPE_UTIL = "System Utilities";
const SOFTWARE_TYPE_PROJECT_SPECIFIC = "Project Specific Softwares";
const SOFTWARE_TYPE_DRIVERS = "Phone Drivers";

const SOFTWARE_CREATE_LINK = '/softwares/create'
const SOFTWARE_REGIST_LINK = '/softwares/regist'
const SOFTWARE_DOWNLOAD_LINK = '/softwares/download'
const LINK_PROJECT_LINK = '/api/softwarelinkProject'


$(document).ready(function () {

	var software_list = $("#software-list").DataTable({
		"stateSave": true,
		"bFilter": false,
		"pageLength": 25,
		"oLanguage": {
	        "sEmptyTable": "There is no record found"
	    }
	});
	
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

	// Software deletiom
	$("#software-delete").on('click', function (e) {
		e.preventDefault();
		if (confirm('Are sure you want to delete?')) {
			var id = $("#software_id").val();
			window.location.href = window.location.origin+"/softwares/"+id+"/delete";
		} else {
			// Cancel form submission
			return false;
		}

	});
	
	// Create button is clicked
	$("#create-software").on("click", function() {
		$("#create-software-spinner").show();
		$.ajax({
            type:"get",
            url: SOFTWARE_CREATE_LINK,
		}).done(function(){
			$("#create-software-spinner").hide();
			window.location.href = SOFTWARE_CREATE_LINK;
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
			$("#create-software-spinner").hide();
			$("#create-software").prop("disabled", false);
			setTimeout(function(){
				location.reload(true);
			}, 5000);

		});
	});
	

	// Download button is clicked
	$("#software-download").on("click", function() {
		setHeaderAlert("Requesting download software list", 2, true);
		$("#software-download-spinner").show();
		$("#software-download").prop("disabled", true);


		var postData = {
			_token: $("#software-list-form > input[name=_token]").val()
		};
		$.ajax({
			type: "POST",
			url: SOFTWARE_DOWNLOAD_LINK,
			data: postData,
			encode: true,
		}).done(function(){
			$("#software-download-form").submit();
			setTimeout(function(){
				setHeaderAlert("Download request sent", 1, true);
				$("#software-download-spinner").hide();
				$("#software-download").prop("disabled", false);
				location.reload(true);
			}, 1500);
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
			$("#software-download-spinner").hide();
			$("#software-download").prop("disabled", false);
			setTimeout(function(){
				location.reload(true);
			}, 5000);

		});

	});


    $(".soft-search-status-rdb-input").on("click", function(){
        filterSoftwareList();
    });

    $(".soft-search-type-rdb-input").on("change", function(){
		// console.log("entered");
        filterSoftwareList();
    });

    $("#soft-search-input").on("input",function(){
        filterSoftwareList();
    });

    function filterSoftwareList() {
      var keyword = $("input[name='softSearchInput']").val();
      var status = $("input[name='softwareStatus']:checked").val();
	  var type = $("#software_type").val();
	  $.ajax({
            type:"get",
            url:"api/softwares/search",
            data :{
                    'keyword' : keyword , 
                    'status' : status ,  
					'type' :  type,
                }, 
		}).done(function(data){
			if(data.success){
				software_list.clear().draw();

				data.update.forEach(function(software){
					const status = [STATUS_REJECTED_TEXT, STATUS_APPROVED_TEXT, STATUS_PENDING_TEXT, STATUS_PENDING_APPROVAL_FOR_UPDATE_TEXT];
					const status_index =  software['approved_status']-1;

					//get only YYYY-MM-DD from date
					let createdate ="";
					let update_date ="";
					let approve_date ="";
					if(software['create_time'])
					{
						if(software['create_time'] !== ""){
							createdate = new Date(software['create_time']).toISOString().slice(0, 10);
						}
					}
					if(software['update_time'])
					{
						if(software['update_time'] !== ""){
							update_date = new Date(software['update_time']).toISOString().slice(0, 10);
						}
					}
					if(software['approve_time'])
					{
						if(software['approve_time'] !== ""){
							approve_date = new Date(software['approve_time']).toISOString().slice(0, 10);
						}					
					}

                    url = window.location.href+"/"+software['id'];
                    software_list.row.add([
						'<a href="'+url+'">'+software['software_name']+'</a>', 
						software['type'], 
						status[status_index],
						software['reasons'],
						software['remarks'], 
						createdate, 
						update_date, 
						approve_date])
						.draw(false);
				});
			}
		}).fail(function(){
			// console.log('error');
		});	  
    }



	$('.btn-prevent-multiple-submit').on('submit', function($e){
		e.preventDefault()
		$('.btn-prevent-multiple-submit').prop('disabled', true);
	});

	//end for software registration

	//start for software details/request

	//link project
	var softprojectTable = $("#project-tbl").DataTable({
		"stateSave": true,
		"bFilter": false,
		"bPaginate": false,
		"bInfo": false,
		"oLanguage": {
	        "sEmptyTable": "No Data"
	    }
	});

	$("#soft-reg-submit").click(function(){
		$("#software-reg-form").submit();
		$('#soft-reg-submit').prop('disabled', true);
		$("#soft-reg-submit-spinner").show();
	});

	// Show Spinner for: Reject Software Request
	$("#soft-reject-request").click(function(){
		$("#soft-reject-request-spinner").show();
	});

	// Show Spinner for: Approve Software Request
	$("#soft-approve-request").click(function(){
		$('#soft-approve-request').prop('disabled', true);
		$("#soft-approve-request-spinner").show();
		$("#soft-approve-request-form").submit();
	});

	// Show Spinner for: Submit the Software Reject
	$("#soft-reject-sub").click(function() {
		$("#soft-reject-sub-spinner").show();
	});

	
    // On modal close,
    $("#softwarerejectRequestModal").on("hidden.bs.modal", function() {
        $(".spinner-border").hide();
    });

	$("#lp-submit-btn").click(function(e){
		$("#lp-submit-btn-spinner").show();
		$("#error-lp-proj-reason").empty();
		$("#error-lp-proj-name").empty();
		$('#lp-submit-btn').prop('disabled', true);

		var postData = {
			_token: $("#linkProjectForm > input[name=_token]").val(),
			software_id: $("#linkProjectForm > input[name=lp_software_id]").val(),
			project_id: $("#projectList > option:selected").val(),
			remarks: $("#project_remarks").val(),
		};
		$.ajax({
			type: "POST",
			url: LINK_PROJECT_LINK,
			data: postData,
			dataType: "json",
			encode: true,
		}).done(function(data){
			$("#lp-submit-btn-spinner").hide();
			// display error
			if(!data.success){
				$("#lp-success-msg").empty();
				var projectError = data.data.project_id;
				if(projectError && projectError.length > 0 ){
					$("#error-lp-proj-name").html(projectError[0]).addClass('text-danger text-start');
				}

				var projectResonError = data.data.remarks;
				if(projectResonError && projectResonError.length > 0 ){
					$("#error-lp-proj-reason").html(projectResonError[0]).addClass('text-danger text-start');
				}
			}else{
				location.reload();
			}
			$('#lp-submit-btn').prop('disabled', false);
		}).fail(function(){
			//console.log('error');
		});
		
		e.preventDefault();
	});


    //reject modal
	$("#soft-reject-request-form").submit(function(){
		if($("#soft-reject-reason").val() == ""){
			$("#soft-reject-reason-error").html('The reason field is required.').addClass("text-danger text-start");
			return false;
		}else if($("#soft-reject-reason").val().length > 1024){
			$("#soft-reject-reason-error").html('The reason must not be greater than 1024 characters.').addClass("text-danger text-start");
			return false;
		}else {
			$('#soft-reject-sub').prop('disabled', true);
		}
	});

	//approve
	$("#soft-approve-request-form").submit(function(){
		$('#approve-request').prop('disabled', true);
	})

	// edit submit
	$("#soft-update-form").submit(function(){
		if($("#active-status").is(':checked')){
			$("#active-status-hidden").prop('disabled', true);
		}
		if($("#server-manage-flag").is(':checked')){
			$("#server-manage-flag-hidden").prop('disabled', true);
		}
		$('#soft-update-submit').prop('disabled', true);
	});

	$('#linkProjectModal').on('hidden.bs.modal', function(){
		$("#lp-success-msg").empty();
		$("#error-lp-proj-reason").empty();
		$("#error-lp-proj-name").empty();
	});



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

	$("#software_type_id").on('change', function () {
        if($(this).val() == 999){
            $("#new_software_type").prop('hidden', false);
			$("#new_software_type").prop('disabled', false)
			$("#new_software_type").prop('required', false)
			$("#new_software_type_label").prop('hidden', false);
			$("#new_software_type_error").prop('hidden', false);
        }else{
            $("#new_software_type").prop('hidden', true);
            $("#new_software_type").prop('disabled', true);
			$("#new_software_type").prop('required', true)
            $("#new_software_type_label").prop('hidden', true);
			$("#new_software_type_error").prop('hidden', true);		
        }
    });
	//end for software details/request

});