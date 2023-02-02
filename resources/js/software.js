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
					const status = [STATUS_REJECTED_TEXT, STATUS_APPROVED_TEXT, STATUS_PENDING_TEXT, STATUS_PENDING_APPROVAL_FOR_UPDATE_TEXT];
					const software_type = [SOFTWARE_TYPE_PRODUCTIVITY, SOFTWARE_TYPE_MESSAGING, SOFTWARE_TYPE_BROWSER, SOFTWARE_TYPE_UTIL, SOFTWARE_TYPE_PROJECT_SPECIFIC, SOFTWARE_TYPE_DRIVERS];
					const status_index =  software['approved_status']-1;
					const st_index = software['type']-1;


                    url = window.location.href+"/"+software['id'];
                    software_list.row.add(['<a href="'+url+'">'+software['software_name']+'</a>', software_type[st_index], status[status_index],software['reasons'],software['remarks']]).draw(false);
                });
            }
       });
    }



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
				$("#linkProjectForm").trigger('reset');
				$("#projectList > option[value=" + postData.project_id + "]").remove();
				$("#lp-success-msg").html('<i class="bi bi-check-circle-fill"></i>&nbsp;' + data.message + '.').addClass("text-success mb-2 text-start");

				//update projects table
				softprojectTable.clear().draw();
				data.update.forEach(function(project){
					let url = window.location.origin + '/devj_portal/projects/' + project.project_id;
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
		if($("#soft-reject-reason").val() == ""){
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

	//end for employee details/request

});