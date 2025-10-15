<?php

namespace App\Exports;

use App\Models\City;
use App\Models\EventAssistant;
use App\Models\UserEventParameter;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class EventAssistantsExport implements FromCollection, WithHeadings
{
    protected $eventId;
    protected $selectedFields;
    protected $additionalParameters;
    protected $search;

    public function __construct($eventId, $selectedFields, $additionalParameters, $search = null)
    {
        $this->eventId = $eventId;
        $this->selectedFields = $selectedFields;
        $this->additionalParameters = $additionalParameters;
        $this->search = $search;
    }

    public function collection()
    {
        // Obtener asistentes filtrados por evento y búsqueda
        $query = EventAssistant::select(
            'event_assistants.id',
            'event_assistants.has_entered',
            'user_id', 'event_id',
            'events.*',
            'users.*',
            'events.name as event_name',
            'events.status as event_status',
            'events.status as event_city_id',
            )
            ->join('users', 'event_assistants.user_id', '=', 'users.id')
            ->join('events', 'event_assistants.event_id', '=', 'events.id')
            ->with('eventParameters')
            ->where('event_assistants.event_id', $this->eventId);
        if ($this->search) {
            $query->whereHas('user', function ($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                    ->orWhere('email', 'like', '%' . $this->search . '%');
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
                $user->has_entered == false ? 'No entrada' : 'Entrada',
                $user->event_name,
                $user->event_date,
                City::find($user->event_city_id)?->name,
                $user->address,
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
            'Estado',
            'Nombre Evento',
            'Fecha Evento',
            'Ciudad Evento',
            'Direccion Evento'
        ]);
        return $headings;
    }
}
