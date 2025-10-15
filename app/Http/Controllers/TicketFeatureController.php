<?php

namespace App\Http\Controllers;

use App\Models\TicketFeatures;
use Illuminate\Http\Request;
use App\Models\TicketType;
use App\Models\Product;
use App\Models\TicketFeatureOption;
use App\Models\EventAssistant;
use App\Models\Event;
use App\Models\TicketCharacteristic;
use Spatie\Permission\Models\Role;
use Illuminate\View\View;

class TicketFeatureController extends Controller
{
    public function index(): View
    {
        $roles = Role::all();

        // Buscar por nombre si se requiere
        $query = TicketType::query();

        if (request('search')) {
            $query->where('name', 'like', '%' . request('search') . '%');
        }

        $tickets = $query->with('features')->paginate(30); // ← Cambiado a TicketType

        // Si aún quieres mostrar los destacados, puedes dejar esto:
        $featuredCategories = TicketFeatures::where('featured', true)->pluck('id');
        $ticketTypesWithFeaturedFeatures = TicketType::whereHas('features', function ($q) use ($featuredCategories) {
            $q->whereIn('ticket_features.id', $featuredCategories);
        })->get();

        $dataGeneral = [];

        foreach ($tickets as $ticket) {
            $event = Event::find($ticket->event_id);
            $sold = EventAssistant::where('ticket_type_id', $ticket->id)->where('has_entered', true)->count();
            $available = $ticket->capacity - $sold;

            $dataGeneral[$ticket->id] = [
                'availableTickets' => $available,
                'soldTickets' => $sold,
                'capacity' => $ticket->capacity,
                'eventName' => $event ? $event->name : 'Sin evento'
            ];
        }

        return view('ticketFeatures.index', compact(
            'roles',
            'tickets',
            'ticketTypesWithFeaturedFeatures',
            'dataGeneral'
        ));
    }

    public function create()
    {
        $characteristics = TicketCharacteristic::all();
        $roles = Role::all();
        $ticketTypes = TicketType::all();

        return view('ticketFeatures.create', compact('roles', 'ticketTypes', 'characteristics'));
    }
    public function edit($id)
    {
        $ticket = TicketFeatures::with(['characteristics', 'ticketTypes'])->findOrFail($id);
        $roles = Role::all();
        $ticketTypes = TicketType::all();
        $characteristics = TicketCharacteristic::all();

        return view('ticketFeatures.update', compact('roles', 'ticketTypes', 'ticket', 'characteristics'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'characteristics' => 'nullable|array',
            'ticket_types' => 'nullable|array',
        ]);

        // 🔁 Procesar características
        $characteristicIds = [];
        foreach ($request->characteristics ?? [] as $item) {
            if (is_numeric($item)) {
                $characteristicIds[] = (int) $item;
            } else {
                // Es una nueva característica
                $newChar = TicketCharacteristic::create(['name' => $item]);
                $characteristicIds[] = $newChar->id;
            }
        }

        // 🔁 Procesar tipos de ticket
        $ticketTypeIds = [];
        foreach ($request->ticket_types ?? [] as $item) {
            if (is_numeric($item)) {
                $ticketTypeIds[] = (int) $item;
            } else {
                // Es un nuevo tipo de ticket (por simplicidad, puedes ajustar valores por defecto)
                $newType = TicketType::create([
                    'name' => $item,
                    'price' => 0,
                    'capacity' => 0,
                    'event_id' => 1, // Ajusta este valor según contexto
                ]);
                $ticketTypeIds[] = $newType->id;
            }
        }

        // Crear el ticket (TicketFeatures)
        $ticket = TicketFeatures::create([
            'name' => $validated['name'],
        ]);

        // Relacionar
        $ticket->ticketTypes()->sync($ticketTypeIds);

        foreach ($ticket->ticketTypes as $type) {
            $type->characteristics()->syncWithoutDetaching($characteristicIds);
        }

        return redirect()->route('ticketFeatures.index')->with('success', 'Ticket creado con éxito.');
    }

    public function update(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:ticket_features,id',
            'name' => 'required|string|max:255',
            'characteristics' => 'nullable|array',
            'characteristics.*' => 'exists:ticket_characteristics,id',
            'ticket_types' => 'nullable|array',
            'ticket_types.*' => 'exists:ticket_types,id',
        ]);

        $ticket = TicketFeatures::findOrFail($request->id);
        $ticket->name = $request->name;
        $ticket->save();

        $ticket->characteristics()->sync($request->characteristics ?? []);

        $ticket->ticketTypes()->sync($request->ticket_types ?? []);

        return redirect()->route('ticketFeatures.index')->with('success', 'Ticket actualizado con éxito.');
    }

    public function delete($id)
    {
        $ticket = TicketFeatures::find($id);

        if (!$ticket) {
            return redirect()->route('ticketFeatures.index')->with('error', 'Ticket no encontrado');
        }

        $ticket->delete();

        return redirect()->route('ticketFeatures.index')->with('success', 'Ticket eliminado');
    }
}
