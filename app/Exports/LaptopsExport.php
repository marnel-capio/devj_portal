<?php

namespace App\Exports;

use App\Models\Employees;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\FromView;

class LaptopsExport implements FromView
{
    use Exportable;
    
    public function view(): View
    {
        return view('laptops.download')->with(['detail' => Employees::getEmployeeLaptopHistory()]);
    }
}
