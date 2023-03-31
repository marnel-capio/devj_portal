const SERVER_DELETEION_API_LINK = '/api/servers/delete';
const SERVER_STATUS_1_MAX_VALUE = 60;
const SERVER_STATUS_2_MAX_VALUE = 89;
const SERVER_STATUS_3_MAX_VALUE = 100;
const SERVER_STATUS_NAME_1 = 'Normal';
const SERVER_STATUS_NAME_2 = 'Stable';
const SERVER_STATUS_NAME_3 = 'Critical';
const KB_TO_BYTES = 1024; //binary system is used 
const SERVER_LIST_STATUS_INDEX = 6;

$(document).ready(function () {

    const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]')
    const tooltipList = [...tooltipTriggerList].map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl))

//******************************************** Server List ********************************************/

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

    $("#server-status").on('change', function () {
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
    
    //server deletion
    $('#server_list').on('click', '.delete_server', function (e) {
        //get id and server name
        let id = $(this).data('id');
        let serverName = $(this).data('server_name');

        if ( confirm(`Delete ${serverName}?`) ) {
            //delete server
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
                    //display error
                    $(`<div class="alert alert-danger" role="alert">${data.error}</div>`).insertBefore('.container-list-table');
                }
            }).fail( function () {
                console.log('error');
            })

        } 
        e.preventDefault()
    });





//******************************************** Server Regist/Update ********************************************/

    //===================================================HDD USAGE===================================================

    /**
     * add new partition
     */
    $("#add_partition").click(function (e) {
        
        //get last partition to clone
        var elementToClone = $(".partition_section").last();

        if($(".partition_section").length == 1){
            elementToClone.find('.remove_partition').show();
        }

        var clonedElement = elementToClone.clone();

        clonedElement.find('[id]').each(function(){
            //update indices in ids
            $(this).attr('id', $(this).attr('id').replace(/(\d+)/, function(str, substr){return (parseInt(substr) + 1)}));
        });
        clonedElement.find('[name]').each(function(){
            //increment index
            $(this).attr('name', $(this).attr('name').replace(/(\d+)/, function(str, substr){return (parseInt(substr) + 1)}));

            //reset inputs
            if($(this).attr('type') == 'radio') {
                if($(this).attr('value') == 1){
                    $(this).prop('checked', true);
                }else{
                    $(this).prop('checked', false);
                }
            }else if($(this).attr('type') == 'text'){
                $(this).val('');
            }else{
                //unit dropdown
                $(this).val(1);
            }

            //enable size inputs, disable inputs for oercentage
        });
        clonedElement.find('[for]').each(function(){
            //update indices in for attribute
            $(this).attr('for', $(this).attr('for').replace(/(\d+)/, function(str, substr){return (parseInt(substr) + 1)}));
        });

        //remove errors in cloned elements
        clonedElement.find('.text-danger').each( function () {
            $(this).html('');
        });

        //add new element
        elementToClone.after(clonedElement);

        //get input index
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
            //hide remove button if only 1 hdd partition is displayed
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
                //update indices in ids
                $(this).attr('id', $(this).attr('id').replace(/(\d+)/, index));
            });
            partition.find('[name]').each(function(){
                //update indices in inputs
                $(this).attr('name', $(this).attr('name').replace(/(\d+)/, index));
            });
            partition.find('[for]').each(function(){
                //update indices in labels
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
            enableDisableHDDInputs(partitionIndex);
        } else if($("#hdd_percentage_radio_" + partitionIndex).is(':checked')){
            enableDisableHDDInputs(partitionIndex, false);
        }
    }

    function enableDisableHDDInputs(pIndex, sizeSelected = true) {
        var sizeProp = sizeSelected ? false : true;
        var percentProp = sizeSelected ? true : false;

        //percentage inputs
        $("#hdd_used_percentage_" + pIndex).prop('disabled', percentProp);
        $("#hdd_free_percentage_" + pIndex).prop('disabled', percentProp);

        //size inputs
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
            enableDisableMemoryInputs();
        } else if($("#memory_percentage_radio").is(':checked')){
            enableDisableMemoryInputs(false);
        }
    }

    function enableDisableMemoryInputs(sizeSelected = true) {
        var sizeProp = sizeSelected ? false : true;
        var percentProp = sizeSelected ? true : false;

        //percentage inputs
        $("#memory_percentage_section").find('input[type=text]').each( function () { 
            $(this).prop('disabled', percentProp);
        });

        //used inputs
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
            enableDisableCPUInputs();
        } else if($("#other_os_radio").is(':checked')){
            enableDisableCPUInputs(false);
        }
    }

    function enableDisableCPUInputs(linuxSelected = true) {
        var linuxProp = linuxSelected ? false : true;
        var othersProp = linuxSelected ? true : false;

        //linux inputs
        $("#linux_usage").find('input[type=text]').each( function() {
            $(this).prop('disabled', linuxProp);
        });

        //others inputs
        $("input[name=other_os_percentage").prop('disabled', othersProp);
    }

    //Values calculations/conversions for HDD usage and Memory usage and CPU Usage

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
                //get the memory values for calculation
                var useUnit = getFloatValueForHDD(parent.find('.hdd_used_unit').val());
                var useValueInBytes = convertToBytes(getFloatValueForHDD(parent.find('.hdd_used').val()), useUnit);
                var freeUnit = useUnit;
                var freeValue = getFreeSizeValue(totalValueInBytes, useValueInBytes, freeUnit);

                //set value of free size
                parent.find('.hdd_free_unit > option[value=' + useUnit + ']').prop('selected', true);
                parent.find('.hdd_free').val(freeValue);

                //set value of percentage
                percentage = parseFloat(((useValueInBytes *  100)/ totalValueInBytes)).toFixed(2);
                var freePercentage = parseFloat(100 - percentage).toFixed(2);
                parent.find('.hdd_used_percentage').val(percentage);
                parent.find('.hdd_free_percentage').val(freePercentage);

            } else if (parent.find('.hdd_percentage_radio').is(':checked') && parent.find('.hdd_used_percentage').val() != ''){

                percentage = parseFloat(parent.find('.hdd_used_percentage').val().toString());
                //set value of free percentage
                var freePercentage = parseFloat(100 - percentage).toFixed(2);
                parent.find('.hdd_free_percentage').val(freePercentage); 

                //set unit of free size and used size
                parent.find('.hdd_free_unit > option[value=' + totalUnit + ']').prop('selected', true);
                parent.find('.hdd_used_unit > option[value=' + totalUnit + ']').prop('selected', true);

                
                //calculate the used and free size
                var useValue = ((percentage * totalValue) / 100).toFixed(2);
                var freeValue = (totalValue - useValue).toFixed(2);
                //set value on screen
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
                    //convert total hdd size to Bytes
                    totalValueInBytes = convertToBytes(getFloatValueForHDD(partition.find('.hdd_total').val()), getFloatValueForHDD(partition.find('.hdd_total_unit').val()));
                    //convert parition usage size to Bytes
                    useValueInBytes = convertToBytes(getFloatValueForHDD(getFloatValueForHDD(partition.find('.hdd_used').val())), getFloatValueForHDD(partition.find('.hdd_used_unit').val()));

                    hddTotalSummation += totalValueInBytes;
                    hddUsedSummation += useValueInBytes;
                }
        });

        if (hddTotalSummation != 0) {
            //calculate total hdd usage
            var totalUsagePercentage = (hddUsedSummation / hddTotalSummation) * 100;
            totalUsagePercentage = totalUsagePercentage.toFixed(2);  

            //get status
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
        //get percentage
        if ($("#other_os_radio").is(':checked') && $("input[name=other_os_percentage]").val() != ''){
            percentage = getFloatValue($("input[name=other_os_percentage]"));
        } else if ($("#linux_radio").is(':checked')  && ($("#us").val() != '' || $("#ni").val() != '' || $("#sy").val() != '')) {
            percentage = (getFloatValue($("#us")) + getFloatValue($("#ni")) + getFloatValue($("#sy")))/3;
        }
        //get status
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

                //get the memory values for calculation
                var useUnit = getFloatValue($("#memory_used_unit > option:selected"));
                var useValueInBytes = convertToBytes(getFloatValue($("#memory_used")), useUnit);
                var freeUnit = useUnit;
                var freeValue = getFreeSizeValue(totalValueInBytes, useValueInBytes, freeUnit);

                //calculate and set value of free usage
                $("#memory_free_unit > option[value=" + useUnit + "]").prop('selected', true);  //set the unit of free size to the same unit with usage size
                $("input[name=memory_free]").val(freeValue);

                //set value of memory percentage
                percentage = parseFloat(((useValueInBytes *  100)/ totalValueInBytes)).toFixed(2);
                var freePercentage = parseFloat(100 - percentage).toFixed(2);
                $("input[name=memory_used_percentage]").val(percentage);
                $("input[name=memory_free_percentage]").val(freePercentage);

            } else if ($("#memory_percentage_radio").is(':checked') && $("#memory_used_percentage").val() != '') {
                percentage = parseFloat($("#memory_used_percentage").val().toString());
                //set value of free percentage
                var freePercentage = parseFloat(100 - percentage).toFixed(2);
                $("#memory_free_percentage").val(freePercentage);

                //set unit of free size and used size
                $("#memory_free_unit option[value=" + totalUnit + "]").prop('selected', true);
                $("#memory_used_unit option[value=" + totalUnit + "]").prop('selected', true);

                //calculate the used and free size
                var useValue = ((percentage * totalValue) / 100).toFixed(2);
                var freeValue = (totalValue - useValue).toFixed(2);

                //set value on screen
                $("#memory_used").val(useValue);
                $("#memory_free").val(freeValue);
            }

            //get status
            changeStatusBasedOnUsage($("#memory_status"), $("input[name=memory_status]"), percentage);
        }
    }
    
//******************************************** Common Functions ********************************************/

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
                //Normal
                statusSelector.text(SERVER_STATUS_NAME_1).removeClass("text-danger text-primary").addClass("text-black");
                inputSelector.val(1)
            }else if (percentage <= SERVER_STATUS_2_MAX_VALUE) {
                //Stable
                statusSelector.text(SERVER_STATUS_NAME_2).removeClass("text-danger text-black").addClass("text-primary");
                inputSelector.val(2)
            }else {
                //Critical
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
        //all
        $("#usage").find('input').each( function () {
            $(this).prop('disabled', false);
        });

        $("#usage").find('select').each( function () {
            $(this).prop('disabled', false);
        });
    }


    //==================================form submission
    $("#server_reg_form").on('submit', function () {

        //enable disabled fields before submission
        enableAllFields();
        $("input[name=partitions_count]").val($(".partition_section").length);

    });

});

