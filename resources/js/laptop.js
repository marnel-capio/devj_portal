const FILTER_LAPTOP_LINK = '/devj_portal/public/api/laptops/search';

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
});