const SERVER_STATUS_1_MAX_VALUE = 60;
const SERVER_STATUS_2_MAX_VALUE = 89;
const SERVER_STATUS_3_MAX_VALUE = 100;
const SERVER_STATUS_NAME_1 = 'Normal';
const SERVER_STATUS_NAME_2 = 'Stable';
const SERVER_STATUS_NAME_3 = 'Critical';
const KB_TO_BYTES = 1024; //binary system is used 

$(document).ready(function () {

//******************************************** Server List ********************************************/

    var serverList = $("#server-list").DataTable({
        "stateSave": true,
        // "searching": false,
        "pageLength": 25,
        "oLanguage": {
            "sEmptyTable": "No Data"
        },
        "sDom": "rtip"
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
            serverList.column(3)
                        .search(filter, true, false)
                        .draw();
        }
    })

    $("#search-input").on('input', function () {
        var filterVal = $(this).val();
        console.log(filterVal);
        serverList.search(filterVal).draw();
    })

    function clearFilter(){
        serverList.column(3).search('').draw();
        serverList.search('').draw();
    }
    
//******************************************** Server Regist/Update ********************************************/

    const BYTES_CONVERTION_MULTIPLIER = 1024;

    /**
     * add new partition
     */
    $("#add_partition").click(function (e) {
        
        //get last partition to clone
        var elementToClone = $(".partition_section").last();
        var clonedElement = elementToClone.clone();
        console.log(clonedElement);

        clonedElement.find('[id]').each(function(){
            //update indices in ids
            $(this).attr('id', $(this).attr('id').replace(/(\d+)/,function(str,substr){return (parseInt(substr,10)+1)}));
        });
        clonedElement.find('[name]').each(function(){
            //increment index
            $(this).attr('name', $(this).attr('name').replace(/(\d+)/,function(str,substr){return (parseInt(substr,10)+1)}));

            //reset inputs
            if($(this).attr('type') == 'radio') {
                if($(this).attr('value') == 1){
                    $(this).prop('checked', true);
                }else{
                    $(this).prop('checked', false);
                }
            }else if($(this).attr('type') == 'text'){
                $(this).attr('value', '');
            }else{
                //unit dropdown
                $(this).attr('value', 1);
            }
        });
        clonedElement.find('[for]').each(function(){
            //update indices in for attribute
            $(this).attr('for', $(this).attr('for').replace(/(\d+)/,function(str,substr){return (parseInt(substr,10)+1)}));
        });

        //add new element
        elementToClone.after(clonedElement);

        e.preventDefault();
    });

    /**
     * removing hdd partitions
     */
    $("#hdd_partitions").on('click', '.remove_partition', function (e) {
        $(this).parents('.partition_section').remove();
        e.preventDefault();
    });

    /**
     * disabling size/percentage input in hdd partition section
     */
    $("#hdd_partitions").on('click', '.hdd_select_radio', function () {
        var partitionIndex = $(this).attr('id').slice(-1);

        if($("#hdd_size_radio_" + partitionIndex).is(':checked')){
            enableDisableHDDInputs(partitionIndex);
        } else if($("#hdd_percentage_radio_" + partitionIndex).is(':checked')){
            enableDisableHDDInputs(partitionIndex, false);
        }
    });

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
    $("input[name=memory_input_type]").change(function () {
        if($("#memory_size_radio").is(':checked')){
            enableDisableMemoryInputs();
        } else if($("#memory_percentage_radio").is(':checked')){
            enableDisableMemoryInputs(false);
        }
    });

    function enableDisableMemoryInputs(sizeSelected = true) {
        var sizeProp = sizeSelected ? false : true;
        var percentProp = sizeSelected ? true : false;

        //percentage inputs
        $("#memory_used_percentage").prop('disabled', percentProp);
        $("#memory_free_percentage").prop('disabled', percentProp);

        //used inputs
        $("#memory_used").prop('disabled', sizeProp);
        $("#memory_used_unit").prop('disabled', sizeProp);
        $("#memory_free").prop('disabled', sizeProp);
        $("#memory_free_unit").prop('disabled', sizeProp);
    }

    /**
     * disabling linux/others input in memory section
     */
    $("input[name=os_type]").change(function () {
        if($("#linux_radio").is(':checked')){
            enableDisableCPUInputs();
        } else if($("#other_os_radio").is(':checked')){
            enableDisableCPUInputs(false);
        }
    });

    function enableDisableCPUInputs(linuxSelected = true) {
        var linuxProp = linuxSelected ? false : true;
        var othersProp = linuxSelected ? true : false;

        //linux inputs
        $("#us").prop('disabled', linuxProp);
        $("#ny").prop('disabled', linuxProp);
        $("#sy").prop('disabled', linuxProp);

        //others inputs
        $("input[name=other_os_percentage").prop('disabled', othersProp);
    }

    //Values calculations/conversions for HDD usage and Memory usage and CPU Usage

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
    $("#ny").on('input', function () { 
        calculateAndSetCPUStatus();
    });

    $("input[name=other_os_percentage]").on('input', function () { 
        calculateAndSetCPUStatus();
    });

    function calculateAndSetCPUStatus(){
        var percentage;
        //get percentage
        if ($("#other_os_radio").is(':checked') && $("input[name=other_os_percentage]").val() != ''){
            percentage = getIntValue($("input[name=other_os_percentage]"));
        } else if ($("#linux_radio").is(':checked')  && ($("#us").val() != '' || $("#ny").val() != '' || $("#sy").val() != '')) {
            percentage = (getIntValue($("#us")) + getIntValue($("#ny")) + getIntValue($("#sy")))/3;
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
            var totalUnit = getIntValue($("#memory_total_unit > option:selected"));
            var totalvalue = getIntValue($("#memory_total"));
            var totalValueInBytes = convertToBytes(totalvalue, totalUnit);

            if ($("#memory_size_radio").is(':checked') && $("#memory_used").val() != '' && $("#memory_used_unit").val() != '') {

                //get the memory values for calculation
                var useUnit = getIntValue($("#memory_used_unit > option:selected"));
                var useValueInBytes = convertToBytes(getIntValue($("#memory_used")), useUnit);
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

                console.log('percentage');
                console.log('used:  ' +percentage);
                console.log('free:  ' +freePercentage);
                
                console.log('size');
                console.log('used in bytes:  ' +useValueInBytes);
                console.log('free in selected unit:  ' +freeValue);

            } else if ($("#memory_percentage_radio").is(':checked') && $("#memory_used_percentage").val() != '') {
                percentage = parseFloat($("#memory_used_percentage").val().toString());
                //set value of free percentage
                var freePercentage = parseFloat(100 - percentage).toFixed(2);
                $("#memory_free_percentage").val(freePercentage);


                //set unit of free size and used size
                $("#memory_free_unit option[value=" + totalUnit + "]").prop('selected', true);
                $("#memory_used_unit option[value=" + totalUnit + "]").prop('selected', true);

                //calculate the used and free size
                var useValue = ((percentage * totalvalue) / 100).toFixed(2);
                var freeValue = (totalvalue - useValue).toFixed(2);
                //set value on screen
                $("#memory_used").val(useValue);
                $("#memory_free").val(freeValue);

                console.log('converted size from percentage');
                console.log('used:  ' +useValue);
                console.log('free:  ' +freeValue);
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
    function getIntValue(selector) {
        var parsed = parseInt(selector.val().toString());
        return isNaN(parsed) ? 0 : parsed;
    }

    /**
     * 
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



});

