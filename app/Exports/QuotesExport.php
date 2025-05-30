<?php

namespace App\Exports;

// app/Exports/QuotesExport.php

use App\Models\Quote;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class QuotesExport implements FromCollection, WithHeadings
{
    public function collection()
    {
        return Quote::select('text', 'author', 'tags')->get()->map(function ($q) {
            return [
                $q->text,
                $q->author,
                implode(', ', $q->tags ?? []),
            ];
        });
    }

    public function headings(): array
    {
        return ['Text', 'Author', 'Tags'];
    }
}
