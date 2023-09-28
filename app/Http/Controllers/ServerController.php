<?php

namespace App\Http\Controllers;

use App\Exports\ServerExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\ServerRequest;
use App\Models\Logs;
use App\Models\Servers;
use App\Models\ServersPartitions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ServerController extends Controller
{
    /**
     * Display the servers
     *
     * @return void
     */
    public function index () {
        abort_if(Auth::user()->roles != config('constants.MANAGER_ROLE_VALUE') && !Auth::user()->server_manage_flag, 403);

        return view('servers.index', [
            'serverData' => Servers::getAllServer(),
        ]);
    }

    public function download () {
        abort_if(Auth::user()->roles != config('constants.MANAGER_ROLE_VALUE') && !Auth::user()->server_manage_flag, 403);
        Logs::createLog('Server', 'Downloaded Server Details for ' .date('F Y'));

        $fileName = 'Dev J Server Capacity Monitoring_' .date('MY') .'.xlsx';        

        $activeServerCount = Servers::selectRaw('id')
                                ->from('servers')
                                ->where('status', 1)
                                ->count();

        // Return error if there are no active server
        if(empty($activeServerCount) || $activeServerCount < 1){
            session(['download_alert' => 'There are no active servers found!']);

            return view('servers.index', [
                'serverData' => Servers::getAllServer(),
            ]);
        }
        

        return (new ServerExport())->download($fileName);
    }

    /**
     * Display the server detail
     *
     * @param [type] $id
     * @return void
     */
    public function details ($id) {
        // Check if logged-in user is Manager or Server Manager
        abort_if(Auth::user()->roles != config('constants.MANAGER_ROLE_VALUE') && !Auth::user()->server_manage_flag, 403);

        $serverData = Servers::selectRaw('s.*, CONCAT(e.first_name, " ", e.last_name) AS updater')
                                ->from('servers AS s')
                                ->leftjoin('employees AS e', 'e.id', 's.updated_by')
                                ->where('s.id', $id)
                                ->first();

        // Abort if server ID is not valid
        abort_if(empty($serverData), 404);

        $partitionData = ServersPartitions::where('server_id', $id)->get()->toArray();

        return view('servers.details', [
            'serverData' => $serverData,
            'partitionData' => $partitionData,
            'detailNote' => 'Last updated by ' .$serverData->updater,
        ]);
    }

    /**
     * Display Server Registration screen
     *
     * @return void
     */
    public function create () {
        abort_if(Auth::user()->roles != config('constants.MANAGER_ROLE_VALUE') && !Auth::user()->server_manage_flag, 403);

        return view('servers.create', ['forUpdate' => false]);
    }

    /**
     * Server Registration Process
     *
     * @param ServerRequest $request
     * @return void
     */
    public function regist (ServerRequest $request) {
        //request validation
        $request->validated();

        //get data from request
        $data = $request->except(['_token']);
        
        // Validate if HDD is not empty
        if(empty($data['hdd'])){
            return view('error.requestError')
                        ->with([
                            'error' => "The server cannot be registered without an HDD partition."
                        ]);
        }

        //save data in servers table
        $serverData = $this->extractServerDataFromRequest($data, false);
        $serverId = Servers::create($serverData)->id;

        //save hdd partition data
        foreach ($data['hdd'] as $idx => $paritionData) {
            $hdd = $this->extractHddPartitionDataFromRequest($paritionData, false);
            $hdd['server_id'] = $serverId;
            
            ServersPartitions::create($hdd);
        }

        Logs::createLog('Server', 'Server Registration');

        session(['regist_update_alert' => 'Server was successfully registered!']);
        return redirect(route('servers.details', ['id' => $serverId]));
    }

    /**
     * Display the server update screen
     *
     * @param [type] $id
     * @return void
     */
    public function edit ($id) {
        abort_if(Auth::user()->roles != config('constants.MANAGER_ROLE_VALUE') && !Auth::user()->server_manage_flag, 403);

        $serverData = Servers::where('id', $id)->first();
        $partitionData = ServersPartitions::where('server_id', $id)->get()->toArray();

        return view('servers.create', [
            'serverData' => $serverData,
            'partitionData' => $partitionData,
            'forUpdate' => true,
        ]);
    }

    /**
     * Server Update Process
     *
     * @param ServerRequest $request
     * @return void
     */
    public function store (ServerRequest $request) {
        //request validation
        $request->validated();
        //get data from request
        $data = $request->except(['_token']);
        $serverId = $data['id']; 
        abort_if(empty($serverId), 403);
        //update server data
        $serverData = $this->extractServerDataFromRequest($data);
        Servers::where('id', $serverId)->update($serverData);

        //hdd partitions
        $partitionsFromRequest = $data['hdd'];
        $partitionsFromDB = ServersPartitions::where('server_id', $serverId)->get()->toArray();

        $partitionIdsFromRequest = array_filter(array_column($partitionsFromRequest, 'id'));
        $partitionIdsFromDB = array_filter(array_column($partitionsFromDB, 'id'));
        $deletedPartitionIds = array_diff($partitionIdsFromDB, $partitionIdsFromRequest);

        //delete removed partitions
        ServersPartitions::whereIn('id', $deletedPartitionIds)->delete();

        //process other partitions from request
        foreach ($partitionsFromRequest as $idx => $partition) {
            if (!empty($partition['id']) && in_array($partition['id'], $deletedPartitionIds)) {
                continue;
            }

            $hdd = $this->extractHddPartitionDataFromRequest($partition);

            if ($partition['id']) {
                //update existing partition data
                ServersPartitions::where('id', $partition['id'])->update($hdd);
            } else {
                //add new partition data
                $hdd['server_id'] = $serverId;
                ServersPartitions::create($hdd);
            }
        }

        //create log
        Logs::createLog('Server', 'Server Data Update');

        session(['regist_update_alert' => 'Server was successfully updated!']);
        return redirect(route('servers.details', ['id' => $serverId]));
    }

    /**
     * extract the server data from the request
     *
     * @param array $data = request data
     * @param boolean $forUpdate: new registration:false, for update: true
     * @return void
     */
    private function extractServerDataFromRequest ($data, $forUpdate = true) {
        $serverData = [
            'server_name' => $data['server_name'],
            'server_ip' => $data['server_ip'],
            'function_role' => $data['function_role'],
            'os' => $data['os'],
            'cpu' => $data['cpu'],
            'motherboard' => $data['motherboard'],
            'memory' => $data['memory'],
            'hdd' => $data['server_hdd'],
            'memory_used_size' => $data['memory_used'],
            'memory_used_size_type' => $data['memory_used_unit'],
            'memory_used_percentage' => $data['memory_used_percentage'],
            'memory_free_size' => $data['memory_free'],
            'memory_free_size_type' => $data['memory_free_unit'],
            'memory_free_percentage' => $data['memory_free_percentage'],
            'memory_total' => $data['memory_total'],
            'memory_total_size_type' => $data['memory_total_unit'],
            'os_type' => $data['os_type'],
            'hdd_status' => $data['hdd_status'],
            'ram_status' => $data['memory_status'],
            'cpu_status' => $data['cpu_status'],
            'remarks' => $data['remarks'],
            'status' => isset($data['status']) && $data['status'] ? 1 : 0,
            'updated_by' => Auth::user()->id,
        ];
        
        if($data['os_type'] == 1)
        {
                $serverData['linux_us_percentage'] = $data['us'];
                $serverData['linux_ni_percentage'] = $data['ni'];
                $serverData['linux_sy_percentage'] = $data['sy'];
                $serverData['other_os_percentage'] = null;
        } else {
                $serverData['linux_us_percentage'] = null;
                $serverData['linux_ni_percentage'] = null;
                $serverData['linux_sy_percentage'] = null;
                $serverData['other_os_percentage'] = $data['other_os_percentage'];
        }

        if (!$forUpdate) {
            //for new registration
            $serverData['created_by'] = Auth::user()->id;
        }

        return $serverData;
    }

    /**
     * Extract HDD partition data from request
     *
     * @param array $hddData
     * @param boolean $forUpdate
     * @return void
     */
    private function extractHddPartitionDataFromRequest ($hddData, $forUpdate = true) {
        $hdd = [
            'hdd_partition' => $hddData['partition_name'],
            'hdd_used_size' => $hddData['used'],
            'hdd_used_size_type' => $hddData['used_unit'],
            'hdd_used_percentage' => $hddData['used_percentage'],
            'hdd_free_size' => $hddData['free'],
            'hdd_free_size_type' => $hddData['free_unit'],
            'hdd_free_percentage' => $hddData['free_percentage'],
            'hdd_total' => $hddData['total'],
            'hdd_total_size_type' => $hddData['total_unit'],
            'updated_by' => Auth::user()->id,
        ];

        if (!$forUpdate) {
            //for new registration
            $hdd['created_by'] = Auth::user()->id;
        }
        return $hdd;
    }
}
