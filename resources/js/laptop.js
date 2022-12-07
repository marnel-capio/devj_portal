const FILTER_LAPTOP_LINK = '/devj_portal/public/api/laptops/search';
const VALIDATE_UPDATE_LINK = '/devj_portal/public/api/laptops/update';

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
        // delete arrData['_token'];
        var jsonData = JSON.stringify(Object.assign({}, arrData));
        jsonData = JSON.parse(jsonData);

        console.log(jsonData);
        
        $.ajax({
            type: "POST",
            url: VALIDATE_UPDATE_LINK,
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
                    console.log("#" + key + "-error")
                    $("#" + key + "-error").html(errors[key]);
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
		"pageLength": 10
	});

	var employeeHistoryTable = $("#emp-hist-tbl").DataTable({
		"stateSave": true,
		"pageLength": 10
	});
});