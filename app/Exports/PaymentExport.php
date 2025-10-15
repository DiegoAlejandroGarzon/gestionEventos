<?php

namespace App\Exports;

use App\Models\City;
use App\Models\EventAssistant;
use App\Models\Payment;
use App\Models\UserEventParameter;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class PaymentExport implements FromCollection, WithHeadings
{
    protected $eventId;
    protected $selectedFields;
    protected $additionalParameters;
    protected $search;
    protected $paymentStatus;

    public function __construct($eventId, $selectedFields, $additionalParameters, $search = null, $paymentStatus = false)
    {
        $this->eventId = $eventId;
        $this->selectedFields = $selectedFields;
        $this->additionalParameters = $additionalParameters;
        $this->search = $search;
        $this->paymentStatus = $paymentStatus;
    }

    public function collection()
    {
        // Obtener asistentes filtrados por evento y búsqueda
        $query = EventAssistant::select(
            'event_assistants.id',
            'event_assistants.has_entered',
            'user_id',
            'event_id',
            'events.*',
            'users.*',
            'events.name as event_name',
            'events.status as event_status',
            'events.status as event_city_id',
            'payments.*',
            'event_assistants.is_paid',
            )
            ->join('users', 'event_assistants.user_id', '=', 'users.id')
            ->join('events', 'event_assistants.event_id', '=', 'events.id')
            ->with('eventParameters')
            ->where('event_assistants.event_id', $this->eventId);
        if(!$this->paymentStatus){
            $query
            ->join('payments', 'event_assistants.id', '=', 'payments.event_assistant_id')
            ->whereHas('payments');
        }else{
            $query->leftjoin('payments', 'event_assistants.id', '=', 'payments.event_assistant_id');
        }
        if ($this->search) {
            $search = $this->search;
            $query->where(function ($q) use ($search) {
                // Buscar en la relación 'user'
                $q->whereHas('user', function ($query) use ($search) {
                    $query->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%")
                            ->orWhere('phone', 'like', "%{$search}%");
                });
                // Buscar en la relación 'ticketType'
                $q->orWhereHas('ticketType', function ($query2) use ($search) {
                    $query2->where('name', 'like', "%{$search}%");
                });
                // Verificar si 'search' contiene "Entrada" y filtrar por 'has_entered'
                if (strtolower($search) === 'entrada') {
                    $q->orWhere('has_entered', 1); // Buscar entradas con valor 1
                } elseif(strtolower($search) === 'no entrada') {
                    $q->orWhere('has_entered', 0); // Buscar entradas con valor 0
                }

                // Verificar si 'search' contiene "pagado", "no pagado" o "pendiente"
                if (strtolower($search) === 'pagado') {
                    $q->orWhere(function ($q) {
                        $q->where('is_paid', true); // Buscar asistente con is_paid = true
                    });
                } elseif (strtolower($search) === 'no pagado') {
                    $q->orWhere(function ($q) {
                        $q->where('is_paid', false)
                          ->whereDoesntHave('payments'); // No hay pagos registrados
                    });
                } elseif (strtolower($search) === 'pendiente') {
                    $q->orWhere(function ($q) {
                        $q->where('is_paid', false)
                          ->whereHas('payments'); // Tiene al menos un pago registrado
                    });
                }
            });
        }
        $users = $query->get();
        // Construir colección de filas para el Excel
        $rows = [];
        $index = 1;
        foreach ($users as $user) {
            $row = [];
            $row[] = $index;
            // Agregar valores de los campos seleccionados
            foreach ($this->selectedFields as $field) {
                $row[] = $user->$field;
            }
            // Agregar valores de los parámetros adicionales
            foreach ($this->additionalParameters as $parameter) {
                $userParameter = UserEventParameter::where('user_id', $user->user_id)
                    ->where('event_id', $this->eventId)
                    ->where('additional_parameter_id', $parameter['id'])
                    ->first();
                $row[] = $userParameter ? $userParameter->value : '-';
            }
            $row = array_merge($row, [
                // $user->has_entered == false ? 'No entrada' : 'Entrada',
                // $user->event_name,
                // $user->event_date,
                // City::find($user->event_city_id)->name,
                // $user->address,
                $user->is_paid ? 'PAGADO' : ($user->totalPayments() == 0 ? 'NO PAGADO' : 'PENDIENTE'),
                $user->payer_name,
                $user->payer_document_type,
                $user->payer_document_number,
                $user->amount,
                $user->payment_method,
                $user->payment_proof ? 'SI' : 'NO',
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

        $headings[] = 'N';
        foreach ($this->selectedFields as $field) {
            $headings[] = ucfirst(str_replace('_', ' ', $field));
        }

        // Construir encabezados para los parámetros adicionales
        foreach ($this->additionalParameters as $parameter) {
            $headings[] = ucfirst(str_replace('_', ' ', $parameter['name']));
        }
        $headings = array_merge($headings, [
            // 'Estado',
            // 'Nombre Evento',
            // 'Fecha Evento',
            // 'Ciudad Evento',
            // 'Direccion Evento',
            'STATUS DE PAGO',
            'Nombre del pagador',
            'Tipo documento del pagador',
            'Numero documento del pagador',
            'Valor',
            'Metodo Pago',
            '¿Tiene archivo de soporte?'
        ]);
        return $headings;
    }
}
