/******/ (() => { // webpackBootstrap
var __webpack_exports__ = {};
/*!**********************************!*\
  !*** ./resources/js/employee.js ***!
  \**********************************/
var CHANGE_PASSWORD_LINK = '/api/changePassword';
var LINK_LAPTOP_LINK = '/api/linkLaptop';
var LINK_EMPLOYEE_PROJECT_LINK = '/api/linkProjectToEmployee';
var DEACTIVATE_EMPLOYEE_LINK = '/api/deactivateEmployee';
var REACTIVATE_EMPLOYEE_LINK = '/api/reactivateEmployee';
var TRANSFER_EMPLOYEE_LINK = '/api/transferEmployee';
var REINSTATE_EMPLOYEE_LINK = '/api/reinstateEmployee';
var NOTIFY_SURRENDER_OF_LAPTOPS_LINK = '/api/notifySurrender';
$(document).ready(function () {
  var employee_list = $("#employee-list").DataTable({
    "stateSave": true,
    "bFilter": false,
    "pageLength": 25,
    "oLanguage": {
      "sEmptyTable": "There is no record found"
    }
  });
  $("#send-notif").on("click", function () {
    $(".spinner-border").show();
  });
  $(".search-status-rdb-input").on("click", function () {
    filterEmployeeList();
  });
  $(".search-filter-rdb-input").on("click", function () {
    filterEmployeeList();
  });
  $("#search-input").on("input", function () {
    filterEmployeeList();
  });
  function filterEmployeeList() {
    var keyword = $("input[name='searchInput']").val();
    var filter = $("input[name='searchFilter']:checked").val();
    var status = $("input[name='employeeStatus']:checked").val();
    $.ajax({
      type: "get",
      url: "api/employees/search",
      data: {
        'keyword': keyword,
        'filter': filter,
        'status': status
        // 'token' : $('meta[name="csrf-token"]').attr('content'),           
      },

      success: function success(res) {
        employee_list.clear().draw();
        var result = JSON.parse(res);
        // console.log(result);
        result.forEach(function (employee) {
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
          var buAssignment = '';
          if (employee['bu_transfer_flag']) {
            buAssignment = employee['bu_transfer_assignment'];
          }
          url = window.location.href + "/" + employee['id'];
          employee_list.row.add(['<a href="' + url + '">' + employee['last_name'] + ', ' + employee['first_name'] + ' (' + employee['middle_name'] + ')</a>', employee['email'], employee['cellphone_number'], employee['current_address_city'], employee['current_address_province'], buAssignment, status]).draw(false);
        });
      }
    });
  }

  //start for employee registration

  //disable submit button if not all required fields have value
  checkRequiredFields();
  $(":input[required]").change(function () {
    checkRequiredFields();
  });

  //password check
  $("#emp-confirm-password, #emp-password").keyup(function () {
    if ($('#emp-confirm-password').val() != $("#emp-password").val()) {
      $("#confirm-pass-text").html("Password does not match.").addClass('text-danger text-start');
      $("#emp-reg-submit").prop('disabled', true);
    } else {
      $("#confirm-pass-text").html("");
      checkRequiredFields();
    }
  });
  function checkRequiredFields() {
    var empty = false;
    $(":input[required]").each(function () {
      if ($(this).val() == '') {
        empty = true;
      }
    });
    if (empty) {
      $("#emp-reg-submit").prop('disabled', true);
    } else {
      $("#emp-reg-submit").prop('disabled', false);
    }
  }
  $('.btn-prevent-multiple-submit').on('submit', function ($e) {
    e.preventDefault();
    $('.btn-prevent-multiple-submit').prop('disabled', true);
  });

  //end for employee registration

  //start for employee details/request

  //change password
  $("#cp-confirm-pw, #cp-new-pw").keyup(function () {
    if ($('#cp-confirm-pw').val() != $("#cp-new-pw").val()) {
      $("#confirm-pass-text").html("Password does not match.").addClass('text-danger text-start');
      $("#ecp-submit-btn").prop('disabled', true);
    } else {
      $("#confirm-pass-text").html("");
    }
  });
  $('#cp-submit-btn').click(function (e) {
    var postData = {
      _token: $("#changePasswordForm > input[name=_token]").val(),
      current_password: $("#cp-current-pw").val(),
      new_password: $("#cp-new-pw").val(),
      id: $("#changePasswordForm > input[name=cp_id]").val()
    };
    $.ajax({
      type: "POST",
      url: CHANGE_PASSWORD_LINK,
      //update later
      data: postData,
      dataType: "json",
      encode: true
    }).done(function (data) {
      if (!data.success) {
        $("#cp-success-msg").empty();
        $("#current-pass-error").empty();
        $("#new-pass-error").empty();
        //display error
        var currentPasswordErrors = data.data.current_password;
        if (currentPasswordErrors && currentPasswordErrors.length > 0) {
          $("#current-pass-error").html(currentPasswordErrors[0]).addClass('text-danger text-start');
        }
        var newPasswordErrors = data.data.new_password;
        if (newPasswordErrors && newPasswordErrors.length > 0) {
          $("#new-pass-error").html(newPasswordErrors[0]).addClass('text-danger text-start');
        }
      } else {
        $("#changePasswordForm").trigger('reset');
        $("#current-pass-error").empty();
        $("#new-pass-error").empty();
        $("#cp-success-msg").html('<i class="bi bi-check-circle-fill"></i>&nbsp;You have successfully changed your account password.').addClass("text-success mb-4 text-start");
      }
    }).fail(function () {
      console.log('error');
    });
    e.preventDefault();
  });

  //link project

  var projectTable = $("#project-tbl").DataTable({
    "stateSave": true,
    "bFilter": false,
    "bPaginate": false,
    "ordering": false,
    "bInfo": false,
    "oLanguage": {
      "sEmptyTable": "No Data"
    }
  });
  $("#lp-submit-btn").click(function (e) {
    $('#lp-submit-btn').prop('disabled', true);
    var postData = {
      _token: $("#linkProjectForm > input[name=_token]").val(),
      employee_id: $("#linkProjectForm > input[name=lp_employee_id]").val(),
      project_id: $("#projectList > option:selected").val(),
      project_start: $("#project-start").val(),
      project_end: $("#project-end").val(),
      project_role: $("#projectRoleList > option:selected").val(),
      project_onsite: $("#project-onsite").is(':checked') ? 1 : 0,
      remarks: $("#link_remarks").val()
    };
    $.ajax({
      type: "POST",
      url: LINK_EMPLOYEE_PROJECT_LINK,
      data: postData,
      dataType: "json",
      encode: true
    }).done(function (data) {
      // display error
      if (!data.success) {
        $("#lp-success-msg").empty();
        $("#error-lp-proj-name").empty();
        $("#error-lp-proj-role").empty();
        $("#error-lp-proj-start").empty();
        $("#error-lp-proj-end").empty();
        $("#error-lp-remarks").empty();
        //error-lp-remarks
        var projectError = data.data.project_id;
        if (projectError && projectError.length > 0) {
          $("#error-lp-proj-name").html(projectError[0]).addClass('text-danger text-start');
        }
        var projectRoleError = data.data.project_role;
        if (projectRoleError && projectRoleError.length > 0) {
          $("#error-lp-proj-role").html(projectRoleError[0]).addClass('text-danger text-start');
        }
        var projectStartError = data.data.project_start;
        if (projectStartError && projectStartError.length > 0) {
          $("#error-lp-proj-start").html(projectStartError[0]).addClass('text-danger text-start');
        }
        var projectEndError = data.data.project_end;
        if (projectEndError && projectEndError.length > 0) {
          $("#error-lp-proj-end").html(projectEndError[0]).addClass('text-danger text-start');
        }
        var remarksError = data.data.remarks;
        if (remarksError && remarksError.length > 0) {
          $("#error-lp-remarks").html(remarksError[0]).addClass('text-danger text-start');
        }
      } else {
        $("#linkProjectForm").trigger('reset');
        $("#error-lp-proj-name").empty();
        $("#error-lp-proj-role").empty();
        $("#error-lp-proj-start").empty();
        $("#error-lp-proj-end").empty();
        $("#error-lp-remarks").empty();
        $("#projectList > option[value=" + postData.project_id + "]").remove();
        $("#lp-success-msg").html('<i class="bi bi-check-circle-fill"></i>&nbsp;' + data.message + '.').addClass("text-success mb-2 text-start");

        //update projects table
        projectTable.clear().draw();
        data.update.forEach(function (project) {
          var url = window.location.origin + '/projects/' + project.project_id;
          // console.log(project);
          sDate = new Date(project.start_date);
          spanStart = sDate.getFullYear() + '/' + sDate.getMonth() + '/' + sDate.getDate() + ' - ';
          spanEnd = '';
          if (project.end_date != '' && project.end_date != null) {
            eDate = new Date(project.end_date);
            spanEnd = eDate.getFullYear() + '/' + eDate.getMonth() + '/' + eDate.getDate();
          }
          projectTable.row.add(['<a href="' + url + '" class="text-decoration-none">' + project.name + '</a>', spanStart + spanEnd, project.project_status]).draw(false);
        });
      }
      $('#lp-submit-btn').prop('disabled', false);
    }).fail(function () {
      console.log('error');
    });
    e.preventDefault();
  });

  //link laptop

  var laptopTable = $("#laptop-tbl").DataTable({
    "stateSave": true,
    "bFilter": false,
    "bPaginate": false,
    "bInfo": false,
    "oLanguage": {
      "sEmptyTable": "No Data"
    }
  });
  $("#ll-submit-btn").click(function (e) {
    $("#ll-link-spinner").show();
    $('#ll-submit-btn').prop('disabled', true);
    var postData = {
      _token: $("#linkLaptopForm > input[name=_token]").val(),
      employee_id: $("#linkLaptopForm > input[name=ll_employee_id]").val(),
      laptop_id: $("#laptopList > option:selected").val(),
      brought_home_flag: $("#ll-brought-home").is(':checked') ? 1 : 0,
      vpn_access_flag: $("#ll-vpn").is(':checked') ? 1 : 0,
      remarks: $("#ll-remarks").val()
    };
    $.ajax({
      type: "POST",
      url: LINK_LAPTOP_LINK,
      data: postData,
      dataType: "json",
      encode: true
    }).done(function (data) {
      if (!data.success) {
        //display error
        $("#ll-success-msg").empty();
        $("#error-laptop-id").empty();
        $("#error-ll-remarks").empty();
        var laptopIdError = data.data.laptop_id;
        if (laptopIdError && laptopIdError.length > 0) {
          $("#error-laptop-id").html(laptopIdError[0]).addClass('text-danger text-start');
        }
        var laptopRemarksError = data.data.remarks;
        if (laptopRemarksError && laptopRemarksError.length > 0) {
          $("#error-ll-remarks").html(laptopRemarksError[0]).addClass('text-danger text-start');
        }
      } else {
        $("#linkLaptopForm").trigger('reset');
        $("#error-laptop-id").empty();
        $("#error-ll-remarks").empty();
        $("#laptopList > option[value=" + postData.laptop_id + "]").remove();
        $("#ll-success-msg").html('<i class="bi bi-check-circle-fill"></i>&nbsp;' + data.message + '.').addClass("text-success mb-2 text-start");

        //update laptops table
        laptopTable.clear().draw();
        data.update.forEach(function (laptop) {
          var url = window.location.origin + '/laptops/' + laptop.id;
          laptopTable.row.add(['<a href="' + url + '" class="text-decoration-none">' + laptop.tag_number + '</a>', laptop.brought_home, laptop.laptop_make, laptop.laptop_model, laptop.use_vpn, laptop.remarks]).draw(false);
        });
      }
      $("#ll-link-spinner").hide();
      $('#ll-submit-btn').prop('disabled', false);
    }).fail(function () {
      console.log('error');
    });
    e.preventDefault();
  });

  //reject modal
  $("#reject-request-form").submit(function () {
    if ($("#reject-reason").val() == "") {
      $("#reject-reason-error").html('The reason field is required.').addClass("text-danger text-start");
      return false;
    } else if ($("#reject-reason").val().length > 1024) {
      $("#reject-reason-error").html('The reason must not be greater than 1024 characters.').addClass("text-danger text-start");
      return false;
    } else {
      $('#reject-sub').prop('disabled', true);
    }
  });

  //approve
  $("#approve-request-form").submit(function () {
    $('#approve-request').prop('disabled', true);
  });

  // edit submit
  $("#emp-update-form").submit(function () {
    if ($("#active-status").is(':checked')) {
      $("#active-status-hidden").prop('disabled', true);
    }
    if ($("#server-manage-flag").is(':checked')) {
      $("#server-manage-flag-hidden").prop('disabled', true);
    }
    if ($("#is-admin").is(':checked')) {
      $("#is-admin-hidden").prop('disabled', true);
    }
    $('#emp-update-submit').prop('disabled', true);
  });
  $('#linkProjectModal').on('hidden.bs.modal', function () {
    $("#lp-success-msg").empty();
  });
  $('#linkLaptopModal').on('hidden.bs.modal', function () {
    $("#ll-success-msg").empty();
  });
  $('#changePasswordModal').on('hidden.bs.modal', function () {
    $("#cp-success-msg").empty();
  });
  hideAdminCheck();
  $('#position').change(function () {
    hideAdminCheck();
  });
  function hideAdminCheck() {
    if ($('#position').val() == 8 || $('#position').val() == 9) {
      $('#admin-check').hide();
      $('#admin-detail').hide();
      $('#is-admin').prop('disabled', true);
    } else {
      $('#admin-check').show();
      $('#admin-detail').show();
      $('#is-admin').prop('disabled', false);
    }
  }

  //end for employee details/request

  //project dropdown max min date
  updateProjectCalendar();
  $("#projectList").change(function (e) {
    updateProjectCalendar();
  });
  function updateProjectCalendar() {
    var minDate = $("#projectList :selected").data("mindate");
    var maxDate = $("#projectList :selected").data("maxdate");
    $("#project-start").attr({
      min: minDate,
      max: maxDate
    });
    $("#project-end").attr({
      min: minDate,
      max: maxDate
    });
  }

  //start for employee deactivation/reactivation

  $("#employee-deactivate").click(function (e) {
    $(".alert").remove();
    if (!confirm("Continue with employee deactivation?")) {
      return false;
    }
    $("#react-deact-spinner").show();
    $.ajax({
      type: "POST",
      url: DEACTIVATE_EMPLOYEE_LINK,
      data: {
        id: $("#deact-react-form > input[name=id").val(),
        _token: $("#deact-react-form > input[name=_token").val()
      },
      dataType: "json",
      encode: true
    }).done(function (data) {
      if (data.success) {
        location.reload();
      } else {
        //display error 
        $("#deact-react-alert").remove();
        $("#alert-div").append('<div id="deact-react-alert" class="alert alert-danger" role="alert"><span class="ms-2">' + data.message + '</span></div>');
      }
      $("#react-deact-spinner").hide();
    }).fail(function () {
      console.log('error');
    });
  });
  $("#employee-reactivate").click(function (e) {
    if (!confirm("Continue with employee reactivation?")) {
      return false;
    }
    $("#react-deact-spinner").show();
    $.ajax({
      type: "POST",
      url: REACTIVATE_EMPLOYEE_LINK,
      data: {
        id: $("#deact-react-form > input[name=id").val(),
        _token: $("#deact-react-form > input[name=_token").val()
      },
      dataType: "json",
      encode: true
    }).done(function (data) {
      if (data.success) {
        location.reload();
      } else {
        //display error 
        $("#deact-react-alert").remove();
        $("#alert-div").append('<div id="deact-react-alert" class="alert alert-danger" role="alert"><span class="ms-2">' + data.message + '</span></div>');
      }
      $("#react-deact-spinner").hide();
    }).fail(function () {
      console.log('error');
    });
  });

  //end for for employee deactivation/reactivation

  //start for employee bu transfer/reinstate

  //bu transfer - form submission
  $("#transferEmployeeForm").on('submit', function (e) {
    $("#transfer_reinstate_spinner").show();
    $("#bu_transfer_msg").empty();

    //bu assignment validation
    if ($("#transferEmployeeForm input[name=bu_transfer_assignment]").val() == "") {
      $("#bu_transfer_msg").text('The BU assignment field is required.').addClass("text-danger text-start");
      $("#transfer_reinstate_spinner").hide();
      return false;
    } else if ($("#transferEmployeeForm input[name=bu_transfer_assignment]").val().length > 20) {
      $("#bu_transfer_msg").text('The BU assignment must not be greater than 20 characters.').addClass("text-danger text-start");
      $("#transfer_reinstate_spinner").hide();
      return false;
    }

    //form submission
    var formData = $(this).serializeArray();
    var arrData = [];
    formData.forEach(function (data) {
      arrData[data['name']] = data['value'];
    });
    var jsonData = JSON.stringify(Object.assign({}, arrData));
    jsonData = JSON.parse(jsonData);
    $.ajax({
      type: "POST",
      url: TRANSFER_EMPLOYEE_LINK,
      data: jsonData,
      dataType: "json",
      encode: true
    }).done(function (data) {
      $("#transfer_reinstate_spinner").hide();
      console.log(data);
      if (data.success) {
        location.reload();
      } else {
        //display error message
        $("#bu_transfer_msg").text(data.message).addClass("text-danger text-start");
      }
    }).fail(function () {
      console.log('error');
    });
    e.preventDefault();
  });
  $('#buTransferModal').on('hidden.bs.modal', function () {
    $("#bu_transfer_msg").empty();
  });
  $("#employee_reinstate").click(function (e) {
    if (!confirm("Continue to reinstate employee to Dev J?")) {
      return false;
    }
    $("#transfer_reinstate_spinner").show();
    $.ajax({
      type: "POST",
      url: REINSTATE_EMPLOYEE_LINK,
      data: {
        id: $("#deact-react-form > input[name=id").val(),
        _token: $("#deact-react-form > input[name=_token").val()
      },
      dataType: "json",
      encode: true
    }).done(function (data) {
      $("#transfer_reinstate_spinner").hide();
      if (data.success) {
        location.reload();
      } else {
        //display error 
        $("#deact-react-alert").remove();
        $("#alert-div").append('<div id="reinstate_alert" class="alert alert-danger" role="alert"><span class="ms-2">' + data.message + '</span></div>');
      }
    }).fail(function () {
      console.log('error');
    });
  });

  //end for employee bu transfer/reinstate
});
/******/ })()
;
