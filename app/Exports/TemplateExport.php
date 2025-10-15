<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

class TemplateExport implements FromArray, WithHeadings
{
    protected $headers;

    public function __construct(array $headers)
    {
        $this->headers = $headers;
    }

    /**
     * Retorna los encabezados para la exportación.
     *
     * @return array
     */
    public function headings(): array
    {
        return array_values($this->headers);
    }

    /**
     * Retorna un array vacío para que sea una plantilla.
     *
     * @return array
     */
    public function array(): array
    {
        return [];
    }
}
