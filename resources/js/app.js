import './bootstrap';
import '../css/app.css'; 

$(document).ready(function () {

	$("#employee-request").DataTable({
		"stateSave": true,
		"pageLength": 10
	});
});