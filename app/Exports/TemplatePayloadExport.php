<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

class TemplatePayloadExport implements FromArray, WithHeadings
{
    protected $idEvent;

    public function __construct($idEvent)
    {
        $this->idEvent = $idEvent;
    }

    /**
     * Retorna los encabezados para la exportación.
     *
     * @return array
     */
    public function headings(): array
    {
        return array_values($this->idEvent);
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
