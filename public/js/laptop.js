/******/ (() => { // webpackBootstrap
var __webpack_exports__ = {};
/*!********************************!*\
  !*** ./resources/js/laptop.js ***!
  \********************************/
var FILTER_LAPTOP_LINK = '/api/laptops/search';
var UPDATE_LINK = '/api/laptops/update';
var UPDATE_LINKAGE_LINK = '/api/laptops/updateLinkage';
var REGISTER_LINKAGE_LINK = '/api/laptops/registLinkage';
$(document).ready(function () {
  //laptop list
  var laptopList = $("#laptop-list").DataTable({
    "stateSave": true,
    "bFilter": false,
    "pageLength": 25,
    "oLanguage": {
      "sEmptyTable": "No Data"
    }
  });
  $(".laptop-search-availability").click(function () {
    filterLaptopList();
  });
  $(".laptop-status").click(function () {
    filterLaptopList();
  });
  $(".laptop-filter").click(function () {
    filterLaptopList();
  });
  $("#search-input").on('input', function () {
    filterLaptopList();
  });
  function filterLaptopList() {
    $.ajax({
      type: "GET",
      url: FILTER_LAPTOP_LINK,
      data: {
        keyword: $('input[name=searchInput]').val(),
        availability: $('input[name=laptopAvailability]:checked').val(),
        status: $('input[name=laptopStatus]:checked').val(),
        searchFilter: $('input[name=searchFilter]:checked').val()
      },
      dataType: "json",
      encode: true
    }).done(function (data) {
      // console.log(data);
      if (data.success) {
        laptopList.clear().draw();
        data.update.forEach(function (laptop) {
          laptopList.row.add(['<a href="' + window.location.href + '/' + laptop.id + '">' + laptop.tag_number + '</a>', laptop.laptop_make, laptop.laptop_model, laptop.laptop_cpu, laptop.laptop_clock_speed, laptop.laptop_ram, laptop.owner, laptop.status]).draw(false);
        });
      }
    }).fail(function () {
      console.log('error');
    });
  }
  $("#edit-form").submit(function (e) {
    $("#el-spinner").show();
    $("#el-submit-btn").prop('disabled', true);
    var formData = $("#edit-form").serializeArray();
    var arrData = [];
    formData.forEach(function (data) {
      arrData[data['name']] = data['value'];
    });
    if ($("#edit-form input[name=status").length) {
      arrData['status'] = $("#edit-form input[name=status").is(':checked') ? 1 : 0;
    }
    var jsonData = JSON.stringify(Object.assign({}, arrData));
    jsonData = JSON.parse(jsonData);

    // console.log(jsonData);

    $.ajax({
      type: "POST",
      url: UPDATE_LINK,
      data: jsonData,
      dataType: "json",
      encode: true
    }).done(function (data) {
      for (var key in jsonData) {
        $("#" + key + "-error").empty();
      }
      if (!data.success) {
        //display errors
        errors = data.data;
        for (var key in errors) {
          $("#" + key + "-error").html(errors[key][0]);
        }
        $("#el-submit-btn").prop('disabled', false);
        $("#el-spinner").hide();
      } else {
        location.reload();
      }
    }).fail(function () {
      console.log('error');
    });
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
  $("#update-linkage-form").submit(function (e) {
    $("#ul-submit-btn").prop('disabled', true);
    $("#link-update-spinner").show();
    var formData = $("#update-linkage-form").serializeArray();
    var arrData = [];
    formData.forEach(function (data) {
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
      encode: true
    }).done(function (data) {
      for (var key in jsonData) {
        $("#ul-" + key + "-error").empty();
      }
      if (!data.success) {
        //display errors
        errors = data.data;
        for (var key in errors) {
          $("#ul-" + key + "-error").html(errors[key][0]).addClass("text-danger");
        }
        $("#ul-submit-btn").prop('disabled', false);
        $("#link-update-spinner").hide();
      } else {
        location.reload();
      }
    }).fail(function () {
      console.log('error');
    });
    e.preventDefault();
  });
  $("#link-form").submit(function (e) {
    $("#ll-submit-btn").prop('disabled', true);
    $("#link-update-spinner").show();
    var formData = $("#link-form").serializeArray();
    var arrData = [];
    formData.forEach(function (data) {
      arrData[data['name']] = data['value'];
    });
    arrData['brought_home_flag'] = $("#ll-brought-home").is(':checked') ? 1 : 0;
    arrData['vpn_flag'] = $("#ll-vpn").is(':checked') ? 1 : 0;
    var jsonData = JSON.stringify(Object.assign({}, arrData));
    jsonData = JSON.parse(jsonData);
    $.ajax({
      type: "POST",
      url: REGISTER_LINKAGE_LINK,
      data: jsonData,
      dataType: "json",
      encode: true
    }).done(function (data) {
      for (var key in jsonData) {
        $("#ll-" + key + "-error").empty();
      }
      if (!data.success) {
        //display errors
        errors = data.data;
        for (var key in errors) {
          // console.log("#ll-" + key + "-error")
          $("#ll-" + key + "-error").html(errors[key][0]).addClass("text-danger");
        }
        $("#ll-submit-btn").prop('disabled', false);
        $("#link-update-spinner").hide();
      } else {
        location.reload();
      }
    }).fail(function () {
      console.log('error');
    });
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
  $(".reject-link-btn").click(function () {
    var linkId = $(this).data('linkid');
    $("#reject-link-in").val(linkId);
  });
  $(".approve-link-btn").click(function () {
    var linkId = $(this).data('linkid');
    $("#approve-link-in").val(linkId);
  });
  setDisplayOfLinkage();
  $("#link_to_self").click(function (e) {
    setDisplayOfLinkage();
  });
  function setDisplayOfLinkage() {
    if ($("#link_to_self").is(":checked")) {
      $("#linkage_form").removeClass('d-none');
    } else {
      $("#linkage_form").addClass('d-none');
    }
  }
  $("#lapreg_form").submit(function () {
    console.log('submission');
    if ($("#link_to_self").length && $("#link_to_self").is(":checked")) {
      $("#link_to_self_hidden").prop("disabled", true);
    }
    if ($("#brought_home_flag").is(":checked")) {
      $("#brought_home_flag_hidden").prop("disabled", true);
    }
    if ($("#vpn_flag").is(":checked")) {
      $("#vpn_flag_hidden").prop("disabled", true);
    }
  });
});
/******/ })()
;