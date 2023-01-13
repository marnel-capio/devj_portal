
$(document).ready(function () {

	var software_list = $("#software-list").DataTable({
		"stateSave": true,
		"bFilter": false,
		"pageLength": 25,
		"oLanguage": {
	        "sEmptyTable": "There is no record found"
	    }
	});

    $(".soft-search-status-rdb-input").on("click", function(){
        filterSoftwareList();
    });

    $(".soft-search-filter-rdb-input").on("click", function(){
        filterSoftwareList();
    });

    $("#soft-search-input").on("input",function(){
        filterSoftwareList();
    });

    function filterSoftwareList() {
      var keyword = $("input[name='softSearchInput']").val();
      var status = $("input[name='softwareStatus']:checked").val();
        $.ajax({
            type:"get",
            url:"api/softwares/search",
            data :{
                    'keyword' : keyword , 
                    'status' : status ,   
                },          
            success:function(res){
                software_list.clear().draw();
                var result = JSON.parse(res);
                // console.log(result);
                result.forEach(function(software) {
                    var status = "";
                    if (software['approved_status'] == 1 || software['approved_status'] == 2 || software['approved_status'] == 4) {
                        status = "Deactivated";
                    } else {
                        status = "Pending for Approval";
                    } 
                    url = window.location.href+"/"+software['id'];
                    software_list.row.add(['<a href="'+url+'">'+software['software_name']+'</a>', software['type'], software['approved_status'],software['reasons'],software['remarks']]).draw(false);
                });
            }
       });
    }

	const LINK_PROJECT_LINK = '/api/linkProject'

	//start for software registration

	//disable submit button if not all required fields have value
	//softcheckRequiredFields();

	//$(":input[required]").change(function(){
	//	softcheckRequiredFields();
	//});


	//function softcheckRequiredFields(){
	//	var empty = false;
	//	$(":input[required]").each(function(){
	//		if($(this).val() == ''){
	//			empty = true;
	//		}
	//	})
	//	if(empty){
	//		$("#soft-reg-submit").prop('disabled', true);
	//	}else{
	//		$("#soft-reg-submitt").prop('disabled', false);
	//	}
	//}

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
				softprojectTable.clear().draw();
				data.update.forEach(function(project){
					let url = window.location.origin + '/devj_portal/projects/' + project.project_id;
					console.log(project);
					sDate = new Date(project.start_date);
					spanStart = sDate.getFullYear() + '/' + sDate.getMonth() + '/' + sDate.getDate() + ' - ';
					spanEnd = '';
					if(project.end_date != '' && project.end_date != null){
						eDate = new Date(project.end_date);
						spanEnd = eDate.getFullYear() + '/' + eDate.getMonth() + '/' + eDate.getDate();
					}
					softprojectTable.row.add([
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


    //reject modal
	$("#soft-reject-request-form").submit(function(){
		if($("soft-#reject-reason").val() == ""){
			console.log("hello");
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

});