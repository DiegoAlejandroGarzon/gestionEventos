<?php

namespace App\Exports;

use App\Models\City;
use App\Models\Coupon;
use App\Models\EventAssistant;
use App\Models\Payment;
use App\Models\UserEventParameter;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class CouponExport implements FromCollection, WithHeadings
{
    protected $eventId;
    protected $selectedFields;
    protected $additionalParameters;
    protected $search;
    protected $paymentStatus;

    public function __construct($eventId)
    {
        $this->eventId = $eventId;
    }

    public function collection()
    {
        // Obtener asistentes filtrados por evento y búsqueda
        $query = Coupon::select(
            'numeric_code',
            'ticket_type_id',
            'is_consumed',
            'ticket_types.name as ticket_type_name',
            )
            ->join('ticket_types', 'coupons.ticket_type_id', '=', 'ticket_types.id')
            ->where('coupons.event_id', $this->eventId);
        $coupons = $query->get();
        // Construir colección de filas para el Excel
        $rows = [];
        $index = 1;
        foreach ($coupons as $coupon) {
            $row = [];
            $row[] = $index;
            $row = array_merge($row, [
                $coupon->numeric_code,
                $coupon->ticket_type_name,
                $coupon->is_consumed ? 'NO disponible' : 'Disponible',
            ]);
            $rows[] = $row;
            $index++;
        }
        return collect($rows);
    }


    public function headings(): array
    {
        // Construir encabezados para los campos seleccionados
        $headings = [];
        $headings = array_merge($headings, [
            'N',
            'Codigo',
            'Ticket',
            'Status',
        ]);
        return $headings;
    }
}
