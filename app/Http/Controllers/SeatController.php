<?php

namespace App\Http\Controllers;

use App\Imports\SeatsImport;
use App\Models\Event;
use App\Models\EventAssistant;
use App\Models\Seat;
use App\Models\TicketType;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class SeatController extends Controller
{

    public function index($idEvent)
    {
        // Obtener todos los tipos de ticket asociados al evento
        $ticketTypes = TicketType::where('event_id', $idEvent)->get();
        $event = Event::find($idEvent);

        // return view('seats.index', compact('ticketTypes', 'event'));
        return view('seats.indexCuadricula', compact('ticketTypes', 'event'));
    }

    // Método para obtener asientos por tipo de ticket (AJAX)
    public function getSeatsByTicketType($ticketTypeId)
    {
        // Obtener los asientos asociados al tipo de ticket con los datos del usuario
        $ticketType = TicketType::with('seats.eventAssistant.user')->findOrFail($ticketTypeId);

        // Mapear los asientos para incluir detalles adicionales
        $seats = $ticketType->seats->map(function ($seat) {
            return [
                'id' => $seat->id,
                'row' => $seat->row,
                'column' => $seat->column,
                'is_assigned' => $seat->is_assigned,
                'event_assistant' => $seat->eventAssistant ? [
                    'id' => $seat->eventAssistant->id,
                    'name' => $seat->eventAssistant->user->name ?? null, // Obtener nombre del usuario
                    'lastname' => $seat->eventAssistant->user->lastname ?? null, // Obtener email del usuario
                    // Agrega otros campos de usuario si es necesario
                ] : null,
            ];
        });

        // Devolver los datos en formato JSON
        return response()->json($seats);
    }

    // Asignar un asiento a un asistente

    public function assignSeat(Request $request, $seatId)
    {
        $seat = Seat::findOrFail($seatId);
        $seat->event_assistant_id = $request->input('event_assistant_id');
        $seat->is_assigned = true;
        $seat->save();

        return redirect()->route('seats.index', ['idEvent' => $seat->ticketType->event_id])->with('success', 'Asiento asignado correctamente');
    }

    // Liberar un asiento
    public function unassignSeat($seatId)
    {
        $seat = Seat::findOrFail($seatId);
        $seat->event_assistant_id = null;
        $seat->is_assigned = false;
        $seat->save();

        return redirect()->back()->with('success', 'Asiento liberado exitosamente.');
    }

    // Mostrar el formulario de carga
    public function showUploadForm($idEvent)
    {
        $ticketTypes = TicketType::where('event_id', $idEvent)->get();
        return view('seats.upload', compact('idEvent', 'ticketTypes'));
    }

    // Procesar la carga del archivo Excel
    public function uploadExcel(Request $request, $idEvent)
    {
        $request->validate([
            'excelFile' => 'required|mimes:xlsx,xls',
            'ticketTypeId' => 'required|exists:ticket_types,id', // Validación para el tipo de ticket
        ]);

        $ticketTypeId = $request->ticketTypeId;

        // Procesa el archivo usando una importación de Laravel Excel
        Excel::import(new SeatsImport($ticketTypeId), $request->file('excelFile'));

        return redirect()->route('seats.index', $idEvent)->with('success', 'Silletería cargada correctamente.');
    }
    public function getEventAssistants($ticketTypeId)
    {
        // Obtener los EventAssistants con la relación de usuario
        $assistants = EventAssistant::where('ticket_type_id', $ticketTypeId)
            ->with('user')
            ->get()
            ->map(function ($assistant) {
                return [
                    'id' => $assistant->id,
                    'name' => $assistant->user->name, // Accede al nombre del usuario relacionado
                    'lastname' => $assistant->user->lastname, // Accede al nombre del usuario relacionado
                ];
            });

        return response()->json($assistants);
    }
}
