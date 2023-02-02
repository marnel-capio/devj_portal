$(document).ready((function(){var t=$("#software-list").DataTable({stateSave:!0,bFilter:!1,pageLength:25,oLanguage:{sEmptyTable:"There is no record found"}});function a(){var e=$("input[name='softSearchInput']").val(),a=$("input[name='softwareStatus']:checked").val();$.ajax({type:"get",url:"api/softwares/search",data:{keyword:e,status:a},success:function(e){t.clear().draw(),JSON.parse(e).forEach((function(e){var a=["Denied","Approved","Pending Approval","Pending Update Approval"],r=["Productivity Tools","Messaging/Collaboration","Browser","System Utilities","Project Specific Softwares","Phone Drivers"],o=e.approved_status-1,s=e.type-1;url=window.location.href+"/"+e.id,t.row.add(['<a href="'+url+'">'+e.software_name+"</a>",r[s],a[o],e.reasons,e.remarks]).draw(!1)}))}})}$(".soft-search-status-rdb-input").on("click",(function(){a()})),$(".soft-search-filter-rdb-input").on("click",(function(){a()})),$("#soft-search-input").on("input",(function(){a()})),$(".btn-prevent-multiple-submit").on("submit",(function(t){e.preventDefault(),$(".btn-prevent-multiple-submit").prop("disabled",!0)}));var r=$("#project-tbl").DataTable({stateSave:!0,bFilter:!1,bPaginate:!1,bInfo:!1,oLanguage:{sEmptyTable:"No Data"}});function o(){8==$("#position").val()||9==$("#position").val()?($("#admin-check").hide(),$("#admin-detail").hide(),$("#is-admin").prop("disabled",!0)):($("#admin-check").show(),$("#admin-detail").show(),$("#is-admin").prop("disabled",!1))}$("#lp-submit-btn").click((function(e){$("#error-lp-proj-reason").empty(),$("#error-lp-proj-name").empty(),$("#lp-submit-btn").prop("disabled",!0);var t={_token:$("#linkProjectForm > input[name=_token]").val(),software_id:$("#linkProjectForm > input[name=lp_software_id]").val(),project_id:$("#projectList > option:selected").val(),remarks:$("#project_remarks").val()};$.ajax({type:"POST",url:"/api/softwarelinkProject",data:t,dataType:"json",encode:!0}).done((function(e){if(e.success)$("#linkProjectForm").trigger("reset"),$("#projectList > option[value="+t.project_id+"]").remove(),$("#lp-success-msg").html('<i class="bi bi-check-circle-fill"></i>&nbsp;'+e.message+".").addClass("text-success mb-2 text-start"),r.clear().draw(),e.update.forEach((function(e){var t=window.location.origin+"/devj_portal/projects/"+e.project_id;sDate=new Date(e.start_date),spanStart=sDate.getFullYear()+"/"+sDate.getMonth()+"/"+sDate.getDate()+" - ",spanEnd="",""!=e.end_date&&null!=e.end_date&&(eDate=new Date(e.end_date),spanEnd=eDate.getFullYear()+"/"+eDate.getMonth()+"/"+eDate.getDate()),r.row.add(['<a href="'+t+'" class="text-decoration-none">'+e.name+"</a>",spanStart+spanEnd,e.project_status]).draw(!1)}));else{$("#lp-success-msg").empty();var a=e.data.project_id;a&&a.length>0&&$("#error-lp-proj-name").html(a[0]).addClass("text-danger text-start");var o=e.data.remarks;o&&o.length>0&&$("#error-lp-proj-reason").html(o[0]).addClass("text-danger text-start")}$("#lp-submit-btn").prop("disabled",!1)})).fail((function(){console.log("error")})),e.preventDefault()})),$("#soft-reject-request-form").submit((function(){return""==$("#soft-reject-reason").val()?(console.log("hello"),$("#soft-reject-reason-error").html("The reason field is required.").addClass("text-danger text-start"),!1):$("#soft-reject-reason").val().length>1024?($("#soft-reject-reason-error").html("The reason must not be greater than 1024 characters.").addClass("text-danger text-start"),!1):void $("#soft-reject-sub").prop("disabled",!0)})),$("#soft-approve-request-form").submit((function(){$("#approve-request").prop("disabled",!0)})),$("#soft-update-form").submit((function(){$("#active-status").is(":checked")&&$("#active-status-hidden").prop("disabled",!0),$("#server-manage-flag").is(":checked")&&$("#server-manage-flag-hidden").prop("disabled",!0),$("#soft-update-submit").prop("disabled",!0)})),$("#linkProjectModal").on("hidden.bs.modal",(function(){$("#lp-success-msg").empty(),$("#error-lp-proj-reason").empty(),$("#error-lp-proj-name").empty()})),o(),$("#position").change((function(){o()}))}));