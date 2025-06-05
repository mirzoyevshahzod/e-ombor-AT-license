<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

class TransitExport implements FromArray, WithHeadings
{
    protected $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function array(): array
    {
        return $this->data;
    }

    public function headings(): array
    {
        return [
            'Document Number',
            'Custom Code',
            'Custom Date',
            'TEBHN Number',
            'Transport Number',
            'Gross Weight',
            'Recipient Name',
            'Delivery Post',
            'Delivery Date',
            'Arrival Place',
            'Status'
        ];
    }
}