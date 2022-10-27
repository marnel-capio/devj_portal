
$(document).ready(function () {

	var employee_list = $("#employee-list").DataTable({
		"stateSave": true,
		"bFilter": false,
		"pageLength": 25,
		"oLanguage": {
	        "sEmptyTable": "There is no record found"
	    }
	});

    $("#search-input").on("input",function(){
    	var keyword = this.value;
    	var filter = $("input[name='searchFilter']:checked").val();
    	$.ajax({
	        type:"get",
	        url:"api/employees/search",
	        data :{
	                'keyword' : keyword , 
	                'filter' : filter ,   
	                // 'token' : $('meta[name="csrf-token"]').attr('content'),           
	            },          
        	success:function(res){
            	employee_list.clear().draw();
            	var result = JSON.parse(res);
            	// console.log(result);
            	result.forEach(function(employee) {
            		var status = "";
            		if (employee['active_status'] == 0) {
            			if (employee['approved_status'] == 1 || employee['approved_status'] == 2 || employee['approved_status'] == 4) {
            				status = "Deactivated";
            			} else {
            				status = "Pending for Approval";
            			}
            		} else if (employee['active_status'] == 1) {
            			if (employee['approved_status'] == 1) {
            				status = "Deactivated";
            			} else if (employee['approved_status'] == 2 || employee['approved_status'] == 4) {
            				status = "Active";
            			} else {
            				status = "Pending for Approval";
            			}
            		}
            		url = window.location.href+"/"+employee['id'];
            		employee_list.row.add(['<a href="'+url+'">'+employee['last_name']+', '+employee['first_name']+'</a>', employee['email'], employee['cellphone_number'],employee['current_address_city'],employee['current_address_province'],status]).draw(false);
            	});
            }
       });
    })
});