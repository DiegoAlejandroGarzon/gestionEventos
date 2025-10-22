<?php

namespace App\Services;

use Illuminate\Http\Request;
use App\Models\Event;
use App\Models\Coupon;
use App\Models\Seat;
use App\Models\User;
use App\Models\EventAssistant;
use App\Models\TicketType;
use App\Models\Minor;
use Spatie\Permission\Models\Role;
use App\Models\AdditionalParameter;
use App\Models\UserEventParameter;
use App\Http\Controllers\EventAssistantController;

class PublicRegistrationService
{
    public function handle(Request $request, string $publicLink, bool $returnJson = false)
    {
        try {
            $event = Event::where('public_link', $publicLink)->firstOrFail();

            if ($request->courtesy_code) {
                $coupon = Coupon::where('numeric_code', $request->courtesy_code)
                    ->where('event_id', $event->id)
                    ->where('is_consumed', false)
                    ->with('ticketType')
                    ->first();

                if (!$coupon) {
                    return $this->response("Inscripción NO exitosa. CUPÓN INVÁLIDO.", $returnJson, false);
                }
            }

            $ticketTypeSeats = Seat::where('ticket_type_id', $request->id_ticket)->exists();

            if ($ticketTypeSeats && !$request->seat_id) {
                return $this->response('Es obligatorio asignar una silla para este tipo de ticket.', $returnJson, false);
            }

            if ($request->seat_id) {
                $seat = Seat::find($request->seat_id);

                if (!$seat || $seat->is_assigned) {
                    return $this->response('Inscripción NO exitosa. SILLA ASIGNADA', $returnJson, false);
                }

                if ($seat->ticket_type_id != $request->id_ticket) {
                    return $this->response('Inscripción NO exitosa. SILLA NO COINCIDE CON EL TIPO DE TICKET', $returnJson, false);
                }
            }

            $eventAssistantController = new EventAssistantController();
            if ($eventAssistantController->eventoFinalizado($event->id)) {
                return $this->response('No se puede realizar esta acción porque el evento ya ha sido finalizado.', $returnJson, false);
            }

            // Validaciones dinámicas
            $registrationParameters = json_decode($event->registration_parameters, true) ?? [];
            $validationRules = [];

            foreach ($registrationParameters as $param) {
                switch ($param) {
                    case 'name':
                    //case 'lastname':
                        $validationRules[$param] = 'required|string|max:255';
                        break;
                    case 'email':
                        $validationRules[$param] = 'required|email|max:255';
                        break;
                    case 'type_document':
                        $validationRules[$param] = 'required|string|max:3';
                        break;
                    case 'document_number':
                        $validationRules[$param] = 'required|string|max:20';
                        break;
                    case 'phone':
                        $validationRules[$param] = 'nullable|string|max:15';
                        break;
                    case 'city_id':
                        $validationRules[$param] = 'nullable|exists:cities,id';
                        break;
                    case 'birth_date':
                        $validationRules[$param] = 'nullable|date';
                        break;
                }
            }

            $validatedData = $request->validate($validationRules);

            $user = User::where('email', $request->email)
                ->orWhere('document_number', $request->document_number)
                ->first();

            if ($user) {
                $user->update($validatedData);
            } else {
                $user = User::create(array_merge($validatedData, ['status' => false]));
            }

            if (!$user->hasRole('assistant')) {
                $assistantRole = Role::firstOrCreate(['name' => 'assistant']);
                $user->assignRole($assistantRole);
            }

            $eventAssistant = EventAssistant::where('event_id', $event->id)
                ->where('user_id', $user->id)
                ->first();

            if ($eventAssistant) {
                return $this->response('El usuario ya está inscrito en este evento.', $returnJson, false);
            }

            $ticketType = TicketType::find($request->id_ticket);
            if (!$ticketType) {
                return $this->response('El tipo de entrada seleccionado no es válido.', $returnJson, false);
            }

            if (!auth()->check() || !auth()->user()->hasRole('admin')) {
                $currentAssistantsCount = EventAssistant::where('event_id', $event->id)
                    ->where('ticket_type_id', $ticketType->id)
                    ->count();

                $currentMinorsCount = Minor::whereIn('event_assistant_id', function ($query) use ($event, $ticketType) {
                    $query->select('id')
                        ->from('event_assistants')
                        ->where('event_id', $event->id)
                        ->where('ticket_type_id', $ticketType->id);
                })->count();

                $totalOccupied = $currentAssistantsCount + $currentMinorsCount;
                $newMinorsCount = $request->has('minors') ? count($request->minors) : 0;
                $totalNew = 1 + $newMinorsCount;

                if (($totalOccupied + $totalNew) > $ticketType->capacity) {
                    return $this->response('No se puede completar la inscripción: se ha alcanzado el límite de capacidad para este tipo de entrada.', $returnJson, false);
                }
            }

            $guardianId = $request->input('guardian_id') ?? null;
            $eventAssistant = EventAssistant::create([
                'event_id' => $event->id,
                'user_id' => $user->id,
                'ticket_type_id' => $ticketType->id,
                'has_entered' => false,
                'guardian_id' => $guardianId,
                'guid' => $request->input('guid') ?? null
            ]);

            if (isset($coupon)) {
                $coupon->update([
                    'is_consumed' => true,
                    'event_assistant_id' => $eventAssistant->id,
                ]);

                $eventAssistant->update([
                    'is_paid' => true,
                    'ticket_type_id' => $coupon->ticket_type_id,
                ]);
            }

            if (isset($seat)) {
                $seat->update([
                    'is_assigned' => 1,
                    'event_assistant_id' => $eventAssistant->id,
                ]);
            }

            if ($request->has('minors')) {
                foreach ($request->minors as $minorData) {
                    Minor::create([
                        'full_name' => $minorData['full_name'],
                        'age' => $minorData['age'],
                        'event_assistant_id' => $eventAssistant->id,
                    ]);
                }
            }

            $definedParameters = AdditionalParameter::where('event_id', $event->id)->get();
            $userFillableColumns = (new User())->getFillable();
            $additionalParameters = $request->except(array_merge(['_token'], $userFillableColumns));

            foreach ($definedParameters as $definedParameter) {
                if (isset($additionalParameters[$definedParameter->name])) {
                    UserEventParameter::create([
                        'event_id' => $event->id,
                        'user_id' => $user->id,
                        'additional_parameter_id' => $definedParameter->id,
                        'value' => $additionalParameters[$definedParameter->name],
                    ]);
                }
            }

            $qrcode = $eventAssistant->qrCode;
            $idEventAssistant = $eventAssistant->id;
            $message = 'Inscripción exitosa.';
            $userName = $user->name ? $user->name . " " . $user->lastname : null;

            $data = compact('event', 'qrcode', 'message', 'userName', 'idEventAssistant');

            return $this->response($data, $returnJson, true);
        } catch (\Exception $e) {
            return $this->response($e->getMessage(), $returnJson, false);
        }
    }

    private function response($data, $json, $success)
    {
        if ($json) {
            return response()->json([
                'success' => $success,
                'data' => $success ? $data : null,
                'message' => $success ? null : $data,
            ], $success ? 200 : 422);
        }

        if ($success) {
            return view('event.public_registrated', $data);
        }

        return redirect()->back()->with('error', $data);
    }
}
