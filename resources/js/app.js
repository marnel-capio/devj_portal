import './bootstrap';

$(document).ready(function () {

	$("#employee-request").DataTable({
		"stateSave": true,
		"pageLength": 10
	});

	$("#software-request").DataTable({
		"stateSave": true,
		"pageLength": 10
	});
});