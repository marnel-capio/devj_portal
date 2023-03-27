const LINK_EMPLOYEE_PROJECT_LINK = '/api/linkEmployeeProject';

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

    

});