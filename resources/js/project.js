const LINK_EMPLOYEE_PROJECT_LINK = '/api/linkEmployeeToProject';
const LINK_SOFTWARE_TO_PROJECT_LINK = '/api/linkSoftwareToProject';
const UPDATE_EMPLOYEE_PROJECT_LINKAGE_LINK = '/api/updateEmployeeProjectLinkage';


const PROJECT_ROLES = [
						'Team Lead',
						'Programmer',
						'QA',
					];
const EMPLOYEE_ROLE_ADMIN = 1;
const EMPLOYEE_ROLE_MANAGER = 2;

$(document).ready( function () {

    let pj_table = $("#proj_members_tbl").DataTable({
		"pageLength": 10,
        "ordering": false,
        "oLanguage": {
	        "sEmptyTable": "No Data",
            "sZerorRecords": "No Data",
            "sInfoFiltered": ""
	    },
        "columnDefs" : [
            {
                "targets": [5],
                "visible": false
            }
        ],
        "sDom": "lrt<'#bottom.row'<'#info.col'i><'#pagination.col'p>>",
	});

	// ===================================== Project Members ==========================================
    hideShowPJHistory();
	
	
	// Create button is clicked
	$("#create-project").on("click", function() {
		$("#create-project-spinner").show();

	});

	
	
	// Submit create project button is clicked
	$("#project-reg-submit").on("click", function() {
		$("#project-reg-submit-spinner").show();

	});

    $("#show_hist").on('change', function () {
        hideShowPJHistory();
    });

    function hideShowPJHistory () {
        pj_table.column(5)
                .search($('#show_hist').is(":checked") ? '' : '1')
                .draw();
    }

    
    $("#link_request_tbl").DataTable({
		"pageLength": 10,
        "ordering": false,
        "oLanguage": {
	        "sEmptyTable": "No Data"
	    },
        "sDom": "lrt<'#bottom.row'<'#info.col'i><'#pagination.col'p>>",
	});

    let s_table = $("#linked_softwares_tbl").DataTable({
		"pageLength": 10,
        "ordering": false,
        "oLanguage": {
	        "sEmptyTable": "No Data"
	    },
        "sDom": "lrt<'#bottom.row'<'#info.col'i><'#pagination.col'p>>",
	});

    
    // Link Project to Employee submission

    $("#pj_submit_btn").click( function (e) {
        $("#link_create_spinner").show();

		var postData = {
			_token: $("#link_employee_form > input[name=_token]").val(),
			employee_id: $("#member_list > option:selected").val(),
			project_id: $("#link_employee_form > input[name=project_id]").val(),
			project_start: $("#link_project_start").val(),
			project_end: $("#link_project_end").val(),
			project_role: $("#link_role > option:selected").val(),
			project_onsite: $("#link_onsite").is(':checked') ? 1 : 0,
			remarks: $("#link_remarks").val(),
			is_employee: true,

			// Role of Logged-in User
			employee_role: $("#link_employee_form > input[name=employee_role").val(),
		};

		$.ajax({
			type: "POST",
			url: LINK_EMPLOYEE_PROJECT_LINK,
			data: postData,
			dataType: "json",
			encode: true,
		}).done(function(data){
            $("#link_create_spinner").hide();

			// display error
			if(!data.success){
				$("#le_success_msg").empty();

                // Remove error messages
                $("#link_employee_form").find("[name]").each( function () {
                    if ($('#link_employee_form  #link_' + $(this).attr('name') + '_error').length > 0 ) {
                        $('#link_employee_form  #link_' + $(this).attr('name') + '_error').empty();
                    }
                });

                // Display error message
                for (key in data.data) {
                    $('#link_employee_form #link_'  + key + '_error').html(data.data[key][0]).addClass('text-danger text-start');
                }

			}else{
                location.reload();
			}

		}).fail(function (ddata, exception) {
			var msg = '';
			if (ddata.status === 0) {
				msg = 'Not connect.\n Verify Network.';
			} else if (ddata.status == 404) {
				msg = 'Requested page not found. [404]';
			} else if (ddata.status == 500) {
				msg = 'Internal Server Error [500].';
			} else if (exception === 'parsererror') {
				msg = 'Requested JSON parse failed.';
			} else if (exception === 'timeout') {
				msg = 'Time out error.';
			} else if (exception === 'abort') {
				msg = 'Ajax request aborted.';
			} else {
				msg = 'Uncaught Error.\n' + ddata.responseText;
			}
			// console.log('error: ' + msg);
		});

        e.preventDefault();
    })

	$('#link_employee_modal').on('hidden.bs.modal', function(){
		$("#le_success_msg").empty();

		// Remove error messages
		$("#link_employee_form").find("[name]").each( function () {
			if ($('#link_employee_form  #link_' + $(this).attr('name') + '_error').length > 0 ) {
				$('#link_employee_form  #link_' + $(this).attr('name') + '_error').empty();
			}
		});
	});

	/**
	 * Updates the Project Members table after Add Member or Update Linkage of Existing Member
	 * 
	 * @param {object} data 
	 * @param {int} employee_id 
	 * @param {int} employee_role
	 * @returns 
	 */
	function updateProjectMemberTable (data, employee_id, employee_role) {
		
		// Clear and Reload Projects table to update
		pj_table.clear().draw();
		let url, newRow, modalData;
		data.update.forEach(function(memberData){
			url = window.location.origin + '/employees/' + memberData.employee_id;
			modalData = {
				id: memberData.id,
				member: memberData.member_name_update,
				start_date: getDate(memberData.start_date),
				end_date: getDate(memberData.end_date),
				onsite_flag: memberData.onsite_flag,
				project_role_type: memberData.project_role_type,
				remarks: memberData.remarks
			};

			newRow = [
				'<a href="' + url + '" class="text-decoration-none">' + memberData.member_name + '</a>',
				PROJECT_ROLES[Number(memberData.project_role_type) - 1],
				memberData.onsite_flag ? 'Yes' : 'No',
				memberData.membership_date
			];

			if (memberData.isActive && (employee_id == memberData.employee_id || employee_role == EMPLOYEE_ROLE_MANAGER || employee_role == EMPLOYEE_ROLE_ADMIN)) {
				// Add update button
				newRow.push('<button class="btn btn-link btn-sm text-success employee_linkage_update_btn" \
									 data-bs-target="#update_employee_linkage_modal" data-bs-toggle="modal" \
									 data-modaldata=\'' + JSON.stringify(modalData) + '\'>Update</button>');
			} else {
				newRow.push('');
			}

			newRow.push(memberData.isActive);
			pj_table.row.add(newRow).draw(false);
		});

		$("#proj_members_tbl > tbody > tr").each(function () {
			// Fix text alignment of remove button
			$(this).find(':nth-last-child(1)').addClass('text-center');
		});
	}
	
	/**
	 * returns the formatted date in YYYY-mm-dd
	 * 
	 * @param {string} date 
	 * @param {string} separator 
	 * @returns 
	 */
	function getDate(date, separator = '-') {
		if (date == null || date == '') {
			return '';
		}
		date = new Date(date);
		let month = date.getMonth() + 1;
		if (month.toString().length == 1) {
			month = '0' + month;
		}
		let day = date.getDate();
		if (day.toString().length == 1) {
			day = '0' + day;
		}
		return date.getFullYear() + separator + month + separator + day;
	}

	// Update linkage submission
	$("#update_pj_submit_btn").click( function (e) {
		$("#update_pj_submit_btn").prop("disabled", true);
		$("#link_update_spinner").show();

		var postData = {
			_token: $("#update_employee_linkage_form > input[name=_token]").val(),
			linkage_id: $("#update_employee_linkage_form input[name=linkage_id]").val(),
			project_start: $("#update_employee_linkage_form input[name=project_start]").val(),
			project_end: $("#update_employee_linkage_form input[name=project_end]").val(),
			project_role: $("#update_link_role > option:selected").val(),
			project_onsite: $("#update_employee_linkage_form  input[name=onsite]").is(':checked') ? 1 : 0,
			remarks: $("#update_link_remarks").val(),
			is_employee_update: true,
			
			// Role of Logged-in User
			employee_role: $("#link_employee_form > input[name=employee_role").val(),
		};
		
		$("#update_employee_linkage_form #link_project_start_error").empty();
		$("#update_employee_linkage_form #link_project_end_error").empty();
		

		$.ajax({
			type: "POST",
			url: UPDATE_EMPLOYEE_PROJECT_LINKAGE_LINK,
			data: postData,
			dataType: "json",
			encode: true,
		}).done(function(data){

			// display error
			if(!data.success){
				$("#ue_success_msg").empty();

				// Remove error messages
				$("#update_employee_linkage_form").find("[name]").each( function () {
					if ($('#update_employee_linkage_form #link_' + $(this).attr('name') + '_error').length > 0 ) {
						$('#update_employee_linkage_form #link_' + $(this).attr('name') + '_error').empty();
					}
				});

				// Display error message
				for (key in data.data) {
					$('#update_employee_linkage_form #link_'  + key + '_error').html(data.data[key][0]).addClass('text-danger text-start');
				}

			}else{
				location.reload();
			}

		}).fail(function (ddata, exception) {
			var msg = '';
			if (ddata.status === 0) {
				msg = 'Not connect.\n Verify Network.';
			} else if (ddata.status == 404) {
				msg = 'Requested page not found. [404]';
			} else if (ddata.status == 500) {
				msg = 'Internal Server Error [500].';
			} else if (exception === 'parsererror') {
				msg = 'Requested JSON parse failed.';
			} else if (exception === 'timeout') {
				msg = 'Time out error.';
			} else if (exception === 'abort') {
				msg = 'Ajax request aborted.';
			} else {
				msg = 'Uncaught Error.\n' + ddata.responseText;
			}
			// console.log('error: ' + msg);
		}).always(function() {
			$("#update_pj_submit_btn").prop("disabled", false);
			$("#link_update_spinner").hide();
		});

		e.preventDefault();
	});

	$('#update_employee_linkage_modal').on('hidden.bs.modal', function(){
		$("#ue_success_msg").empty();

		// Remove error messages
		$("#update_employee_linkage_form").find("[name]").each( function () {
			if ($('#update_employee_linkage_form > #link_' + $(this).attr('name') + '_error').length > 0 ) {
				$('#update_employee_linkage_form > #link_' + $(this).attr('name') + '_error').empty();
			}
		});
	});

    // Link software submission
    $("#ls_submit_btn").click( function (e) {
        $("#link_software_spinner").show();

		var postData = {
			_token: $("#link_software_form > input[name=_token]").val(),
			software_id: $("#software_list > option:selected").val(),
			project_id: $("#link_software_form > input[name=project_id]").val(),
			remarks: $("#link_software_remarks").val()
		};

		$.ajax({
			type: "POST",
			url: LINK_SOFTWARE_TO_PROJECT_LINK,
			data: postData,
			dataType: "json",
			encode: true,
		}).done(function(data){
            $("#link_software_spinner").hide();

			// display error
			if(!data.success){
				$("#ls_success_msg").empty();

                // Remove error messages
                $("#link_software_form").find("[name]").each( function () {
                    if ($('#link_software_form #link_' + $(this).attr('name') + '_error').length > 0 ) {
                        $('#link_software_form #link_' + $(this).attr('name') + '_error').empty();
                    }
                });

                // Display error message
                for (key in data.data) {
                    $('#link_software_form #link_'  + key + '_error').html(data.data[key][0]).addClass('text-danger text-start');
                }

			}else{
                location.reload();
			}

		}).fail(function(){
			// console.log('error');
		});

        e.preventDefault();
    });

	$('#link_software_modal').on('hidden.bs.modal', function(){
		$("#ls_success_msg").empty();

		// Remove error messages
		$("#link_software_form").find("[name]").each( function () {
			if ($('#link_software_form #link_' + $(this).attr('name') + '_error').length > 0 ) {
				$('#link_software_form #link_' + $(this).attr('name') + '_error').empty();
			}
		});
	});

	// Software linkage removal
	$("#linked_softwares_tbl").on('click', '.software_linkage_remove_btn', function (e) {
		let linkageId =  $(this).data('linkid');
		let softwareName = $(this).data('softwarename');

		$("#remove_software_spinner_" + linkageId).show();
		if (confirm('Are sure you want to remove ' + softwareName + '?')) {
			// Set id in software removal form
			$("#remove_software_form > input[name=id").val(linkageId);
		} else {
			// Cancel form submission
			return false;
		}

	});

	$("#proj_members_tbl").on('click', '.employee_linkage_update_btn', function () {
		// Render data on update modal
		let linkageData = $(this).data('modaldata');

		$("#member_info").text('Member: ' + linkageData.member);
		$("#update_employee_linkage_form input[name=linkage_id]").val(linkageData.id);
		$("#update_employee_linkage_form input[name=project_start]").val(linkageData.start_date);
		$("#update_employee_linkage_form input[name=project_end]").val(linkageData.end_date);
		$("#update_employee_linkage_form select").val(linkageData.project_role_type);

		if (linkageData.onsite_flag) {
			$("#update_employee_linkage_form  input[name=onsite]").prop('checked', true);
		}

		$("#update_employee_linkage_form textarea").text(linkageData.remarks);
	});


	var project_list = $("#project-list").DataTable({
		"stateSave": true,
		"bFilter": false,
		"pageLength": 25,
		"oLanguage": {
	        "sEmptyTable": "There is no record found"
	    }
	});


    $(".project-search-status-rdb-input").on("click", function(){
        filterProjectList();
    });

    $("#proj-search-input").on("input",function(){
        filterProjectList();
    });

    function filterProjectList() {
      var keyword = $("input[name='projSearchInput']").val();
      var status = $("input[name='projectStatus']:checked").val();
	  $.ajax({
            type:"get",
            url:"api/projects/search",
            data :{
                    'keyword' : keyword , 
                    'status' : status ,  
                }, 
		}).done(function(data){
			if(data.success){
				project_list.clear().draw();

				data.update.forEach(function(project){
					// Get only YYYY-MM-DD from date
					let start_date ="";
					let end_date ="";
					if(project['start_date'])
					{
						if(project['start_date'] !== ""){
							sd = new Date(project['start_date']);
							sd.setHours(sd.getHours() + 8); // JavaScript converts date to +0:00. Add 8 Hrs to convert to PHT (+8:00)
							start_date = sd.toISOString().slice(0, 10);
						}
					}
					if(project['end_date'])
					{
						if(project['end_date'] !== ""){
							ed = new Date(project['end_date']);
							ed.setHours(ed.getHours() + 8); // JavaScript converts date to +0:00. Add 8 Hrs to convert to PHT (+8:00)
							end_date = ed.toISOString().slice(0, 10);
						}
					}

                    url = window.location.href+"/"+project['id'];
                    project_list.row.add([
						'<a href="'+url+'">'+ project['name']+'</a>', 
						start_date,
						end_date,
						project['status']])
						.draw(false);
				});
			}
		}).fail(function(){
			// console.log('error');
		});	  
    }
	
	// Approve button for Approve Project Link Request
    $(".approve-link-btn").click(function(){
        var linkId = $(this).data('linkid');
		$("#approve_btn_spinner_" + linkId).show();
        $("#approve-link-in").val(linkId);
    });

	// Reject button for Reject Project Link Request
    $(".reject-link-btn").click(function(){
        var linkId = $(this).data('linkid');
		$("#reject_btn_spinner_" + linkId).show();
        $("#reject-link-in").val(linkId);
    });

    // On modal close,
    $("#rejectLinkageRequestModal").on("hidden.bs.modal", function() {
        $(".spinner-border").hide();
    });

    // Reject modal
	$("#reject-request-form").submit(function(){
		if($("#reject-reason").val() == ""){
			$("#reject-reason-error").html('The reason field is required.').addClass("text-danger text-start");
			return false;
		}else if($("#reject-reason").val().length > 1024){
			$("#reject-reason-error").html('The reason must not be greater than 1024 characters.').addClass("text-danger text-start");
			return false;
		}else {
			$('#reject-sub').prop('disabled', true);
			$('#reject-sub-spinner').show();
		}
	});


});
