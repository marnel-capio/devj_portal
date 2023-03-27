const LINK_EMPLOYEE_PROJECT_LINK = '/api/linkEmployeeToProject';
const PROJECT_ROLES = [
						'Team Lead',
						'Programmer',
						'QA',
					];

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

//=====================================Project Members==========================================
    hideShowPJHistory();
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

    $("#linked_softwares_tbl").DataTable({
		"pageLength": 10,
        "ordering": false,
        "oLanguage": {
	        "sEmptyTable": "No Data"
	    },
        "sDom": "lrt<'#bottom.row'<'#info.col'i><'#pagination.col'p>>",
	});

    
    //link project submission
    $("#pj_submit_btn").click( function (e) {
        $("#link_update_spinner").show();

		var postData = {
			_token: $("#link_employee_form > input[name=_token]").val(),
			employee_id: $("#member_list > option:selected").val(),
			project_id: $("#link_employee_form > input[name=project_id]").val(),
			project_start: $("#link_project_start").val(),
			project_end: $("#link_project_end").val(),
			project_role: $("#link_role > option:selected").val(),
			project_onsite: $("#link_onsite").is(':checked') ? 1 : 0,
			remarks: $("#link_remarks").val()
		};

		$.ajax({
			type: "POST",
			url: LINK_EMPLOYEE_PROJECT_LINK,
			data: postData,
			dataType: "json",
			encode: true,
		}).done(function(data){
            $("#link_update_spinner").hide();

			// display error
			if(!data.success){
				$("#le_success_msg").empty();

                //remove error messages
                $("#link_employee_form").find("[name]").each( function () {
                    if ($('#link_' + $(this).attr('name') + '_error').length > 0 ) {
                        $('#link_' + $(this).attr('name') + '_error').empty();
                    }
                });

                //display error message
                for (key in data.data) {
                    console.log(data.data[key][0]);
                    $('#link_'  + key + '_error').html(data.data[key][0]).addClass('text-danger text-start');
                }

			}else{
                //reset form
				$("#link_employee_form").trigger('reset');
                //remove error messages
                $("#link_employee_form").find("[name]").each( function () {
                    console.log($(this).val());
                    if ($('#link_' + $(this).attr('name') + '_error').length > 0 ) {
                        $('#link_' + $(this).attr('name') + '_error').empty();
                    }
                });


				$("#projectList > option[value=" + postData.project_id + "]").remove();
				$("#le_success_msg").html('<i class="bi bi-check-circle-fill"></i>&nbsp;' + data.message + '.').addClass("text-success mb-2 text-start");

				//update projects table
				pj_table.clear().draw();
				data.update.forEach(function(memberData){
					let url = window.location.origin + '/employees/' + memberData.employee_id;
					pj_table.row.add([
						'<a href="' + url + '" class="text-decoration-none">' + memberData.member_name + '</a>',
						PROJECT_ROLES[Number(memberData.project_role_type) - 1],
						memberData.onsite_flag ? 'Yes' : 'No',
						memberData.membership_date,
						'Update',	//==============NEED TO UPDATE/ADD DATA FOR LINKAGE UPDATE
						memberData.isActive
					])
					.draw(false);
				});
			}

		}).fail(function(){
			console.log('error');
		});

        e.preventDefault();
    })


});