const FILTER_LAPTOP_LINK = '/api/laptops/search';
const UPDATE_LINK = '/api/laptops/update';
const UPDATE_LINKAGE_LINK = '/api/laptops/updateLinkage';
const REGISTER_LINKAGE_LINK = '/api/laptops/registLinkage';

const LAPTOP_CREATE_LINK = '/laptops/create'
const LAPTOP_DOWNLOAD_LINK = '/laptops/download'

$(document).ready(function(){

    //laptop list
    var laptopList = $("#laptop-list").DataTable({
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


	// Create button is clicked
	$("#create-laptop").on("click", function() {
        $("#create-laptop-spinner").show();

		$.ajax({
            type:"get",
            url: LAPTOP_CREATE_LINK,
		}).done(function(){
			$("#create-laptop-spinner").hide();
			window.location.href = LAPTOP_CREATE_LINK;
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
			$("#create-laptop-spinner").hide();
			$("#create-laptop").prop("disabled", false);
			setTimeout(function(){
				location.reload(true);
			}, 5000);

		});
	});

	// Download button is clicked
	$("#laptop-download").on("click", function() {
        
		setHeaderAlert("Requesting download current list", 2, true);
		$("#laptop-download-spinner").show();
		$("#laptop-download").prop("disabled", true);


		var postData = {
			_token: $("#laptop-list-form > input[name=_token]").val(),
            laptopAvailability: $('input[name="laptopAvailability"]:checked').val(),
            laptopStatus: $('input[name="laptopStatus"]:checked').val(),
            searchFilter: $('input[name="searchFilter"]:checked').val(),
            searchInput: $('input[name="searchInput"]').val(),
		};

		$.ajax({
			type: "POST",
			url: LAPTOP_DOWNLOAD_LINK,
			data: postData,
			encode: true,
		}).done(function(){
            $("#laptop-list-form").submit();
            setHeaderAlert("Download request sent", 1, true);
			setTimeout(function(){
				setHeaderAlert("Download request sent", 1, true);
				$("#laptop-download-spinner").hide();
				$("#laptop-download").prop("disabled", false);
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
			$("#laptop-download-spinner").hide();
			$("#laptop-download").prop("disabled", false);
			setTimeout(function(){
				location.reload(true);
			}, 5000);

		});


	});

    $(".laptop-search-availability").click(function(){
        filterLaptopList();
    })
    $(".laptop-status").click(function(){
        filterLaptopList();
    })
    $(".laptop-filter").click(function(){
        filterLaptopList();
    })
    $("#search-input").on('input', function(){
        filterLaptopList();
    })
    

    function filterLaptopList(){

        $.ajax({
            type: "GET",
            url: FILTER_LAPTOP_LINK,
            data: {
                keyword: $('input[name=searchInput]').val(),
                availability: $('input[name=laptopAvailability]:checked').val(),
                status: $('input[name=laptopStatus]:checked').val(),
                searchFilter: $('input[name=searchFilter]:checked').val(),
            },
            dataType: "json",
            encode: true
        }).done(function(data){
            if(data.success){
                laptopList.clear().draw();

                data.update.forEach(function(laptop){
                    laptopList.row.add([
                        '<a href="' + window.location.href + '/' + laptop.id + '">' + laptop.tag_number + '</a>',
                        laptop.laptop_make,
                        laptop.laptop_model,
                        laptop.laptop_cpu,
                        laptop.laptop_clock_speed,
                        laptop.laptop_ram,
                        laptop.owner,
                        laptop.status
                    ])
                    .draw(false);
                });
            }
        }).fail(function(){
            // console.log('error');
        });
    }

    function setFormDataToJson (formSelector) {
        const formData = formSelector.serializeArray(); 
        const jsonData = {};
        formData.forEach(function(data){
            jsonData[data.name] = data.value;
        });
        return jsonData;
    }
    

    $("#edit-form").submit(function(e){
        $("#el-spinner").show();
        $("#el-submit-btn").prop('disabled', true);

        const jsonData = setFormDataToJson($(this));

        if($("#edit-form input[name=status").length){
            jsonData.status = $("#edit-form input[name=status").is(':checked') ? 1 : 0;
        }
        
        $.ajax({
            type: "POST",
            url: UPDATE_LINK,
            data: jsonData,
            dataType: "json",
            encode: true,
        })
        .done(function(data){
            for(var key in jsonData){
                $("#" + key + "-error").empty();
            }
            if(!data.success){
                //display errors
                errors = data.data;
                for(var key in errors){
                    $("#" + key + "-error").html(errors[key][0]);
                }
                $("#el-submit-btn").prop('disabled', false);
                $("#el-spinner").hide();
            }else{
                location.reload();
            }

        }).fail(function(){
            // console.log('error');
        })


		e.preventDefault();
        
    });

    var linkRequestTable = $("#link-req-tbl").DataTable({
		"stateSave": true,
        "bFilter": false,
        "ordering": false,
		"pageLength": 10,
		"oLanguage": {
	        "sEmptyTable": "No Data"
	    }
	});

    $("#update-linkage-form").submit(function(e){
        $("#ul-submit-btn").prop('disabled', true);
        $("#link-update-spinner").show();

        const jsonData = setFormDataToJson($(this)); //try using this

        jsonData.brought_home_flag = $("#ul-brought-home").is(':checked') ? 1 : 0;
        jsonData.vpn_flag = $("#ul-vpn").is(':checked') ? 1 : 0;
        jsonData.surrender_flag = $("#ul-surrender").is(':checked') ? 1 : 0;
        
        $.ajax({
            type: "POST",
            url: UPDATE_LINKAGE_LINK,
            data: jsonData,
            dataType: "json",
            encode: true,
        })
        .done(function(data){
            for(var key in jsonData){
                $("#ul-" + key + "-error").empty();
            }
            if(!data.success){
                //display errors
                errors = data.data;
                for(var key in errors){
                    $("#ul-" + key + "-error").html(errors[key][0]).addClass("text-danger");
                }
                $("#ul-submit-btn").prop('disabled', false);
                $("#link-update-spinner").hide();
            }else{
                location.reload();
            }

        }).fail(function(){
            // console.log('error');
        })


		e.preventDefault();
    });

    $("#link-form").submit(function(e){
        $("#ll-submit-btn").prop('disabled', true);
        $("#link-update-spinner").show();

        const jsonData = setFormDataToJson($(this)); //try using this
        jsonData.brought_home_flag = $("#ll-brought-home").is(':checked') ? 1 : 0;
        jsonData.vpn_flag = $("#ll-vpn").is(':checked') ? 1 : 0;
        
        $.ajax({
            type: "POST",
            url: REGISTER_LINKAGE_LINK,
            data: jsonData,
            dataType: "json",
            encode: true,
        })
        .done(function(data){
            for(var key in jsonData){
                $("#ll-" + key + "-error").empty();
            }
            if(!data.success){
                //display errors
                errors = data.data;
                for(var key in errors){
                    // console.log("#ll-" + key + "-error")
                    $("#ll-" + key + "-error").html(errors[key][0]).addClass("text-danger");
                }
                $("#ll-submit-btn").prop('disabled', false);
                $("#link-update-spinner").hide();
            }else{
                location.reload();
            }

        }).fail(function(){
            // console.log('error');
        })


		e.preventDefault();
    });

	var employeeHistoryTable = $("#emp-hist-tbl").DataTable({
		"stateSave": true,
		"pageLength": 10,
        "ordering": false,
        "bFilter": false,
        "oLanguage": {
	        "sEmptyTable": "No Data"
	    }
	});

    
	// Click Reject button to display Reject Modal
	$("#reject-request").click(function(){
        $("#laptop_reject_spinner").show();
		$('#reject-request').prop('disabled', true);
	})

    // Reject modal
	$("#reject-request-form").submit(function(){
        $("#laptop_reject_submit_spinner").show();
        $("#link_reject_submit_spinner").show();
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
    
	// Approve Laptop Detail Update/Creation request
	$("#approve-request-form").submit(function(){
        $("#laptop_approve_spinner").show();
		$('#approve-request').prop('disabled', true);
	});


    // Reject laptop linkage to employee
    $("#reject-sub").click(function(e){
        $("reject-sub-spinner#").show();
    });


    // Submit laptop linkage to employee
    $(".reject-link-btn").click(function(e){
        var linkId = $(this).data('linkid');
        $("#link_reject_spinner_" + linkId).show();
        $("#reject-link-in").val(linkId);
    });

    // On modal close,
    $("#rejectLinkageRequestModal").on("hidden.bs.modal", function() {
        $(".spinner-border").hide();
    });

    // Approve laptop linkage to employee
    $(".approve-link-btn").click(function(){
        var linkId = $(this).data('linkid');
        $("#link_approve_spinner_" + linkId).show();
        $("#approve-link-in").val(linkId);
    });

    setDisplayOfLinkage();
    $("#link_to_self").click(function (e) {
        setDisplayOfLinkage();
    });

    function setDisplayOfLinkage(){
        if($("#link_to_self").is(":checked")){
            $("#linkage_form").removeClass('d-none');
        }else{
            $("#linkage_form").addClass('d-none');
        }
    }

    $("#lapreg_form").submit(function () {
        if($("#link_to_self").length && $("#link_to_self").is(":checked")){
            $("#link_to_self_hidden").prop("disabled", true);
        }
        if($("#brought_home_flag").is(":checked")){
            $("#brought_home_flag_hidden").prop("disabled", true);
        }
        if($("#vpn_flag").is(":checked")){
            $("#vpn_flag_hidden").prop("disabled", true);
        }
    });

});