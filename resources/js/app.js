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

	const container = document.querySelector('.dash-notifications');
	const indicator = document.querySelector('.indicator');
	
	container.addEventListener('scroll', () => {
		if (container.scrollHeight - container.scrollTop === container.clientHeight) {
			indicator.style.display = 'none';
		} else {
			indicator.style.display = 'block';
		}
	});

	$("button").click(function() {
		var module_name = $(this).prop("name").replace("home-", "");
		var user_id = $(this).attr("user-id");
		var url;

		switch(module_name) {
			case "employee" :
				url = `/employees/${user_id}`;
				break;
			case "laptop" :
				url = "/laptops/create";
				break;
			case "laptop-link" :
				url = "/laptops";
				break;
			case "project-link" :
				url = "/projects";
				break;
		}

		$(location).attr('href',url);
	});
	
});