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
    
//******************************************** Server Regist/Upadate ********************************************/

    const BYTES_CONVERTION_MULTIPLIER = 1024;

    /**
     * add new partition
     */
    $("#add_partition").click(function (e) {
        
        //get last partition to clone
        var elementToClone = $(".partition_section").last();
        var clonedElement = elementToClone.clone();

        clonedElement.find('button').each(function(){
            //update indices in ids
            this.dataset.index = this.dataset.index.replace(/(\d+)/,function(str,p1){return (parseInt(p1,10)+1)});
        });
        clonedElement.find('[id]').each(function(){
            //update indices in ids
            this.id = this.id.replace(/(\d+)/,function(str,p1){return (parseInt(p1,10)+1)});
        });
        clonedElement.find('[name]').each(function(){
            //increment index
            this.name = this.name.replace(/(\d+)/,function(str,p1){return (parseInt(p1,10)+1)});

            //reset inputs
            if(this.getAttribute('type') == 'radio') {
                if(this.value == 1){
                    this.checked = true;
                }else{
                    this.checked = false;
                }
            }else if(this.getAttribute('type') == 'text'){
                this.value = '';
            }else{
                this.value = 1;
            }
        });
        clonedElement.find('[for]').each(function(){
            //update indices in for attribute
            this.setAttribute('for', this.getAttribute('for').replace(/(\d+)/,function(str,p1){return (parseInt(p1,10)+1)}));
        });

        //add new element
        elementToClone.after(clonedElement);

        e.preventDefault();
    });

    // $(".remove_partition").on('click', function (e) {
    //     console.log($(this));
    //     // console.log($(this).data('index'));
    //     e.preventDefault();
    // });

    $("#hdd_partitions").on('click', '.remove_partition', function (e) {
        console.log($(this).data('index'));
        console.log($(this).parents('.partition_section'));
        $(this).parents('.partition_section').remove();
        
        // $(this).remove();
        e.preventDefault();
    });









});

