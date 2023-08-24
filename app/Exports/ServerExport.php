<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;


class ServerExport implements WithMultipleSheets

{
    use Exportable;

    public function __construct()
    {
        
    }

    public function sheets(): array
    {
        return [
            new ServerCoverPageSheet(),
            new ServerDataSheet(),
            new ServerRevisionSheet(),
        ];
    }

}
