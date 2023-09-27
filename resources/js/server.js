const SERVER_DELETEION_API_LINK = '/api/servers/delete';
const SERVER_STATUS_1_MAX_VALUE = 60;
const SERVER_STATUS_2_MAX_VALUE = 89;
const SERVER_STATUS_3_MAX_VALUE = 100;
const SERVER_STATUS_NAME_1 = 'Normal';
const SERVER_STATUS_NAME_2 = 'Stable';
const SERVER_STATUS_NAME_3 = 'Critical';
const KB_TO_BYTES = 1024; // Binary system is used 
const SERVER_LIST_STATUS_INDEX = 6;

$(document).ready(function () {

    const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]')
    const tooltipList = [...tooltipTriggerList].map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl))

// ******************************************** Server List ********************************************/

    
	
	
	// Create button is clicked
	$("#create").on("click", function() {
		$("#create-spinner").show();
		$("#create").prop("disabled", true);
        
        $(location).attr('href', "/servers/create");

	});
	

	// Download button is clicked
	$("#servers-download").on("click", function() {
		$("#servers-download-spinner").show();
		$("#servers-download").prop("disabled", true);
		
		setTimeout(function(){
			$("#servers-download-spinner").hide();
			$("#servers-download").prop("disabled", false);
		}, 5000);

	});
    
    
    var serverList = $("#server_list").DataTable({
        "stateSave": true,
        "pageLength": 25,
        "oLanguage": {
            "sEmptyTable": "There is no record found",
            "sZeroRecords": "There is no record found",
            "sInfoFiltered": ""
        },
        "sDom": "lrt<'#bottom.row'<'#info.col'i><'#pagination.col'p>>",
    });

    clearFilter();

    $(".server-status").on('change', function () {
        var filterVal = $("input[name=serverStatus]:checked").val();
        
        if (filterVal == 1) { 
            clearFilter();
        } else {
            var filter;
            if (filterVal == 2){
                var filter = '^Active$';
            } else if (filterVal == 3){
                var filter = '^Inactive$';
            }
            serverList.column(SERVER_LIST_STATUS_INDEX)
                        .search(filter, true, false)
                        .draw();
        }
    })

    $("#search-input").on('input', function () {
        var filterVal = $(this).val();
        serverList.search(filterVal).draw();
    })

    function clearFilter(){
        serverList.column(SERVER_LIST_STATUS_INDEX).search('').draw();
        serverList.search('').draw();
    }
    
    // Server deletion
    $('#server_list').on('click', '.delete_server', function (e) {
        // Get id and server name
        let id = $(this).data('id');
        let serverName = $(this).data('server_name');

        
		$("#delete-btn-spinner-" + id).show();
        
		setTimeout(function(){},500);

        if ( confirm(`Are you sure you want to delete ${serverName}? delete-btn-spinner-${id}`) ) {
            // Delete server
            console.log('delete server');
            $.ajax({
                type: "GET",
                url: SERVER_DELETEION_API_LINK,
                data: {
                    'id': id
                },
                dataType: 'json',
            }).done(function (data) {
                console.log('done');
                if (data.success) {
                    location.reload();
                } else {
                    // Display error
                    $(`<div class="alert alert-danger" role="alert">${data.error}</div>`).insertBefore('.container-list-table');
                }
                $("#delete-btn-spinner-" + id).hide();
            }).fail( function () {
                console.log('error');
                $("#delete-btn-spinner-" + id).hide();
            })

        } else {
		    $("#delete-btn-spinner-" + id).hide();
        }
        
        e.preventDefault()
    });





// ******************************************** Server Regist/Update ********************************************/

    // ===================================================HDD USAGE===================================================

    /**
     * add new partition
     */
    $("#add_partition").click(function (e) {
        
        // Get last partition to clone
        var elementToClone = $(".partition_section").last();

        if($(".partition_section").length == 1){
            elementToClone.find('.remove_partition').show();
        }

        var clonedElement = elementToClone.clone();

        clonedElement.find('[id]').each(function(){
            // Update indices in ids
            $(this).attr('id', $(this).attr('id').replace(/(\d+)/, function(str, substr){return (parseInt(substr) + 1)}));
        });
        clonedElement.find('[name]').each(function(){
            // Increment index
            $(this).attr('name', $(this).attr('name').replace(/(\d+)/, function(str, substr){return (parseInt(substr) + 1)}));

            // Reset inputs
            if($(this).attr('type') == 'radio') {
                if($(this).attr('value') == 1){
                    $(this).prop('checked', true);
                }else{
                    $(this).prop('checked', false);
                }
            }else if($(this).attr('type') == 'text'){
                $(this).val('');
            }else{
                // Unit dropdown
                $(this).val(1);
            }

        });
        // Enable size inputs, disable inputs for percentage
        clonedElement.find('[for]').each(function(){
            // Update indices in for attribute
            $(this).attr('for', $(this).attr('for').replace(/(\d+)/, function(str, substr){return (parseInt(substr) + 1)}));
        });

        // Remove errors in cloned elements
        clonedElement.find('.text-danger').each( function () {
            $(this).html('');
        });

        // Add new element
        elementToClone.after(clonedElement);

        // Get input index
        var partitionIndex = parseInt(clonedElement.find('.partition_name').attr('id').match(/\d+/));
        enableDisableHDDInputs(partitionIndex, true);

        e.preventDefault();
    });

    /**
     * removing hdd partitions
     */
    $("#hdd_partitions").on('click', '.remove_partition', function (e) {
        $(this).parents('.partition_section').remove();
        hideRemoveButton();
        resetHddPartitionIndex();
        e.preventDefault();
    });

    hideRemoveButton();
    function hideRemoveButton(){
        if($(".partition_section").length == 1){
            // Hide remove button if only 1 hdd partition is displayed
            $(".partition_section .remove_partition").hide();
        }else{
            $(".partition_section .remove_partition").show();
        }
    }

    function resetHddPartitionIndex () {
        var index = 1;
        $(".partition_section").each( function () {
            var partition = $(this);

            partition.find('[id]').each(function(){
                // Update indices in ids
                $(this).attr('id', $(this).attr('id').replace(/(\d+)/, index));
            });
            partition.find('[name]').each(function(){
                // Update indices in inputs
                $(this).attr('name', $(this).attr('name').replace(/(\d+)/, index));
            });
            partition.find('[for]').each(function(){
                // Update indices in labels
                $(this).attr('for', $(this).attr('for').replace(/(\d+)/, index));
            });

            index++;
        });
    }

    /**
     * disabling size/percentage input in hdd partition section
     */
    $(".partition_section").each( function () {
        checkAndSetHddInputOption($(this).find(".hdd_select_radio"));
    });
    $("#hdd_partitions").on('click', '.hdd_select_radio', function () {
        checkAndSetHddInputOption($(this));
    });

    function checkAndSetHddInputOption (radioSelector) {
        var partitionIndex = radioSelector.attr('id').match(/\d+/);
        partitionIndex = parseInt(partitionIndex);

        if($("#hdd_size_radio_" + partitionIndex).is(':checked')){
            enableDisableHDDInputs(partitionIndex, true);
        } else if($("#hdd_percentage_radio_" + partitionIndex).is(':checked')){
            enableDisableHDDInputs(partitionIndex, false);
        }
    }

    function enableDisableHDDInputs(pIndex, isSizeSelected = true) {
        var sizeProp = isSizeSelected ? false : true;
        var percentProp = isSizeSelected ? true : false;

        // Percentage inputs
        $("#hdd_used_percentage_" + pIndex).prop('disabled', percentProp);
        $("#hdd_free_percentage_" + pIndex).prop('disabled', percentProp);

        // Size inputs
        $("#hdd_used_" + pIndex).prop('disabled', sizeProp);
        $("#hdd_used_unit_" + pIndex).prop('disabled', sizeProp);
        $("#hdd_free_" + pIndex).prop('disabled', sizeProp);
        $("#hdd_free_unit_" + pIndex).prop('disabled', sizeProp);
    }

    /**
     * disabling size/percentage input in memory section
     */
    checkAndSetMemoryInputOption();
    $("input[name=memory_input_type]").change(function () {
        checkAndSetMemoryInputOption();
    });

    function checkAndSetMemoryInputOption () {
        if($("#memory_size_radio").is(':checked')){
            enableDisableMemoryInputs(true);
        } else if($("#memory_percentage_radio").is(':checked')){
            enableDisableMemoryInputs(false);
        }
    }

    function enableDisableMemoryInputs(isSizeSelected = true) {
        var sizeProp = isSizeSelected ? false : true;
        var percentProp = isSizeSelected ? true : false;

        // Percentage inputs
        $("#memory_percentage_section").find('input[type=text]').each( function () { 
            $(this).prop('disabled', percentProp);
        });

        // Size inputs
        $("#memory_size_section").find('input[type=text]').each( function () { 
            $(this).prop('disabled', sizeProp);
        });
        $("#memory_size_section").find('select').each( function () { 
            $(this).prop('disabled', sizeProp);
        });
    }

    /**
     * disabling linux/others input in memory section
     */
    checkAndSetOsType();
    $("input[name=os_type]").change(function () {
        checkAndSetOsType();
    });

    function checkAndSetOsType () {
        if($("#linux_radio").is(':checked')){
            enableDisableCPUInputs(true);
        } else if($("#other_os_radio").is(':checked')){
            enableDisableCPUInputs(false);
        }
    }

    function enableDisableCPUInputs(isLinuxSelected = true) {
        var linuxProp = isLinuxSelected ? false : true;
        var othersProp = isLinuxSelected ? true : false;

        // Linux inputs
        $("#linux_usage").find('input[type=text]').each( function() {
            $(this).prop('disabled', linuxProp);
        });

        // Others inputs
        $("input[name=other_os_percentage").prop('disabled', othersProp);
    }

    // Values calculations/conversions for HDD usage and Memory usage and CPU Usage

    /**
     * HDD Status Calculation
     */

    $('#hdd_partitions').on('change', '.hdd_select_radio', function () {
        calculatSetHDDPartitionFreeSize($(this).parents('.partition_section'));
        calculateHDDStatus();
    });

    $('#hdd_partitions').on('input', '.hdd_total', function () {
        calculatSetHDDPartitionFreeSize($(this).parents('.partition_section'));
        calculateHDDStatus();
    });

    $('#hdd_partitions').on('change', '.hdd_total_unit', function () {
        calculatSetHDDPartitionFreeSize($(this).parents('.partition_section'));
        calculateHDDStatus();
    });

    $('#hdd_partitions').on('input', '.hdd_used', function () {
        calculatSetHDDPartitionFreeSize($(this).parents('.partition_section'));
        calculateHDDStatus();
    });

    $('#hdd_partitions').on('change', '.hdd_used_unit', function () {
        calculatSetHDDPartitionFreeSize($(this).parents('.partition_section'));
        calculateHDDStatus();
    });

    $('#hdd_partitions').on('input', '.hdd_used_percentage', function () {
        calculatSetHDDPartitionFreeSize($(this).parents('.partition_section'));
        calculateHDDStatus();
    });

    function calculatSetHDDPartitionFreeSize (parent) {

        if (parent.find('.hdd_total').val() != '' && parent.find('.hdd_total_unit').val() != ''){
            var totalUnit = getFloatValueForHDD(parent.find('.hdd_total_unit').val());
            var totalValue = getFloatValueForHDD(parent.find('.hdd_total').val());
            var totalValueInBytes = convertToBytes(totalValue, totalUnit);

            if (parent.find('.hdd_size_radio').is(':checked') && parent.find('.hdd_used').val() != '' && parent.find('.hdd_used_unit').val() != '') {
                // Get the memory values for calculation
                var useUnit = getFloatValueForHDD(parent.find('.hdd_used_unit').val());
                var useValueInBytes = convertToBytes(getFloatValueForHDD(parent.find('.hdd_used').val()), useUnit);
                var freeUnit = useUnit;
                var freeValue = getFreeSizeValue(totalValueInBytes, useValueInBytes, freeUnit);

                // Set value of free size
                parent.find('.hdd_free_unit > option[value=' + useUnit + ']').prop('selected', true);
                parent.find('.hdd_free').val(freeValue);

                // Set value of percentage
                percentage = parseFloat(((useValueInBytes *  100)/ totalValueInBytes)).toFixed(2);
                var freePercentage = parseFloat(100 - percentage).toFixed(2);
                parent.find('.hdd_used_percentage').val(percentage);
                parent.find('.hdd_free_percentage').val(freePercentage);

            } else if (parent.find('.hdd_percentage_radio').is(':checked') && parent.find('.hdd_used_percentage').val() != ''){

                percentage = parseFloat(parent.find('.hdd_used_percentage').val().toString());
                // Set value of free percentage
                var freePercentage = parseFloat(100 - percentage).toFixed(2);
                parent.find('.hdd_free_percentage').val(freePercentage); 

                // Set unit of free size and used size
                parent.find('.hdd_free_unit > option[value=' + totalUnit + ']').prop('selected', true);
                parent.find('.hdd_used_unit > option[value=' + totalUnit + ']').prop('selected', true);

                
                // Calculate the used and free size
                var useValue = ((percentage * totalValue) / 100).toFixed(2);
                var freeValue = (totalValue - useValue).toFixed(2);
                // Set value on screen
                parent.find('.hdd_used').val(useValue);
                parent.find('.hdd_free').val(freeValue);
            }
        }
    }

    function calculateHDDStatus () {
        var hddTotalSummation = 0;
        var hddUsedSummation = 0;
        var totalValueInBytes;
        var useValueInBytes;

        $(".partition_section").each( function () {
            var partition = $(this);
            totalValueInBytes = 0;
            useValueInBytes = 0;

            if (partition.find('.hdd_total').val() != '' && partition.find('.hdd_total_unit').val() != '' 
                && partition.find('.hdd_used').val() != '' && partition.find('.hdd_used_unit').val() != '') {
                    // Convert total hdd size to Bytes
                    totalValueInBytes = convertToBytes(getFloatValueForHDD(partition.find('.hdd_total').val()), getFloatValueForHDD(partition.find('.hdd_total_unit').val()));
                    // Convert parition usage size to Bytes
                    useValueInBytes = convertToBytes(getFloatValueForHDD(getFloatValueForHDD(partition.find('.hdd_used').val())), getFloatValueForHDD(partition.find('.hdd_used_unit').val()));

                    hddTotalSummation += totalValueInBytes;
                    hddUsedSummation += useValueInBytes;
                }
        });

        if (hddTotalSummation != 0) {
            // Calculate total hdd usage
            var totalUsagePercentage = (hddUsedSummation / hddTotalSummation) * 100;
            totalUsagePercentage = totalUsagePercentage.toFixed(2);  

            // Get status
            changeStatusBasedOnUsage($("#hdd_status"), $("input[name=hdd_status]"), totalUsagePercentage);
        }

    }

    function getFloatValueForHDD (value) {
        var parsed = parseFloat(value.toString());
        return isNaN(parsed) ? 0 : parsed;
    }

    /**
     * CPU Status calculations
     */

    $("input[name=os_type]").on('change', function () {
        calculateAndSetCPUStatus();
    });

    $("#us").on('input', function () { 
        calculateAndSetCPUStatus();
    });
    $("#sy").on('input', function () { 
        calculateAndSetCPUStatus();
    });
    $("#ni").on('input', function () { 
        calculateAndSetCPUStatus();
    });

    $("input[name=other_os_percentage]").on('input', function () { 
        calculateAndSetCPUStatus();
    });

    function calculateAndSetCPUStatus(){
        var percentage;
        // Get percentage
        if ($("#other_os_radio").is(':checked') && $("input[name=other_os_percentage]").val() != ''){
            percentage = getFloatValue($("input[name=other_os_percentage]"));
        } else if ($("#linux_radio").is(':checked')  && ($("#us").val() != '' || $("#ni").val() != '' || $("#sy").val() != '')) {
            percentage = (getFloatValue($("#us")) + getFloatValue($("#ni")) + getFloatValue($("#sy")))/3;
        }
        // Get status
        changeStatusBasedOnUsage($("#cpu_status"), $("input[name=cpu_status]"), percentage);
    }


    /**
     * Memory Status calculations
     */
    $("input[name=memory_input_type]").on('change', function () {
        calculateAndSetMemoryStatus();
    });
    $("input[name=memory_total]").on('input', function () {
        calculateAndSetMemoryStatus();
    });
    $("#memory_total_unit").on('change', function () {
        calculateAndSetMemoryStatus();
    });

    $("input[name=memory_used]").on('input', function () {
        calculateAndSetMemoryStatus();
    });
    $("#memory_used_unit").on('change', function () {
        calculateAndSetMemoryStatus();
    });
    $("input[name=memory_used_percentage]").on('input', function () {
        calculateAndSetMemoryStatus();
    });


    function calculateAndSetMemoryStatus () {
        var percentage;
        if ($("#memory_total").val() != '') {
            var totalUnit = getFloatValue($("#memory_total_unit > option:selected"));
            var totalValue = getFloatValue($("#memory_total"));
            var totalValueInBytes = convertToBytes(totalValue, totalUnit);

            if ($("#memory_size_radio").is(':checked') && $("#memory_used").val() != '' && $("#memory_used_unit").val() != '') {

                // Get the memory values for calculation
                var useUnit = getFloatValue($("#memory_used_unit > option:selected"));
                var useValueInBytes = convertToBytes(getFloatValue($("#memory_used")), useUnit);
                var freeUnit = useUnit;
                var freeValue = getFreeSizeValue(totalValueInBytes, useValueInBytes, freeUnit);

                // Calculate and set value of free usage
                $("#memory_free_unit > option[value=" + useUnit + "]").prop('selected', true);  // Set the unit of free size to the same unit with usage size
                $("input[name=memory_free]").val(freeValue);

                // Set value of memory percentage
                percentage = parseFloat(((useValueInBytes *  100)/ totalValueInBytes)).toFixed(2);
                var freePercentage = parseFloat(100 - percentage).toFixed(2);
                $("input[name=memory_used_percentage]").val(percentage);
                $("input[name=memory_free_percentage]").val(freePercentage);

            } else if ($("#memory_percentage_radio").is(':checked') && $("#memory_used_percentage").val() != '') {
                percentage = parseFloat($("#memory_used_percentage").val().toString());
                // Set value of free percentage
                var freePercentage = parseFloat(100 - percentage).toFixed(2);
                $("#memory_free_percentage").val(freePercentage);

                // Set unit of free size and used size
                $("#memory_free_unit option[value=" + totalUnit + "]").prop('selected', true);
                $("#memory_used_unit option[value=" + totalUnit + "]").prop('selected', true);

                // Calculate the used and free size
                var useValue = ((percentage * totalValue) / 100).toFixed(2);
                var freeValue = (totalValue - useValue).toFixed(2);

                // Set value on screen
                $("#memory_used").val(useValue);
                $("#memory_free").val(freeValue);
            }

            // Get status
            changeStatusBasedOnUsage($("#memory_status"), $("input[name=memory_status]"), percentage);
        }
    }
    
// ******************************************** Common Functions ********************************************/

    /**
     * Returns the equivalent size in Bytes
     * @param {int} value 
     * @param {int} unit 
     * @returns int
     */
    function convertToBytes(value, unit){
        var multiplier = Math.pow(KB_TO_BYTES, unit - 1);
        return value * multiplier;
    }

    function getFreeSizeValue(totalValInBytes, usedValInBytes, toUnit){
        var divisor = Math.pow(KB_TO_BYTES, toUnit - 1);
        var freeValue = totalValInBytes - usedValInBytes;
        return parseFloat((freeValue / divisor).toFixed(2));
    }

    /**
     * Returns the int value of an html object
     * @param {jquery selector} selector 
     * @returns 
     */
    function getFloatValue(selector) {
        var parsed = parseFloat(selector.val().toString());
        return isNaN(parsed) ? 0 : parsed;
    }

    /**
     * Change status based on usage value, used for hdd, memory and cpu usage
     * @param {jquery selector for status display} statusSelector 
     * @param {jquery selector for status input} inputSelector 
     * @param {int/float} percentage 
     */
    function changeStatusBasedOnUsage (statusSelector, inputSelector, percentage) {

        if (typeof percentage !== 'undefined' && (Number.isInteger(percentage) || percentage.toString().match(/\d+[.]?\d+/) !== null)) {
            if (percentage <= SERVER_STATUS_1_MAX_VALUE) {
                // Normal
                statusSelector.text(SERVER_STATUS_NAME_1).removeClass("text-danger text-primary").addClass("text-black");
                inputSelector.val(1)
            }else if (percentage <= SERVER_STATUS_2_MAX_VALUE) {
                // Stable
                statusSelector.text(SERVER_STATUS_NAME_2).removeClass("text-danger text-black").addClass("text-primary");
                inputSelector.val(2)
            }else {
                // Critical
                statusSelector.text(SERVER_STATUS_NAME_3).removeClass("text-black text-primary").addClass("text-danger");
                inputSelector.val(3)
            }
        }else{
            statusSelector.empty()
            inputSelector.val('')
        }
    }

    /**
     * enable all the fields on screen before form submission
     */
    function enableAllFields(){
        // All
        $("#usage").find('input').each( function () {
            $(this).prop('disabled', false);
        });

        $("#usage").find('select').each( function () {
            $(this).prop('disabled', false);
        });
    }


    // ==================================form submission
    $("#server_reg_form").on('submit', function () {
        $("#server-reg-submit").prop("disabled",true);
        $("#server-reg-submit-spinner").show();
        // Enable disabled fields before submission
        enableAllFields();
        $("input[name=partitions_count]").val($(".partition_section").length);

    });

});

