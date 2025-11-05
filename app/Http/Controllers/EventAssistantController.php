<?php

namespace App\Http\Controllers;

use App\Exports\EventAssistantsExport;
use App\Exports\TemplateExport;
use App\Imports\AssistantsImport;
use App\Models\AdditionalParameter;
use App\Models\Departament;
use App\Models\Event;
use App\Models\EventAssistant;
use App\Models\FeatureConsumption;
use App\Models\Payment;
use App\Models\TicketFeatures;
use App\Models\TicketType;
use App\Models\User;
use App\Models\UserEventParameter;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Facades\Excel;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Mail;
use App\Http\Controllers\PDFController;
use App\Models\Coupon;
use App\Models\Seat;

class EventAssistantController extends Controller
{
    public function index(Request $request, $idEvent){
        $event = Event::findOrFail($idEvent);

        // Número de asistentes registrados para el evento
        $totalTickets = EventAssistant::where('event_id', $idEvent)->where('has_entered', true)->count();
        $ticketsNoEntered = EventAssistant::where('event_id', $idEvent)->where('has_entered', false)->count();
        $tickets = TicketType::where('event_id', $idEvent)->get();

        // Capacidad total del evento
        $capacity = $event->capacity;

        // Calcula los datos para el gráfico
        $availableTickets = $capacity - $totalTickets;
        $dataGeneral = [
            'soldTickets' => $totalTickets, // Entradas vendidas
            'ticketsNoEntered' => $ticketsNoEntered, // Entradas no ingresadas
            'availableTickets' => $availableTickets, // Entradas disponibles
            'capacity' => $capacity // Capacidad total
        ];


        // Obtener todos los tipos de tickets para el evento
        $ticketTypes = TicketType::where('event_id', $idEvent)->get();

        // Inicializar el array para almacenar la información
        $ticketsInfo = [];

        foreach ($ticketTypes as $ticketType) {
            // Contar cuántos usuarios han ingresado con este tipo de ticket
            $totalEntered = EventAssistant::where('event_id', $idEvent)
                ->where('ticket_type_id', $ticketType->id)
                ->where('has_entered', true)
                ->count();

            $ticketTypeNoEntered = EventAssistant::where('event_id', $idEvent)
                ->where('ticket_type_id', $ticketType->id)
                ->where('has_entered', false)
                ->count();

            // Calcular entradas disponibles
            $availableTickets = $ticketType->capacity - $totalEntered;

            // Guardar la información en el array
            $ticketsInfo[] = [
                'ticket_type_id' => $ticketType->id,
                'name' => $ticketType->name,
                'price' => $ticketType->price,
                'capacity' => $ticketType->capacity,
                'soldTickets' => $totalEntered,
                'availableTickets' => $availableTickets,
                'ticketTypeNoEntered' => $ticketTypeNoEntered,
            ];
        }
        // Aplicar búsqueda y paginación
        $query = EventAssistant::where('event_id', $idEvent);

        if ($request->has('search')) {
            $search = $request->input('search');

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
            });
        }

        $asistentes = $query->paginate(10);

        return view('eventAssistant.index', compact(['asistentes', 'idEvent', 'dataGeneral', 'event', 'ticketsInfo', 'tickets']));
    }

    // Muestra la vista para subir el archivo de Excel
    public function showMassAssign($idEvent)
    {
        $event = Event::findOrFail($idEvent);
        return view('eventAssistant.massAssign', compact('event'));
    }

    // Procesa el archivo de Excel y asigna los asistentes de forma masiva
    public function uploadMassAssign(Request $request, $idEvent)
    {
        if($this->eventoFinalizado($idEvent)){
            return redirect()->back()
                            ->with('messages', 'No se puede realizar está acción porque el evento ya ha sido finalizado');
        }
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv|max:2048', // Validar que sea un archivo Excel
        ]);

        try {
            // Instanciar el importador y recolectar los datos de las importaciones
            $import = new AssistantsImport($idEvent);
            Excel::import($import, $request->file('file'));

            // Obtener los detalles de los usuarios agregados y las novedades
            $importedUsers = $import->getImportedUsers();
            $messages = $import->getMessages();

            return redirect()->route('eventAssistant.massAssign', $idEvent)
                            ->with('success', 'Asistentes asignados exitosamente.')
                            ->with('importedUsers', $importedUsers)
                            ->with('messages', $messages);
        } catch (\Exception $e) {
            return redirect()->route('eventAssistant.massAssign', $idEvent)
                            ->with('error', 'Hubo un error al procesar el archivo: ' . $e->getMessage());
        }
    }

    public function singleAssignForm($idEvent)
    {
        $assistants = User::whereHas('roles', function ($query) {
            $query->where('name', 'Assistant');
        })->get();
        $event = Event::findOrFail($idEvent);
        $ticketTypes  = TicketType::where('event_id', $idEvent)->get();
        return view('eventAssistant.singleAssign', compact('event', 'assistants', 'ticketTypes'));
    }

    public function uploadSingleAssign(Request $request, $eventId)
    {
        if($this->eventoFinalizado($eventId)){
            return redirect()->route('eventAssistant.index', $eventId)
            ->with('success', 'No se puede realizar está acción porque el evento ya ha sido finalizado.');
        }
        // Validar los datos recibidos
        $validatedData = $request->validate([
            'ticketTypes' => 'nullable|array',
            'ticketTypes.*' => 'nullable|exists:ticket_types,id',
        ]);

        // Obtener el evento
        $event = Event::findOrFail($eventId);
        // return $request;
        foreach ($validatedData['ticketTypes'] as $userId => $ticketId) {
            // Buscar si ya existe un registro para este usuario y evento
            $eventAssistant = EventAssistant::where('event_id', $eventId)
                ->where('user_id', $userId)
                ->first();

            if ($eventAssistant) {
                // Actualizar el registro existente con el tipo de ticket seleccionado
                $eventAssistant->ticket_type_id = $validatedData['ticketTypes'][$userId] ?? null;
                $eventAssistant->save();
            } else {
                // Crear un nuevo registro
                EventAssistant::create([
                    'event_id' => $eventId,
                    'user_id' => $userId,
                    'ticket_type_id' => $validatedData['ticketTypes'][$userId] ?? null,
                    'has_entered' => false, // Asumimos que inicialmente el usuario no ha entrado al evento
                ]);
            }
        }

        // Redirigir con un mensaje de éxito
        return redirect()->route('eventAssistant.index', $eventId)
        ->with('success', 'Asistentes asignados exitosamente.');
    }

    public function singleCreateForm($idEvent)
    {
        $event = Event::find($idEvent);
        $additionalParameters = json_decode($event->additionalParameters, true) ?? [];
        $departments = Departament::all();
        $event = Event::findOrFail($idEvent);
        $ticketTypes  = TicketType::where('event_id', $idEvent)->get();
        // Obtener asistentes mayores de 18 años con número de documento
        $guardians = EventAssistant::where('event_id', $idEvent)
            ->whereHas('user', function ($query) {
                $query->whereNotNull('document_number')
                    ->where('birth_date', '<=', now()->subYears(18))
                    ; // Filtrar mayores de 18
            })
            ->with(['user' => function ($query) {
                $query->select('id', 'name', 'document_number'); // Seleccionar solo los campos requeridos
            }])
        ->get();
        return view('eventAssistant.singleCreate', compact('event', 'departments', 'ticketTypes', 'additionalParameters', 'guardians'));
    }

    public function singleCreateUpload(Request $request, $idEvent)
    {
        if($this->eventoFinalizado($idEvent)){
            return redirect()->back()
            ->with('success', 'No se puede realizar está acción porque el evento ya ha sido finalizado.');
        }
        $event = Event::find($idEvent);

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

        $registrationParameters = json_decode($event->registration_parameters, true) ?? [];

        // Construir reglas de validación dinámicas
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
                    $validationRules[$param] = 'nullable|string|max:15'; // Suponiendo que es opcional
                    break;
                case 'city_id':
                    $validationRules[$param] = 'nullable|exists:cities,id'; // Asegúrate de que la ciudad exista
                    break;
                case 'birth_date':
                    $validationRules[$param] = 'nullable|date'; // Opcional, formato de fecha
                    break;
                // Agrega más parámetros según sea necesario
            }
        }

        // Validar el request
        $validatedData = $request->validate($validationRules);

        if ($request->has('email') || $request->has('document_number')) {
            // Verificar si el usuario ya existe por correo o número de documento
            $user = User::where('email', $request->email)
            ->orWhere('document_number', $request->document_number)
            ->first();
        }

        if (isset($user)) {
            // Actualizar el usuario existente
            $user->update($validatedData);
        } else {
            // Si no existe, crear un nuevo usuario
            $userFillableColumns = (new User())->getFillable(); // Obtener las columnas permitidas
            $createData = []; // Inicializar el array para los datos de creación

            foreach ($userFillableColumns as $column) {
                if ($request->has($column)) {
                    $createData[$column] = $request[$column];
                }
            }
            $createData['status'] = false;
            $user = User::create($createData);
        }

        // Verificar si el usuario tiene el rol de 'assistant', si no, asignarlo
        if (!$user->hasRole('assistant')) {
            $assistantRole = Role::firstOrCreate(['name' => 'assistant']); // Crear el rol si no existe
            $user->assignRole($assistantRole);
        }
        $guardianId = $request->input('guardian_id') ?? null; // Asegúrate de que tu formulario tenga este campo

        // Crear el registro en la tabla `event_assistant` si no existe
        $eventAssistant = EventAssistant::firstOrCreate(
            [
                'event_id' => $event->id,
                'user_id' => $user->id,
            ],
            [
                'ticket_type_id' => $request['id_ticket'] ?? null,
                'has_entered' => false,
                'guardian_id' => $guardianId,
            ]
        );

        if(isset($seat)){
            $seat->is_assigned = 1;
            $seat->event_assistant_id = $eventAssistant->id;
            $seat->save();
        }

        // Obtener los parámetros adicionales definidos para el evento
        $definedParameters = AdditionalParameter::where('event_id', $event->id)->get();
        // Obtener las columnas definidas en $fillable del modelo User
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

        return redirect()->route('eventAssistant.singleCreateForm', $idEvent)->with('success', 'Inscripción exitosa.');
    }

    public function edit($idEventAssistant){
        $eventAssistant = EventAssistant::find($idEventAssistant);
        $userEventParameter = UserEventParameter::where('user_id', $eventAssistant->user_id)->where('event_id', $eventAssistant->event_id)->get();
        // return $userEventParameter[0]->value;
        $event = Event::find($eventAssistant->event_id);
        $additionalParameters = json_decode($event->additionalParameters, true) ?? [];
        $departments = Departament::all();
        $event = Event::findOrFail($event->id);
        $ticketTypes  = TicketType::where('event_id', $event->id)->get();
        // Obtener asistentes mayores de 18 años con número de documento
        $guardians = EventAssistant::where('event_id', $eventAssistant->event_id)
            ->whereHas('user', function ($query) {
                $query->whereNotNull('document_number')
                    ->where('birth_date', '<=', now()->subYears(18))
                    ; // Filtrar mayores de 18
            })
            ->with(['user' => function ($query) {
                $query->select('id', 'name', 'document_number'); // Seleccionar solo los campos requeridos
            }])
        ->get();
        return view('eventAssistant.editAssistant', compact('event', 'departments', 'ticketTypes', 'additionalParameters', 'eventAssistant', 'userEventParameter', 'guardians'));
    }

    public function singleUpdateUpload(Request $request, $idEventAssistant)
    {
        $eventAssistant = EventAssistant::find($idEventAssistant);
        $idEvent = $eventAssistant->event_id;
        if($this->eventoFinalizado($idEvent)){
            return redirect()->back()
            ->with('success', 'No se puede realizar está acción porque el evento ya ha sido finalizado.');
        }
        // Encontrar el evento por su ID
        $event = Event::findOrFail($idEvent);

        // Encontrar el usuario por su ID
        $user = User::findOrFail($eventAssistant->user_id);
        // return "prueba";
        // Validar la solicitud
        // $request->validate([
        //     'name' => 'required|string|max:255',
        //     'email' => 'required|email|max:255|unique:users,email,' . $user->id,
        //     'type_document' => 'required|string|max:3',
        //     'document_number' => 'required|string|max:20|unique:users,document_number,' . $user->id,
        // ]);

        // Inicializar un arreglo para almacenar los datos a actualizar
        $updateData = [];

        // Obtener las columnas definidas en $fillable del modelo User
        $userFillableColumns = (new User())->getFillable();

        // Recorrer las columnas permitidas y verificar si están presentes en el request
        foreach ($userFillableColumns as $column) {
            if ($request->has($column)) {
                $updateData[$column] = $request[$column];
            }
        }

        // Actualizar los datos del usuario de manera dinámica
        $user->update($updateData);

        $guardianId = $request->input('guardian_id') ?? null; // Asegúrate de que tu formulario tenga este campo
        // Actualizar o crear el registro en la tabla `event_assistant`
        $eventAssistant = EventAssistant::updateOrCreate(
            [
                'event_id' => $event->id,
                'user_id' => $user->id,
            ],
            [
                'ticket_type_id' => $request['id_ticket'] ?? null,
                'guardian_id' => $guardianId,
            ]
        );

        // Obtener los parámetros adicionales definidos para el evento
        $definedParameters = AdditionalParameter::where('event_id', $event->id)->get();
        // Obtener las columnas definidas en $fillable del modelo User
        $userFillableColumns = (new User())->getFillable();
        // Detectar y almacenar parámetros adicionales enviados en el registro
        $additionalParameters = $request->except(array_merge(['_token'], $userFillableColumns)); // Excluir columnas del modelo User

        foreach ($definedParameters as $definedParameter) {
            if (isset($additionalParameters[$definedParameter->name])) {
                // Actualizar o crear el parámetro adicional del usuario
                UserEventParameter::updateOrCreate(
                    [
                        'event_id' => $event->id,
                        'user_id' => $user->id,
                        'additional_parameter_id' => $definedParameter->id,
                    ],
                    [
                        'value' => $additionalParameters[$definedParameter->name],
                    ]
                );
            }
        }


        return redirect()->route('eventAssistant.index', $idEvent)
        ->with('success', 'Actualización exitosa.');
        // return redirect()->route('eventAssistant.singleUpdateForm', [$idEventAssistant])->with('success', 'Actualización exitosa.');
    }

    public function singleDelete($idEventAssistant)
    {
        // Buscar el registro de EventAssistant por el ID proporcionado
        $eventAssistant = EventAssistant::find($idEventAssistant);
        // Verificar si el registro existe
        if (!$eventAssistant) {
            return redirect()->back()->with('error', 'El registro no fue encontrado.');
        }
        // Validar que has_entered no sea 1
        if ($eventAssistant->has_entered == 1) {
            return redirect()->back()->with('error', 'No se puede eliminar el registro porque el asistente ya ha ingresado.');
        }
        // Verificar si existen registros relacionados en feature_consumptions
        $relatedFeatureConsumptions = FeatureConsumption::where('event_assistant_id', $idEventAssistant)->exists();
        if ($relatedFeatureConsumptions) {
            return redirect()->back()->with('error', 'No se puede eliminar el registro porque tiene consumos de características asociados.');
        }
        try {
            // Intentar eliminar el registro
            $eventAssistant->delete();

            // Redirigir de vuelta con un mensaje de éxito
            return redirect()->route('eventAssistant.index')->with('success', 'El registro fue eliminado exitosamente.');
        } catch (\Exception $e) {
            // Manejo de errores, redirigir de vuelta con un mensaje de error
            return redirect()->back()->with('error', 'Ocurrió un error al intentar eliminar el registro.');
        }
    }

    public function showQr($id)
    {
        // Obtener el asistente por ID
        $asistente = EventAssistant::findOrFail($id);

        // Retornar una vista que muestre el QR
        return view('eventAssistant.qr', compact('asistente'));
    }

    public function infoQr($id, $public_link)
    {
        // Buscar el asistente por su ID
        $eventAssistant = EventAssistant::findOrFail($id);

        if($eventAssistant->guid != $public_link){
            abort(404); // Devuelve un error 404 Not Found
        }
        // Verificar si el usuario está autenticado
        if (Auth::check()) {
            // Obtener el usuario autenticado
            $user = Auth::user();

            // Verificar si el usuario tiene los roles 'admin' o 'organizer'
            if ($user->hasRole('admin') || $user->hasRole('organizer')) {
                // Redirigir a la vista con información completa
                return view('eventAssistant.qr.adminView', compact('eventAssistant'));
            } else {
                // Redirigir a la vista con información básica para usuarios autenticados que no tienen esos roles
                return view('eventAssistant.qr.basicAuthView', compact('eventAssistant'));
            }
        } else {
            // Redirigir a la vista con información básica para usuarios no autenticados
            return view('eventAssistant.qr.guestView', compact('eventAssistant'));
        }
    }

    public function registerEntry($id)
    {
        // Buscar el asistente por su ID
        $eventAssistant = EventAssistant::findOrFail($id);

        // Cambiar el estado de has_entered
        $eventAssistant->has_entered = true;
        $eventAssistant->entry_time = now();
        $eventAssistant->save();

        $event = $eventAssistant->event;
        $ticketType = $eventAssistant->ticketType; // Obtener el tipo de ticket
        $successMessage = 'Ingreso registrado correctamente.';

        // Contar cuántas entradas han ingresado con este tipo de ticket
        $currentTicketCount = EventAssistant::where('event_id', $event->id)
            ->where('ticket_type_id', $ticketType->id)
            ->where('has_entered', true)
            ->withCount('minors') // cuenta los menores por cada asistente
            ->get()
            ->sum(fn($ea) => 1 + $ea->minors_count); // 1 por el adulto + sus menores

        // Verificar el porcentaje de aforo del tipo de ticket
        $ticketCapacityReached = $currentTicketCount >= $ticketType->capacity;
        $ticketNearCapacity = $currentTicketCount >= ($ticketType->capacity * 0.9);

        if ($ticketNearCapacity && !$ticketCapacityReached) {
            // Avisar que se está cerca del aforo máximo para el tipo de ticket
            return redirect()->back()->with([
                'success' => $successMessage,
                'warning' => 'Atención: Se ha alcanzado el 90% de la capacidad para el tipo de Boleta "' . $ticketType->name . '". Se han registrado ' . $currentTicketCount . " entradas y la capacidad máxima es de " . $ticketType->capacity . "."
            ]);
        } elseif ($ticketCapacityReached) {
            // Redirigir con una alerta si se ha superado el aforo para el tipo de ticket
            return redirect()->back()->with([
                'success' => $successMessage,
                'error' => 'Aforo máximo alcanzado o superado para el tipo de Boleta "' . $ticketType->name . '". Se han registrado ' . $currentTicketCount . " entradas y la capacidad máxima es de " . $ticketType->capacity . "."
            ]);
        }else{
            return redirect()->back()->with('success', $successMessage);
        }
    }

    public function registerEntry2($id)
    {
        try {
            // Buscar el asistente
            $eventAssistant = EventAssistant::with(['event', 'ticketType', 'minors'])->findOrFail($id);

            // Marcar ingreso
            $eventAssistant->has_entered = true;
            $eventAssistant->entry_time = now();
            $eventAssistant->save();

            $event = $eventAssistant->event;
            $ticketType = $eventAssistant->ticketType;

            // Contar aforo actual (adultos + menores)
            $currentTicketCount = EventAssistant::where('event_id', $event->id)
                ->where('ticket_type_id', $ticketType->id)
                ->where('has_entered', true)
                ->withCount('minors')
                ->get()
                ->sum(fn($ea) => 1 + $ea->minors_count); // 1 por el adulto + sus menores

            $capacity = $ticketType->capacity ?? 0;
            $ticketCapacityReached = $capacity > 0 && $currentTicketCount >= $capacity;
            $ticketNearCapacity = $capacity > 0 && $currentTicketCount >= ($capacity * 0.9);

            // Construir respuesta JSON según el caso
            if ($ticketCapacityReached) {
                return response()->json([
                    'success' => true,
                    'error' => "❌ Aforo máximo alcanzado para el tipo de Boleta '{$ticketType->name}'.
                                Se han registrado {$currentTicketCount} entradas de un máximo de {$capacity}.",
                ]);
            }

            if ($ticketNearCapacity) {
                return response()->json([
                    'success' => true,
                    'warning' => "⚠️ Se ha alcanzado el 90% de la capacidad para la Boleta '{$ticketType->name}'.
                                Registradas: {$currentTicketCount}/{$capacity}.",
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => '✅ Ingreso registrado correctamente.',
                'current_count' => $currentTicketCount,
                'capacity' => $capacity
            ]);

        } catch (\Exception $e) {
            // Manejo de errores inesperados
            return response()->json([
                'success' => false,
                'message' => '⚠️ Ocurrió un error al registrar el ingreso.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function rejectEntry($id)
    {
        // Buscar el asistente por su ID
        $eventAssistant = EventAssistant::findOrFail($id);

        // Cambiar el estado de rechazo
        $eventAssistant->rejected = true;
        $eventAssistant->rejected_time = now();
        $eventAssistant->save();

        // Redirigir de nuevo a la vista con un mensaje de rechazo
        return redirect()->back()->with('success', 'Ingreso rechazado correctamente.');
    }

    public function generatePDF($id)
    {
        $asistente = EventAssistant::with('user', 'event')->findOrFail($id);
        $evento = Event::find($asistente->event_id);

        // Generar QR code como base64
        $qrCodeBase64 = 'data:image/png;base64,' . base64_encode($asistente->qrCode);

        $pdf = Pdf::loadView('pdf.assistant', compact('asistente', 'evento', 'qrCodeBase64'));

        //se muestra en una nueva ventana
        return $pdf->stream('asistente_'.$asistente->user->name.'_evento_'.$evento->name.'.pdf');
        // Se descarga,
        // return $pdf->download('Asistente_Evento_' . $asistente->user->name . '.pdf');
    }

    public function consumeFeature(EventAssistant $eventAssistant, $feature)
    {
        $eventAssistant = EventAssistant::find($eventAssistant)->first();
        // return $eventAssistant;
        $feature = TicketFeatures::find($feature);
        // return $feature->id;
        $featureConsumptions = FeatureConsumption::where('event_assistant_id', $eventAssistant->id)->where('ticket_feature_id', $feature->id)->get()->count();
        // Verificar si el feature es consumible y no ha sido consumido
        if (!$feature->consumable || $featureConsumptions > 0) {
            return redirect()->back()->with('error', 'El feature no es consumible o ya ha sido consumido.');
        }

        // Marcar el feature como consumido
        $featureConsumptions = new FeatureConsumption();
        $featureConsumptions->event_assistant_id = $eventAssistant->id;
        $featureConsumptions->ticket_feature_id = $feature->id;
        $featureConsumptions->consumed_at = now();
        $featureConsumptions->save();

        return redirect()->back()->with('success', 'El feature ha sido consumido exitosamente.');
    }

    public function downloadTemplate($id)
    {
        // Obtener los parámetros de inscripción (registration_parameters) del evento desde la base de datos
        $event = Event::findOrFail($id);

        // Decodificar los parámetros de inscripción de formato JSON a un array PHP
        $registration_parameters = json_decode($event->registration_parameters, true);

        // Si registration_parameters es nulo o vacío, usa un conjunto predeterminado
        if (empty($registration_parameters)) {
            $registration_parameters = [
                'name',
                'lastname',
                'email',
                'type_document',
                'document_number',
                'phone',
                'city_id',
                'birth_date',
            ];
        }

        // Agregar la columna adicional "ticket_type"
        $registration_parameters[] = 'ticket_type';

        // Crear una instancia de la exportación con los encabezados
        $export = new TemplateExport($registration_parameters);

        // Descargar el archivo Excel
        return Excel::download($export, 'plantilla_asistentes-'.$event->name.'.xlsx');
    }

    public function specificSearch($idEvent){
        $eventAssistant = EventAssistant::where('event_id', $idEvent)->get();
        $event = Event::find($idEvent);
        $additionalParameters = json_decode($event->additionalParameters, true) ?? [];
        $departments = Departament::all();
        $event = Event::findOrFail($idEvent);
        $ticketTypes  = TicketType::where('event_id', $idEvent)->get();
        return view('eventAssistant.specificSearch', compact('event', 'departments', 'ticketTypes', 'additionalParameters'));
    }

    public function specificSearchUploead(Request $request, $idEvent){
        // Obtener los campos de búsqueda del request y eliminar aquellos con valores nulos
        $input = array_filter($request->except('_token'), function($value) {
            return !is_null($value); // Filtra inputs que no sean null
        });

        // Obtener los IDs de usuarios que ya están registrados como asistentes del evento
        $eventAssistantUserIds = EventAssistant::where('event_id', $idEvent)->pluck('user_id');

        // Buscar en la tabla Users, pero solo aquellos que estén asociados con el evento
        $query = User::whereIn('id', $eventAssistantUserIds);
        $query->where('id', -1);
        foreach ($input as $key => $value) {
            if ($value) {
                $query->orWhere($key, 'LIKE', '%' . $value . '%');
            }
        }

        // Ejecutar la búsqueda en la tabla Users
        $users = $query->get();
        // Si no se encontraron usuarios en la tabla User, buscar en UserEventParameter y AdditionalParameter
        if ($users->isEmpty()) {
            $additionalParameterMatches = [];

            foreach ($input as $key => $value) {
                if ($value) {
                    // Encontrar los additional_parameters que coincidan con los nombres de los campos
                    $parameters = AdditionalParameter::where('event_id', $idEvent)
                        ->where('name', $key)
                        ->get();

                    foreach ($parameters as $parameter) {
                        // Buscar en UserEventParameter por el valor correspondiente y usuarios relacionados con el evento
                        $matches = UserEventParameter::where('event_id', $idEvent)
                            ->whereIn('user_id', $eventAssistantUserIds)
                            ->where('additional_parameter_id', $parameter->id)
                            ->where('value', 'LIKE', '%' . $value . '%')
                            ->pluck('user_id');

                        // Agregar los matches al arreglo de resultados
                        $additionalParameterMatches = array_merge($additionalParameterMatches, $matches->toArray());
                    }
                }
            }

            // Obtener los usuarios que coinciden en UserEventParameter y están relacionados con el evento
            $users = User::whereIn('id', $additionalParameterMatches)->get();
        }

        // Retornar la vista con los resultados
        $eventAssistant = EventAssistant::where('event_id', $idEvent)->get();
        $event = Event::find($idEvent);
        $additionalParameters = json_decode($event->additionalParameters, true) ?? [];
        $departments = Departament::all();
        $event = Event::findOrFail($idEvent);
        $ticketTypes  = TicketType::where('event_id', $idEvent)->get();
        // return view('eventAssistant.specificSearch', compact('users', 'event'));
        return view('eventAssistant.specificSearch', compact('event', 'departments', 'ticketTypes', 'additionalParameters', 'users'));
    }

    public function exportExcel(Request $request, $idEvent)
    {
        $event = Event::find($idEvent);
        // Obtener la búsqueda, campos seleccionados y parámetros adicionales desde el request
        $search = $request->input('search');
        $selectedFields = json_decode($event->registration_parameters, true) ?? [];
        $additionalParameters = $event->additionalParameters;
        // Exportar el archivo Excel usando los datos proporcionados
        return Excel::download(
            new EventAssistantsExport($idEvent, $selectedFields, $additionalParameters, $search),
            'asistentes_de_'.$event->name.'_'.date('d-m-Y').'.xlsx'
        );
    }

    public function sendMsg($idEvent){
        $eventAssistants = EventAssistant::where('event_id', $idEvent)->get();
        // Obtener todos los asistentes del evento
        $eventAssistants = EventAssistant::where('event_id', $idEvent)->get();

        foreach ($eventAssistants as $eventAssistant) {
            // Generar la URL con la ruta nombrada 'eventAssistant.infoQr'
            $url = route('eventAssistant.infoQr', ['id' => $eventAssistant->id, 'guid' => $eventAssistant->guid]);

            // Construir el mensaje de texto
            $message = "La URL para que puedas acceder al evento es la siguiente: $url";

            // Aquí puedes utilizar un servicio de SMS para enviar el mensaje
            // Ejemplo de uso de un servicio de SMS (Twilio, Nexmo, etc.)
            // SmsService::send($assistant->user->phone, $message);

            // Mostrar en consola o logs para depuración
            info("Mensaje enviado a {$eventAssistant->user->phone}: $message");
        }
    }

    public function sendEmail($id){
        // Buscar el registro de EventAssistant por ID
        $eventAssistant = EventAssistant::find($id);

        // Asegúrate de que el asistente existe
        if (!$eventAssistant || !$eventAssistant->user) {
            return response()->json(['message' => 'No se ha encontrado el usuario respectivo'], 404);
        }
//dd($eventAssistant->qrCode);

        $svg = $eventAssistant->qrCode;
        // Limpiar el encabezado XML opcionalmente (para evitar conflictos)
        $svgCleaned = preg_replace('/<\?xml.*?\?>/', '', $svg);
        // Codificar en base64
        $qrCodeBase64 = base64_encode($svgCleaned);


        // Obtener el correo electrónico del usuario relacionado
        $email = $eventAssistant->user->email;
        $pdfControlle = new PDFController();
        // Generar el PDF llamando a getPDFEvento
        $pdfContent = $pdfControlle->getPDFEventoBinary($id);

        // Enviar el correo con el PDF adjunto
        Mail::send('emails.assistant', ['eventAssistant' => $eventAssistant, 'qrCodeBase64'=>$qrCodeBase64], function ($message) use ($email, $pdfContent, $id) {
            $message->to($email)
                    ->subject('Información del evento')
                    ->attachData($pdfContent, "ticket{$id}.pdf", [
                        'mime' => 'application/pdf',
                    ]);
        });

        return response()->json(['message' => 'Email enviado a ' . $email]);
    }

    public function eventoFinalizado ($idEvent){
        return Event::find($idEvent)->status == 4 ? true : false;
    }

    public function payment($id){
        $eventAssistant = EventAssistant::find($id);
        return view('eventAssistant.payment', compact('eventAssistant'));
    }

    public function paymentStore(Request $request){

        $request->validate([
            'payer_name' => 'required|string|max:255',
            'payer_document_type' => 'required|string|max:10',
            'payer_document_number' => 'required|string|max:50',
            'amount' => 'required|numeric',
            'payment_method' => 'nullable|string',
            'payment_proof' => 'nullable|image|max:2048', // Validación de la imagen
            'courtesy_code' => 'nullable|string|max:50', // Código de cortesía opcional
        ]);
        // Si tiene código de cortesía, verificarlo
        if ($request->has('courtesy_code')) {
            $eventAssistant = EventAssistant::find($request->event_assistant_id);
            $courtesyCode = Coupon::where('numeric_code', $request->courtesy_code)
                                        ->where('is_consumed', false)
                                        ->where('event_id', $eventAssistant->event_id)
                                        ->first();

            if (!$courtesyCode) {
                return redirect()->back()->withErrors(['error' => 'El código de cortesía no es válido o ya ha sido utilizado.']);
            }

            // Si el código es válido, marcarlo como usado y registrar el asistente como pagado
            $courtesyCode->is_consumed = true;
            $courtesyCode->eventAssistant_id = $eventAssistant->id;
            $courtesyCode->save();

            $eventAssistant->is_paid = true;
            $eventAssistant->save();

            // Generar el PDF y enviar correo si es necesario
            $PDFController = new PDFController();
            $meta = $PDFController->buildPDF_Mail($request->event_assistant_id);

            return view('email.return_email_ticketevent', compact('meta'));
        }

        $paymentProofPath = null;
        if ($request->hasFile('payment_proof')) {
            $paymentProofPath = $request->file('payment_proof')->store('payment_proofs', 'public');
        }

        if ($request->hasFile('payment_proof')) {
            $paymentProofPath = $request->file('payment_proof')->store('payment_proofs', 'public');
        }


        if($request->payment_method=='PayPal'){

            $eventAssistant = EventAssistant::find($request->event_assistant_id);
            $event=Event::find($eventAssistant->event_id);
            $pago['event_assistant_id']=$request->event_assistant_id;
            $pago['payer_name']=$request->payer_name;
            $pago['payer_document_number']=$request->payer_document_number;
            $pago['amount']=round($request->amount*0.000234);
            $pago['evento']=$event->name;
            return view('paypal.checkout', compact('pago'));

        }

        Payment::create([
            'event_assistant_id' => $request->event_assistant_id,
            'payer_name' => $request->payer_name,
            'payer_document_type' => $request->payer_document_type,
            'payer_document_number' => $request->payer_document_number,
            'amount' => $request->amount,
            'payment_method' => $request->payment_method,
            'payment_proof' => $paymentProofPath,
            'description' => 'Pago de Ticket',
        ]);



        $eventAsistant = EventAssistant::find($request->event_assistant_id);
        if($eventAsistant->isFullyPaid()){
            $eventAsistant->is_paid = true;
            $eventAsistant->save();
        }

        #return redirect()->back()
        #->with('success', 'Se ha registrado el pago correctamente');

        $PDFController=new PDFController();
        $meta=$PDFController->buildPDF_Mail($request->event_assistant_id);
        return view('email.return_email_ticketevent',compact('meta'));

        }

    public function sendEmailInfoPago($id){
        // Buscar el registro de EventAssistant por ID
        $eventAssistant = EventAssistant::find($id);

        // Asegúrate de que el asistente existe
        if (!$eventAssistant || !$eventAssistant->user) {
            return response()->json(['message' => 'No se ha encontrado el usuario respectivo'], 404);
        }

        // Obtener el correo electrónico del usuario relacionado
        $email = $eventAssistant->user->email;
        // Asegúrate de que el email existe
        if (!$email) {
            return response()->json(['message' => 'No se ha encontrado el email respectivo'], 404);
        }

        // Enviar el correo
        Mail::send('emails.infoPagoAssistant',
        ['eventAssistant' => $eventAssistant]
        , function($message) use ($email) {
            $message->to($email)
                    ->subject('Información del evento');
        });

        return response()->json(['message' => 'Email enviado a ' . $email]);
    }



    public function showMassPayload($idEvent){
        $event = Event::find($idEvent);
        return view('eventAssistant.massPayload', compact('event'));
    }

    public function courtesyCode($idEvent){
        $event = Event::find($idEvent);
        return view('eventAssistant.courtesyCode', compact('event'));
    }

}
