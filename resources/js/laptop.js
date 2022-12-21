const FILTER_LAPTOP_LINK = '/api/laptops/search';
const UPDATE_LINK = '/api/laptops/update';
const UPDATE_LINKAGE_LINK = '/api/laptops/updateLinkage';
const REGISTER_LINKAGE_LINK = '/api/laptops/registLinkage';

$(document).ready(function(){

    //laptop list
    var laptopList = $("#laptop-list").DataTable({
        "stateSave": true,
		"bFilter": false,
		"pageLength": 25,
		"oLanguage": {
	        "sEmptyTable": "No Data"
	    }
    });

    $(".laptop-search-availability").click(function(){
        filterLaptopList();
    })
    $(".laptop-status").click(function(){
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
            },
            dataType: "json",
            encode: true
        }).done(function(data){
            console.log(data);
            if(data.success){
                laptopList.clear().draw();

                data.update.forEach(function(laptop){
                    laptopList.row.add([
                        '<a href="' + window.location.href + laptop.id + '">' + laptop.tag_number + '</a>',
                        laptop.peza_form_number,
                        laptop.peza_permit_number,
                        laptop.laptop_make,
                        laptop.laptop_model,
                        laptop.status
                    ])
                    .draw(false);
                });
            }
        }).fail(function(){
            console.log('error');
        });
    }

    $("#edit-form").submit(function(e){
        var formData = $("#edit-form").serializeArray();
        var arrData = [];
        formData.forEach(function(data){
            arrData[data['name']] = data['value'];
        });

        var jsonData = JSON.stringify(Object.assign({}, arrData));
        jsonData = JSON.parse(jsonData);

        console.log(jsonData);
        
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
            }else{
                location.reload();
            }

        }).fail(function(){
            console.log('error');
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
        var formData = $("#update-linkage-form").serializeArray();
        var arrData = [];
        formData.forEach(function(data){
            arrData[data['name']] = data['value'];
        });

        arrData['brought_home_flag'] = $("#ul-brought-home").is(':checked') ? 1 : 0;
        arrData['vpn_flag'] = $("#ul-vpn").is(':checked') ? 1 : 0;
        arrData['surrender_flag'] = $("#ul-surrender").is(':checked') ? 1 : 0;

        var jsonData = JSON.stringify(Object.assign({}, arrData));
        jsonData = JSON.parse(jsonData);
        
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
            }else{
                location.reload();
            }

        }).fail(function(){
            console.log('error');
        })


		e.preventDefault();
    });

    $("#link-form").submit(function(e){
        var formData = $("#link-form").serializeArray();
        var arrData = [];
        formData.forEach(function(data){
            arrData[data['name']] = data['value'];
        });

        arrData['brought_home_flag'] = $("#ll-brought-home").is(':checked') ? 1 : 0;
        arrData['vpn_flag'] = $("#ll-vpn").is(':checked') ? 1 : 0;

        var jsonData = JSON.stringify(Object.assign({}, arrData));
        jsonData = JSON.parse(jsonData);
        console.log(jsonData);
        
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
                    console.log("#ll-" + key + "-error")
                    $("#ll-" + key + "-error").html(errors[key][0]).addClass("text-danger");
                }
            }else{
                location.reload();
            }

        }).fail(function(){
            console.log('error');
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
});