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

	$("#laptop-request").DataTable({
		"stateSave": true,
		"pageLength": 10
	});

	$("#laptop-link-request").DataTable({
		"stateSave": true,
		"pageLength": 10
	});

	$("#project-link-request").DataTable({
		"stateSave": true,
		"pageLength": 10
	});
	
	// Display/Hide Scroll to top button
	$(window).scroll(function() {
		if($(this).scrollTop() >= 20) {
			$("#btnTop").removeClass("d-none");
			$("#btnTop").addClass("d-block");
			$("#btnTop").fadeIn("fast");
		} else {
			$("#btnTop").fadeOut("fast", function() {
				$("#btnTop").removeClass("d-block");
				$("#btnTop").addClass("d-none");
			});
		}
	});

	// Fucntion for Scroll to top button
	$("#btnTop").click(function() {
		$(window).scrollTop(0);
	})

	var popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'))
	var popoverList = popoverTriggerList.map(function (popoverTriggerEl) {
		return new bootstrap.Popover(popoverTriggerEl)
	})

	var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
	var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
		return new bootstrap.Tooltip(tooltipTriggerEl)
	})

	  

});