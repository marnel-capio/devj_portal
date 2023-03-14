@include('header')

<div class="container mt-4 mb-4 ms-5">
    <h3>Guide in filling out the Capacity Monitoring Form</h3>
    <div class="ps-3 pt-2">
        <p>
            <span class="fw-bold">Frequency of getting data:</span> Monthly <br>
            <span class="fw-bold">Responsible:</span> BU server admin <br>
            <span class="fw-bold">Filename:</span> Capacity Monitoring YYYY (DEVx).xls <br>
        </p>

        <div class="mb-3">
            <h5>※Filling out the Server Details</h5>
            <div class="ps-4">
                <span class="fw-semibold">Server Name: </span> Server name or host name <br>
                <span class="fw-semibold">IP Address: </span> Private IP address of the server  <br>
                <span class="fw-semibold">Operating System: </span>  Specify OS version and build  <br>
                <span class="fw-semibold">Motherboard: </span> Motherboard  <br>
                <span class="fw-semibold">Processor: </span> Model and speed  <br>
                <span class="fw-semibold">RAM: </span> in GB (include size and quantity of each)  <br>
                <span class="fw-semibold">HDD: </span> in GB  (include size and quantity of each)  <br>
                <span class="fw-semibold">Function/Role: </span> Specify server's function/role <br>
                <span class="fw-semibold">Remarks: </span>  Input any observation or details of the status <br>
            </div>
        </div>
        <div class="mb-3">
            <h5>※Filling out the HDD Utilization</h5>
            <div class="ps-4">
                <span class="fw-semibold">Windows:</span> Check properties of each drive/partition <br>
                <span class="fw-semibold">Linux:</span>  Access CLI and issue the following command: # df -h  <br>
                <span class="ps-4">※For Linux, include only the essential partitions (root, partition that has the : application, backup and data)</span>
                
            </div>
        </div>
        <div class="mb-3">
            <h5>※Filling out the Memory Utilization</h5>
            <div class="ps-4">
                <span class="fw-semibold">Windows:</span> Open task manager, go to performance tab then get Memory usage <br>
                <span class="fw-semibold">Linux:</span>  Access CLI and issue the following command: # free  <br>
            </div>
        </div>
        <div class="mb-3">
            <h5>※Filling out the CPU Utilization</h5>
            <div class="ps-4">
                <span class="fw-semibold">Windows:</span> From task manager, go to performance tab then gather CPU utilization data <br>
                <span class="ps-2">※Using snipping tool, capture data 5x with 1 minute interval. Get average %</span> <br>
                <span class="fw-semibold">Linux:</span>  Access CLI and issue the following command: # iostat <br>
                <span class="ps-5">Get only  %user; %nice; %system</span> <br>
                <span class="ps-2">※ If iostat is not available, please install </span> <br>
                <span class="ps-5">CentOS: yum install sysstat</span> <br>
                <span class="ps-5">Ubuntu/Debian: sudo apt-get install sysstat</span> <br>
            </div>
        </div>
    </div>


</div>

@include('footer')