<?php

namespace App\Http\Controllers;

use App\Models\AdditionalParameter;
use App\Models\Coupon;
use App\Models\Departament;
use App\Models\Event;
use App\Models\EventAssistant;
use App\Models\Seat;
use App\Models\TicketFeatures;
use App\Models\TicketType;
use App\Models\User;
use App\Models\UserEventParameter;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

class EventController extends Controller
{

    public function index(Request $request)
    {
        $search = $request->input('search');
        // $status = config('statusEvento.'.$search);
        // return $status;
        $eventos = Event::query()
            ->when($search, function ($query, $search) {
                $status = config('statusEvento.'.$search);
                $query->where('name', 'like', "%{$search}%")
                      ->orWhere('description', 'like', "%{$search}%");
                if($status){
                    $query
                    ->orWhere('status', 'like', "%{$status}%");
                }
            })
            ->paginate(10);
        return view('event.index', compact('eventos', 'search'));
    }

    public function create (){
        $departments = Departament::all();
        $features = TicketFeatures::all();
        return view('event.create', compact('departments', 'features'));
    }
    public function store(Request $request)
    {
        // Validar los datos de entrada
        $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'required|max:255',
            'status' => 'required',
            'description' => 'required|string',
            'capacity' => 'required|integer|min:1',
            'city_id' => 'required|integer|exists:cities,id',
            'event_date' => 'required|date',
            'event_date_end' => 'required|date',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'header_image_path' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'color_one' => 'nullable|string|max:7', // HEX color format
            'color_two' => 'nullable|string|max:7', // HEX color format
            'ticketTypes.*.name' => 'required|string|max:255',
            'ticketTypes.*.capacity' => 'required|integer|min:1',
            'ticketTypes.*.price' => 'required|numeric',
            'ticketTypes.*.features' => 'required|array|exists:ticket_features,id',
            'ticketTypes.*.date_entry' => 'nullable|date',
            'ticketTypes.*.entry_start_time' => ['nullable', 'regex:/^\d{2}:\d{2}(:\d{2})?$/'],
            'ticketTypes.*.entry_end_time' => ['nullable', 'regex:/^\d{2}:\d{2}(:\d{2})?$/', 'after:ticketTypes.*.entry_start_time'],
            'additionalFields.*.label' => 'required|string|max:255',
            'additionalFields.*.value' => 'required|string|max:255',
        ]);

        // Manejar la carga de la imagen
        $imagePath = null;
        if ($request->hasFile('header_image_path')) {
            $image = $request->file('header_image_path');
            $imagePath = $image->store('event_images', 'public');
        }

        // Crear el evento
        $event = new Event();
        $event->name = $request->name;
        $event->description = $request->description;
        $event->capacity = $request->capacity;
        $event->city_id = $request->city_id;
        $event->event_date = $request->event_date;
        $event->event_date_end = $request->event_date_end;
        $event->start_time = $request->start_time;
        $event->end_time = $request->end_time;
        $event->address = $request->address;
        $event->header_image_path = $imagePath;
        $event->status = $request->status;
        $event->color_one = $request->color_one;
        $event->color_two = $request->color_two;
        // Convertir los campos adicionales a JSON
        if($request->input('additionalFields')){
            $event->additionalFields = json_encode($request->input('additionalFields', []));
        }

        // Guardar el ID del usuario que creó el evento
        $event->created_by = Auth::user()->id;
        $event->save();

        // Crear los tipos de entradas
        if($request->ticketTypes){
            foreach ($request->ticketTypes as $ticketTypeData) {
                $ticketType = $event->ticketTypes()->create([
                    'name' => $ticketTypeData['name'],
                    'capacity' => $ticketTypeData['capacity'],
                    'price' => $ticketTypeData['price'],
                    'entry_date' => $ticketData['entry_date'] ?? null,
                    'entry_start_time' => $ticketData['entry_start_time'] ?? null,
                    'entry_end_time' => $ticketData['entry_end_time'] ?? null,
                ]);

                // Asignar características
                $ticketType->features()->sync($ticketTypeData['features']);
            }
        }

        return redirect()->route('event.index')->with('success', 'Evento creado exitosamente.');
    }

    public function edit($id){
        $event = Event::with('ticketTypes.features')->findOrFail($id);
        $departments = Departament::all();
        $features = TicketFeatures::all();

        $event = Event::with('ticketTypes.features')->findOrFail($id);
        $selectedFeaturesByIndex = [];

        foreach ($event->ticketTypes as $i => $tt) {
            $selectedFeaturesByIndex[$i] = $tt->features ? $tt->features->pluck('id')->toArray() : [];
        }


        return view('event.update', compact(['event', 'departments', 'features', 'selectedFeaturesByIndex']));
    }

    public function update(Request $request){

        try {
            $id = $request->id;
            $event = Event::findOrFail($id);

            // Validar los datos de entrada
            $request->validate([
                'name' => 'required|string|max:255',
                'description' => 'required|string',
                'capacity' => 'required|integer|min:1',
                'event_date' => 'required|date',
                'event_date_end' => 'required|date',
                'header_image_path' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
                'color_one' => 'nullable|string|max:7', // HEX color format
                'color_two' => 'nullable|string|max:7', // HEX color format
                'ticketTypes.*.name' => 'required|string|max:255',
                'ticketTypes.*.capacity' => 'required|integer|min:1',
                'ticketTypes.*.price' => 'required|numeric',
                'ticketTypes.*.date_entry' => 'nullable|date',
                'ticketTypes.*.entry_start_time' => ['nullable', 'regex:/^\d{2}:\d{2}(:\d{2})?$/'],
                'ticketTypes.*.entry_end_time' => ['nullable', 'regex:/^\d{2}:\d{2}(:\d{2})?$/', 'after:ticketTypes.*.entry_start_time'],
                'address' => 'required|max:255',
                'status' => 'required',
            ]);

            // Manejar la carga de la nueva imagen si se sube una
            if ($request->hasFile('header_image_path')) {
                if ($event->header_image_path) {
                    Storage::disk('public')->delete($event->header_image_path);
                }
                $image = $request->file('header_image_path');
                $event->header_image_path = $image->store('event_images', 'public');
            }

            // Actualizar el evento
            $event->name = $request->name;
            $event->description = $request->description;
            $event->capacity = $request->capacity;
            $event->city_id = $request->city_id;
            $event->event_date = $request->event_date;
            $event->event_date_end = $request->event_date_end;
            $event->start_time = $request->start_time;
            $event->end_time = $request->end_time;
            $event->address = $request->address;
            $event->status = $request->status;
            $event->color_one = $request->color_one;
            $event->color_two = $request->color_two;

            // Convertir los campos adicionales a JSON
            if($request->input('additionalFields')){
                $event->additionalFields = json_encode($request->input('additionalFields', []));
            }
            $event->save();

            // Obtener los IDs de los ticketTypes que vienen en la solicitud
            $newTicketTypeIds = collect($request->ticketTypes)->pluck('id')->filter()->all();

            // Eliminar los ticketTypes que no están en la solicitud y no están asociados con EventAssistant
            $event->ticketTypes()->whereNotIn('id', $newTicketTypeIds)->get()->each(function ($ticketType) {
                if ($ticketType->EventAssistant()->exists()) {
                    // Si el tipo de ticket está asociado a algún EventAssistant, no lo eliminamos y podríamos optar por otra lógica aquí
                    throw new \Exception("El tipo de Boleta '{$ticketType->name}' no puede ser eliminado porque está asociado a un asistente.");
                }
                $ticketType->delete();
            });

            // Actualizar o crear nuevos ticketTypes
            if ($request->has('ticketTypes')) {
                foreach ($request->ticketTypes as $ticketData) {
                    // Verificar si ya existe (por id)
                    if (isset($ticketData['id'])) {
                        $ticket = TicketType::find($ticketData['id']);
                        if ($ticket) {
                            // Validar rango de fecha_ingreso
                            if (
                                isset($ticketData['date_entry']) &&
                                ($ticketData['date_entry'] < $event->event_date || $ticketData['date_entry'] > $event->event_date_end)
                            ) {
                                return back()->withErrors([
                                    "ticketTypes.{$ticketData['id']}.date_entry" =>
                                        "La fecha de ingreso debe estar dentro del rango del evento ({$event->event_date} a {$event->event_date_end})."
                                ])->withInput();
                            }

                        // dd($ticketData['entry_start_time']);
                            $ticket->update([
                                'name' => $ticketData['name'],
                                'capacity' => $ticketData['capacity'],
                                'price' => $ticketData['price'],
                                'entry_date' => $ticketData['entry_date'] ?? null,
                                'entry_start_time' => $ticketData['entry_start_time'] ?? null,
                                'entry_end_time' => $ticketData['entry_end_time'] ?? null,
                            ]);

                        // dd($ticket);
                            // Sincronizar características
                            if (isset($ticketData['features'])) {
                                $ticket->features()->sync($ticketData['features']);
                            }
                        }
                    } else {
                        // Crear nuevo tipo de ticket
                        $newTicket = TicketType::create([
                            'event_id' => $event->id,
                            'name' => $ticketData['name'],
                            'capacity' => $ticketData['capacity'],
                            'price' => $ticketData['price'],
                            'entry_date' => $ticketData['entry_date'] ?? null,
                            'entry_start_time' => $ticketData['entry_start_time'] ?? null,
                            'entry_end_time' => $ticketData['entry_end_time'] ?? null,
                        ]);

                        if (isset($ticketData['features'])) {
                            $newTicket->features()->sync($ticketData['features']);
                        }
                    }
                }
            }

            return redirect()->route('event.index')->with('success', 'Evento actualizado exitosamente.');
        } catch (\Exception $e) {
            // Capturar la excepción y redirigir con un mensaje de error
            return redirect()->route('event.edit', $id)->with('error', $e->getMessage());
        }
    }

    public function generatePublicLink($id)
    {
        $event = Event::findOrFail($id);

        // Generar GUID único
        $guid = (string) Str::uuid();

        // Guardar el GUID en el evento
        $event->public_link = $guid;
        $event->save();

        // Devolver el enlace completo
        return redirect()->route('event.index')->with('success', 'Enlace público generado: ' . route('event.register', $guid));
    }

    public function showPublicRegistrationForm($public_link)
    {
        // Busca el evento por el enlace público
        $event = Event::where('public_link', $public_link)->firstOrFail();
        $additionalParameters = json_decode($event->additionalParameters, true) ?? [];
        $departments = Departament::all();
        $ticketTypes  = TicketType::where('event_id', $event->id)->get();

        // Retorna la vista de registro, pasando el evento
        return view('event.public_registration', compact('event', 'departments', 'additionalParameters', 'ticketTypes'));
    }

    public function submitPublicRegistration(Request $request, $public_link)
    {
        $event = Event::where('public_link', $public_link)->firstOrFail();

        // Verificar si se proporcionó un código de cortesía
        if ($request->courtesy_code) {
            $coupon = Coupon::where('numeric_code', $request->courtesy_code)
                ->where('event_id', $event->id)
                ->where('is_consumed', false)
                ->with('ticketType') // Asegura la relación con ticketType
                ->first();
            if (!$coupon) {
                return redirect()->back()->with('error', 'Inscripción NO exitosa. CUPON INVALIDO');
            }
        }

        // Verificar si el tipo de ticket tiene asientos registrados
        $ticketTypeSeats = Seat::where('ticket_type_id', $request->id_ticket)->exists();

        // Si el tipo de ticket tiene asientos y no se envió un seat_id en el request, mostrar error
        if ($ticketTypeSeats && !$request->seat_id) {
            return redirect()->back()->with('error', 'Es obligatorio asignar una silla para este tipo de ticket.');
        }

        // Si el seat_id está presente en el request, hacer las validaciones adicionales
        if ($request->seat_id) {
            $seat = Seat::find($request->seat_id);

            // Validar si la silla ya está asignada
            if ($seat->is_assigned) {
                return redirect()->back()->with('error', 'Inscripción NO exitosa. SILLA ASIGNADA');
            }

            // Validar si la silla coincide con el tipo de ticket seleccionado
            if ($seat->ticket_type_id != $request->id_ticket) {
                return redirect()->back()->with('error', 'Inscripción NO exitosa. SILLA NO COINCIDE CON TIPO DE TICKET');
            }
        }

        // Verificar si el evento ya ha finalizado
        $eventAssistantController = new EventAssistantController();
        if ($eventAssistantController->eventoFinalizado($event->id)) {
            return redirect()->back()->with('error', 'No se puede realizar esta acción porque el evento ya ha sido finalizado.');
        }

        // Construir reglas de validación dinámicas basadas en los parámetros de registro del evento
        $registrationParameters = json_decode($event->registration_parameters, true) ?? [];
        $validationRules = [];

        foreach ($registrationParameters as $param) {
            switch ($param) {
                case 'name':
                case 'lastname':
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

        // Validar el request
        $validatedData = $request->validate($validationRules);
        $user = null;
        if ($request->has('email')) {
            // Verificar si el usuario ya existe por correo
            $user = User::where('email', $request->email)
            ->first();
        }elseif($request->has('document_number')){
            // Verificar si el usuario ya existe por número de documento
            $user = User::where('document_number', $request->document_number)
            ->first();
        }
        if ($user) {
            // Si el usuario existe, actualizar su información
            $user->update($validatedData);
        } else {
            // Si no existe, crearlo
            $user = User::create(array_merge($validatedData, ['status' => false]));
        }
        $userName = null;
        if($user->name != null){
            $userName = $user->name." ".$user->lastname;
        }
        if (!$user->hasRole('assistant')) {
            $assistantRole = Role::firstOrCreate(['name' => 'assistant']); // Crear el rol si no existe
            $user->assignRole($assistantRole);
        }

        // Verificar si el usuario ya está inscrito en el evento
        $eventAssistant = EventAssistant::where('event_id', $event->id)
            ->where('user_id', $user->id)
            ->first();

        if ($eventAssistant) {
            // Si ya está inscrito, mostrar mensaje de error
            return redirect()->back()->with('error', 'El usuario ya está inscrito en este evento.');
        } else {
            // Si no está inscrito, crear el registro en `event_assistant`
            $guardianId = $request->input('guardian_id') ?? null;
            $eventAssistant = EventAssistant::create([
                'event_id' => $event->id,
                'user_id' => $user->id,
                'ticket_type_id' => $request['id_ticket'] ?? null,
                'has_entered' => false,
                'guardian_id' => $guardianId,
            ]);

            // Si hay un código de cortesía, marcarlo como consumido y asignarlo al asistente
            if (isset($coupon)) {
                $coupon->is_consumed = true;
                $coupon->event_assistant_id = $eventAssistant->id;
                $coupon->save();
                $eventAssistant->is_paid = true;
                $eventAssistant->ticket_type_id = $coupon->ticket_type_id;
                $eventAssistant->save();
            }
            if(isset($seat)){
                $seat->is_assigned = 1;
                $seat->event_assistant_id = $eventAssistant->id;
                $seat->save();
            }
        }

        // Obtener los parámetros adicionales definidos para el evento
        $definedParameters = AdditionalParameter::where('event_id', $event->id)->get();
        $userFillableColumns = (new User())->getFillable();

        // Detectar y almacenar parámetros adicionales enviados en el registro
        $additionalParameters = $request->except(array_merge(['_token'], $userFillableColumns)); // Excluir columnas del modelo User
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

        // Generar el código QR y devolver la vista de registro exitoso
        $qrcode = $eventAssistant->qrCode;
        $idEventAssistant = $eventAssistant->id;
        $message = 'Inscripción exitosa.';
        return view('event.public_registrated', compact('event', 'qrcode', 'message', 'userName', 'idEventAssistant'));
    }

    public function setRegistrationParameters($id)
    {
        $event = Event::findOrFail($id);
        $additional_parameters = AdditionalParameter::where('event_id', $id)->get();
        return view('event.set-registration-parameters', compact('event', 'additional_parameters'));
    }

    public function storeRegistrationParameters(Request $request, $id)
    {
        $event = Event::findOrFail($id);

        // Validar la entrada de los campos seleccionados
        $request->validate([
            'fields' => 'array',
            'fields.*' => 'in:name,lastname,email,type_document,document_number,phone,status,profile_photo_path,city_id,birth_date',
        ]);

        // Almacenar los campos seleccionados como parámetros de inscripción
        $parameters = json_encode($request->fields); // Convertir a JSON

        $event->registration_parameters = $parameters;
        $event->save();

        // Manejar los parámetros adicionales
        $additionalParameters = $request->input('additional_parameters', []);

        // Obtener los nombres de los parámetros adicionales enviados desde el formulario
        $newParameterNames = array_column($additionalParameters, 'name');

        // Obtener todos los parámetros adicionales actuales en la base de datos para este evento
        $existingParameters = AdditionalParameter::where('event_id', $event->id)->get();

        // Eliminar los parámetros adicionales que ya no están presentes en los nuevos datos enviados
        foreach ($existingParameters as $existingParameter) {
            if (!in_array($existingParameter->name, $newParameterNames)) {
                $existingParameter->delete();
            }
        }

        // Agregar o actualizar los parámetros adicionales
        foreach ($additionalParameters as $param) {
            if (!empty($param['name']) && !empty($param['type'])) {
                // Verificar si ya existe un parámetro adicional con el mismo 'name' y 'event_id'
                $existingParameter = AdditionalParameter::where('event_id', $event->id)
                    ->where('name', $param['name'])
                    ->first();

                if ($existingParameter) {
                    $existingParameter->update([
                        'type' => $param['type']
                    ]);
                } else {
                    AdditionalParameter::create([
                        'event_id' => $event->id,
                        'name' => $param['name'],
                        'type' => $param['type']
                    ]);
                }
            }
        }
        return redirect()->route('event.index')->with('success', 'Parámetros de inscripción guardados correctamente.');
    }

    function findByDocument(){
        return view('event.findByDocument');
    }


    public function findByDocumentStore(Request $request)
    {
        $request->validate([
            'document_number' => 'required|string',
        ]);

        // Buscar usuario por número de cédula
        $user = User::where('document_number', $request->document_number)->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Usuario no encontrado'
            ]);
        }

        // Buscar todos los eventos en los que está registrado
        $assistances = EventAssistant::with('event')
            ->where('user_id', $user->id)
            ->get();

        if ($assistances->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No se encuentra registrado en ningún evento'
            ]);
        }

        // Obtener fecha y hora actual
        $now = Carbon::now();

        // Información del usuario
        $userData = [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'document_number' => $user->document_number,
            'phone' => $user->phone ?? null,
            'age' => $user->age ?? null,
            'address' => $user->address ?? null,
            'created_at' => optional($user->created_at)->format('Y-m-d H:i'),
        ];

        // Información de los eventos con validación de fecha/hora
        $events = $assistances->map(function ($a) use ($now) {
            $event = $a->event;

            $eventDate = $event->event_date ? Carbon::parse($event->event_date) : null;
            $startTime = $event->start_time ? Carbon::parse($event->start_time) : null;
            $endTime   = $event->end_time ? Carbon::parse($event->end_time) : null;

            $isToday = $eventDate && $eventDate->isSameDay($now);
            $isWithinTime = $isToday && $startTime && $endTime && $now->between($startTime, $endTime);

            $isActive = $isWithinTime;
            $statusMessage = $isActive
                ? '🟢 El evento está activo en este momento.'
                : ($isToday
                    ? '🕓 El evento es hoy, pero aún no está en su rango horario.'
                    : '🔴 Este evento no está activo en la fecha actual.');

            return [
                'id' => $event->id,
                'name' => $event->name,
                'description' => $event->description ?? 'Sin descripción',
                'date' => $event->event_date ?? 'Fecha no disponible',
                'place' => $event->address ?? 'Lugar no especificado',
                'start_time' => $event->start_time ?? 'No especificada',
                'end_time' => $event->end_time ?? 'No especificada',
                'created_at' => optional($event->created_at)->format('Y-m-d H:i'),
                'is_active_now' => $isActive,
                'status_message' => $statusMessage,
            ];
        });

        return response()->json([
            'success' => true,
            'user' => $userData,
            'events' => $events,
            'checked_at' => $now->format('Y-m-d H:i:s'),
        ]);
    }

}
