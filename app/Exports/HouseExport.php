<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class HouseExport implements FromView
{
    protected $exportData;
    public function __construct(array $exportData)
    {
        $this->exportData = $exportData;
    }
    /**
     * @return \Illuminate\Support\Collection
     */
    public function view(): View
    {
        return view('exports.houseExport', [
            'exportData' => $this->exportData
        ]);
    }
}
