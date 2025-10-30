<?php

namespace App\Http\Controllers;

use App\Models\AdditionalParameter;
use App\Models\Coupon;
use App\Models\Departament;
use App\Models\Event;
use App\Models\EventAssistant;
use App\Models\Minor;
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
            'allow_minors' => 'nullable|boolean',
            'generate_qr' => 'nullable|boolean',
            'send_email'  => 'nullable|boolean',
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
        $event->allow_minors = $request->boolean('allow_minors');
        $event->generate_qr = $request->boolean('generate_qr', true);
        $event->send_email  = $request->boolean('send_email', true);
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
                'allow_minors' => 'nullable|boolean',
                'generate_qr' => 'nullable|boolean',
                'send_email'  => 'nullable|boolean',
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
            $event->allow_minors = $request->boolean('allow_minors');
            $event->generate_qr = $request->boolean('generate_qr', true);
            $event->send_email  = $request->boolean('send_email', true);

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
        //$service = new PublicRegistrationService();
        //return $service->handle($request, $public_link, false); // Vista
        $event = Event::where('public_link', $public_link)->firstOrFail();

        // Verificar si se proporcionó un código de cortesía
        if ($request->courtesy_code) {
            $coupon = Coupon::where('numeric_code', $request->courtesy_code)
                ->where('event_id', $event->id)
                ->where('is_consumed', false)
                ->with('ticketType')
                ->first();

            if (!$coupon) {
                return redirect()->back()->with('error', 'Inscripción NO exitosa. CUPÓN INVÁLIDO.');
            }
        }

        // 🔹 Verificar si el tipo de ticket tiene asientos registrados
        $ticketTypeSeats = Seat::where('ticket_type_id', $request->id_ticket)->exists();

        if ($ticketTypeSeats && !$request->seat_id) {
            return redirect()->back()->with('error', 'Es obligatorio asignar una silla para este tipo de ticket.');
        }

        // 🔹 Validaciones del asiento (si aplica)
        if ($request->seat_id) {
            $seat = Seat::find($request->seat_id);

            if ($seat->is_assigned) {
                return redirect()->back()->with('error', 'Inscripción NO exitosa. SILLA ASIGNADA');
            }

            if ($seat->ticket_type_id != $request->id_ticket) {
                return redirect()->back()->with('error', 'Inscripción NO exitosa. SILLA NO COINCIDE CON EL TIPO DE TICKET');
            }
        }

        // 🔹 Verificar si el evento ha finalizado
        $eventAssistantController = new EventAssistantController();
        if ($eventAssistantController->eventoFinalizado($event->id)) {
            return redirect()->back()->with('error', 'No se puede realizar esta acción porque el evento ya ha sido finalizado.');
        }

        // 🔹 Reglas dinámicas de validación
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

        $validatedData = $request->validate($validationRules);

        // Crear o actualizar usuario de forma segura
        $user = null;

        // Solo buscar si al menos uno de los dos campos tiene valor
        if (!empty($request->email) || !empty($request->document_number)) {
            $user = User::where(function ($query) use ($request) {
                if (!empty($request->email)) {
                    $query->where('email', $request->email);
                }
                if (!empty($request->document_number)) {
                    $query->orWhere('document_number', $request->document_number);
                }
            })->first();
        }

        // Si no se encontró o no tiene identificadores únicos válidos, crear uno nuevo
        if (!$user) {
            $user = User::create(array_merge($validatedData, ['status' => false]));
        } else {
            $user->update($validatedData);
        }

        $userName = $user->name ? $user->name . " " . $user->lastname : null;

        // Asignar rol si no lo tiene
        if (!$user->hasRole('assistant')) {
            $assistantRole = Role::firstOrCreate(['name' => 'assistant']);
            $user->assignRole($assistantRole);
        }

        // 🔹 Verificar capacidad del tipo de ticket
        $ticketType = TicketType::find($request->id_ticket);

        if (!$ticketType) {
            return redirect()->back()->with('error', 'El tipo de entrada seleccionado no es válido.');
        }

        // Verificar si ya está inscrito en este evento con el mismo tipo de ticket
        $alreadyRegisteredSameTicket = EventAssistant::where('event_id', $event->id)
            ->where('user_id', $user->id)
            ->where('ticket_type_id', $ticketType->id)
            ->exists();

        if ($alreadyRegisteredSameTicket) {
            return redirect()->back()->with('error', 'El usuario ya está inscrito en este evento con el mismo tipo de entrada.');
        }

        // Contar asistentes adultos inscritos en este tipo de ticket
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
            $totalNew = 1 + $newMinorsCount; // 1 adulto + menores

            if (($totalOccupied + $totalNew) > $ticketType->capacity) {
                return redirect()->back()->with('error', 'No se puede completar la inscripción: se ha alcanzado el límite de capacidad para este tipo de entrada.');
            }
        }

        // 🔹 Crear registro EventAssistant
        $guardianId = $request->input('guardian_id') ?? null;
        $eventAssistant = EventAssistant::create([
            'event_id' => $event->id,
            'user_id' => $user->id,
            'ticket_type_id' => $ticketType->id,
            'has_entered' => false,
            'guardian_id' => $guardianId,
        ]);

        // 🔹 Aplicar cupón de cortesía si existe
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

        // 🔹 Asignar silla (si aplica)
        if (isset($seat)) {
            $seat->update([
                'is_assigned' => 1,
                'event_assistant_id' => $eventAssistant->id,
            ]);
        }

        // 🔹 Registrar menores (si existen)
        if ($request->has('minors')) {
            foreach ($request->minors as $minorData) {
                Minor::create([
                    'full_name' => $minorData['full_name'],
                    'age' => $minorData['age'],
                    'event_assistant_id' => $eventAssistant->id,
                ]);
            }
        }

        // 🔹 Guardar parámetros adicionales definidos
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

        // 🔹 Mostrar vista de confirmación
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
        $events = Event::where('status', 2)->get(['id', 'name']);
        return view('event.findByDocument', compact('events'));
    }

    public function findByDocumentStore(Request $request)
    {
        $request->validate([
            'document_number' => 'required|string|min:4',
            'event_id' => 'required|integer|exists:events,id',
        ]);

        $document = $request->document_number;

        // Buscar usuario por coincidencia parcial de documento
        $user = User::where('document_number', 'like', $document . '%')->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Usuario no encontrado'
            ]);
        }

        // Buscar solo el registro del evento seleccionado
        $assistances = EventAssistant::with(['event', 'ticketType', 'minors'])
            ->where('user_id', $user->id)
            ->where('event_id', $request->event_id)
            ->get();

        if ($assistances->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'El usuario no está registrado en este evento.'
            ]);
        }

        $now = Carbon::now();

        $userData = [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'document_number' => $user->document_number,
            'phone' => $user->phone,
            'age' => $user->age,
            'address' => $user->address,
        ];

        $events = $assistances->map(function ($a) use ($now) {
            $ticket = $a->ticketType;
            $event = $a->event;

            // Si no existe ticket, usar directamente la información del evento
            $entryDate  = $ticket && $ticket->entry_date
                ? Carbon::parse($ticket->entry_date)
                : ($event->event_date ? Carbon::parse($event->event_date) : null);

            $entryStart = $ticket && $ticket->entry_start_time
                ? Carbon::parse($ticket->entry_start_time)
                : ($event->start_time ? Carbon::parse($event->start_time) : null);

            $entryEnd   = $ticket && $ticket->entry_end_time
                ? Carbon::parse($ticket->entry_end_time)
                : ($event->end_time ? Carbon::parse($event->end_time) : null);

            // 🟢 2️⃣ Evaluar si está en el horario permitido
            $isToday = $entryDate && $entryDate->isSameDay($now);
            $isWithinTime = $isToday && $entryStart && $entryEnd && $now->between($entryStart, $entryEnd);

            $isActive = $isWithinTime;

            // 🟠 3️⃣ Generar mensaje adecuado
            if (!$entryDate) {
                $statusMessage = '⚠️ No se ha definido una fecha de ingreso para este ticket ni para el evento.';
            } elseif ($isWithinTime) {
                $statusMessage = '🟢 El evento está activo en este momento.';
            } elseif ($isToday) {
                $statusMessage = '🕓 El evento es hoy, pero aún no está en su rango horario.';
            } else {
                $statusMessage = '🔴 Este evento no está activo en la fecha actual.';
            }

            // 👶 4️⃣ Obtener menores relacionados (si existen)
            $minors = $a->minors()->get(['full_name', 'age'])->map(function ($minor) {
                return [
                    'full_name' => $minor->full_name,
                    'age' => $minor->age,
                ];
            });

            // 5️⃣ Armar el resultado
            return [
                'id' => $event->id,
                'name' => $event->name,
                'description' => $event->description ?? 'Sin descripción',
                'date' => optional($entryDate)->format('Y-m-d') ?? 'Sin fecha',
                'start_time' => optional($entryStart)->format('H:i') ?? 'No especificada',
                'end_time' => optional($entryEnd)->format('H:i') ?? 'No especificada',
                'place' => $event->address ?? 'Lugar no especificado',
                'is_active_now' => $isActive,
                'status_message' => $statusMessage,
                'event_assistant_id' => $a->id,
                'minors' => $minors,
            ];
        });

        return response()->json([
            'success' => true,
            'user' => $userData,
            'events' => $events,
            'checked_at' => $now->format('Y-m-d H:i:s'),
        ]);
    }

    public function buscarCedulas(Request $request)
    {
        $eventId = $request->get('event_id');
        $query = $request->get('query');

        $results = User::whereHas('events', function ($q) use ($eventId) {
                $q->where('event_id', $eventId);
            })
            ->where('document_number', 'like', "%{$query}%")
            ->limit(10)
            ->get(['id', 'document_number', 'name', 'lastname']);

        return response()->json([
            'success' => true,
            'results' => $results
        ]);
    }

    public function getLocalRecords(Request $request)
    {
        $request->validate([
            'event_id' => 'required|integer|exists:events,id',
            'date' => 'required|date',
        ]);

        $event = Event::findOrFail($request->event_id);
        $requestedDate = Carbon::parse($request->date)->format('Y-m-d');

        // 1) Tickets que explícitamente tienen entry_date = requestedDate
        $ticketIdsWithDate = TicketType::where('event_id', $event->id)
            ->whereDate('entry_date', $requestedDate)
            ->pluck('id')
            ->toArray();
        // 2) Si la fecha del evento coincide con la solicitada, incluir ticket types sin entry_date
        $ticketIds = $ticketIdsWithDate;
        if ($event->event_date) {
            $eventDate = Carbon::parse($event->event_date)->format('Y-m-d');
            if ($eventDate === $requestedDate) {
                $ticketIdsWithoutDate = TicketType::where('event_id', $event->id)
                    ->whereNull('entry_date')
                    ->pluck('id')
                    ->toArray();

                // unir arrays y eliminar duplicados por si acaso
                $ticketIds = array_values(array_unique(array_merge($ticketIdsWithDate, $ticketIdsWithoutDate)));
            }
        }

        // Si no hay ticket types que coincidan, devolvemos vacío
        if (empty($ticketIds)) {
            return response()->json([
                'success' => true,
                'records' => [],
                'message' => 'No hay registros para la fecha/tipo de ticket seleccionados.'
            ]);
        }

        // Obtener asistentes cuya ticket_type_id esté en la lista y que pertenezcan al evento
        $assistants = EventAssistant::with(['user', 'ticketType', 'minors'])
            ->where('event_id', $event->id)
            ->whereIn('ticket_type_id', $ticketIds)
            ->get()
            ->map(function ($a) {
                return [
                    'id' => $a->id,
                    'event_id' => $a->event_id,
                    'document_number' => $a->user->document_number,
                    'name' => trim(($a->user->name ?? '') . ' ' . ($a->user->lastname ?? '')),
                    'email' => $a->user->email,
                    'ticket' => $a->ticketType ?? null,
                    'ticket_id' => $a->ticket_type_id,
                    'minors' => $a->minors->isNotEmpty()
                        ? $a->minors->map(fn($m) => [
                            'full_name' => $m->full_name,
                            'age' => $m->age
                        ])->values()
                        : collect(), // colección vacía si no tiene menores
                ];
            });

        return response()->json([
            'success' => true,
            'records' => $assistants,
        ]);
    }

}
