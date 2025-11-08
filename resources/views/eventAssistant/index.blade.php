@extends('../themes/' . $activeTheme . '/' . $activeLayout)

@section('subhead')
    <title>Asistentes del Evento</title>
@endsection

@section('subcontent')
    @if(session('error'))
        <x-base.alert class="mb-2 flex items-center" variant="danger">
            <x-base.lucide class="mr-2 h-6 w-6" icon="AlertCircle" />
            {{ session('error') }}
        </x-base.alert>
    @endif
    @if(!isset($startDate) || !isset($endDate))

    <div class="mt-10 p-5 box text-center">
        <h3 class="text-lg font-medium mb-3">Seleccionar rango de fechas para consultar asistentes</h3>

        <form method="GET" action="{{ route('eventAssistant.index', ['idEvent' => $idEvent]) }}"
            class="flex flex-col sm:flex-row gap-3 justify-center items-center">

            <div>
                <label for="startDate" class="block text-sm font-medium text-slate-700 mb-1">
                    Fecha de inicio
                </label>
                <input
                    type="date"
                    id="startDate"
                    name="startDate"
                    class="form-control w-48"
                    required
                    min="{{ $event->event_date }}"
                    max="{{ $event->event_date_end}}"
                    value="{{ request('startDate') }}"
                >
            </div>

            <div>
                <label for="endDate" class="block text-sm font-medium text-slate-700 mb-1">
                    Fecha límite
                </label>
                <input
                    type="date"
                    id="endDate"
                    name="endDate"
                    class="form-control w-48"
                    required
                    min="{{ $event->event_date}}"
                    max="{{ $event->event_date_end}}"
                    value="{{ request('endDate') }}"
                >
            </div>

            <button type="submit" class="btn btn-primary mt-3 sm:mt-6">
                Filtrar
            </button>
        </form>
    </div>

    @else
    <h2 class="intro-y mt-10 text-lg font-medium">
        Lista de Asistentes
        @if ($startDate === $endDate)
            del <strong>{{ $startDate }}</strong>
        @else
            entre <strong>{{ $startDate }}</strong> y <strong>{{ $endDate }}</strong>
        @endif
    </h2>
    <div class="">
        <script>
            const chartData = @json($dataGeneral);
            const ticketData = @json($ticketsInfo); // Pasar información de tickets
        </script>
            <!-- BEGIN: Multiple Item -->
            <x-base.preview-component class="intro-y box mt-5">
                <div
                    class="flex flex-col items-center border-b border-slate-200/60 p-5 dark:border-darkmode-400 sm:flex-row">
                    <h2 class="mr-auto text-base font-medium">
                        Informe Grafico de entradas
                    </h2>
                </div>
                <div class="p-5">
                    <x-base.preview>
                        <div class="mx-6">
                            <x-base.tiny-slider
                                id="multiple-item-slider"
                                config="multiple-items"
                            >
                            <div class="px-2">
                                <div class="h-full rounded-md bg-slate-100 dark:bg-darkmode-400">
                                    <div>ASISTENCIA GENERAL</div>
                                    <div class="intro-y box mt-5 p-5 col-span-12 items-center">
                                        <div class="mt-3">
                                            <x-chart-assistants />
                                        </div>

                                        {{-- ✅ Leyenda interactiva con checkboxes --}}
                                        <div class="mx-auto mt-8 w-52 sm:w-auto legend-controls" data-chart-id="report-pie-chart">
                                            <label class="flex items-center mt-2">
                                                <input type="checkbox" class="chart-toggle mr-2" data-index="0" checked>
                                                <span class="flex items-center">
                                                    <span class="mr-2 h-2 w-2 rounded-full bg-primary"></span>
                                                    Entradas registradas
                                                </span>
                                                <span class="ml-auto font-medium">{{ $dataGeneral['soldTickets'] }} ({{ round(($dataGeneral['soldTickets'] / $dataGeneral['capacity']) * 100, 2) }}%)</span>
                                            </label>

                                            <label class="flex items-center mt-2">
                                                <input type="checkbox" class="chart-toggle mr-2" data-index="1" checked>
                                                <span class="flex items-center">
                                                    <span class="mr-2 h-2 w-2 rounded-full bg-warning"></span>
                                                    Entradas No ingresadas
                                                </span>
                                                <span class="ml-auto font-medium">{{ $dataGeneral['ticketsNoEntered'] }} ({{ round(($dataGeneral['ticketsNoEntered'] / $dataGeneral['capacity']) * 100, 2) }}%)</span>
                                            </label>

                                            <label class="flex items-center mt-2">
                                                <input type="checkbox" class="chart-toggle mr-2" data-index="2" checked>
                                                <span class="flex items-center">
                                                    <span class="mr-2 h-2 w-2 rounded-full bg-pending"></span>
                                                    Entradas Disponibles
                                                </span>
                                                <span class="ml-auto font-medium">{{ $dataGeneral['availableTickets'] }} ({{ round(($dataGeneral['availableTickets'] / $dataGeneral['capacity']) * 100, 2) }}%)</span>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>

                                @foreach ($ticketsInfo as $ticketInfo)
                                <div class="px-2">
                                    <div class="h-full rounded-md bg-slate-100 dark:bg-darkmode-400">
                                        <div>{{$ticketInfo['name']}}</div>
                                        <div class="intro-y box mt-5 p-5 col-span-12 items-center">
                                            <div class="mt-3">
                                                {{-- <x-chart-assistants height="h-[213px]" /> --}}
                                                <div class="chart-container">
                                                    <x-base.chart

                                                        id="report-pie-chart-{{ $ticketInfo['ticket_type_id'] }}"
                                                        class="chart"
                                                    ></x-base.chart>
                                                </div>
                                            </div>
                                            <div class="flex flex-col space-y-2 mt-4 text-left text-sm legend-controls" data-chart-id="report-pie-chart-{{ $ticketInfo['ticket_type_id'] }}">
                                                <label class="flex items-center space-x-2">
                                                    <input type="checkbox" class="chart-toggle" data-index="0" checked>
                                                    <span><span class="inline-block w-3 h-3 rounded-full bg-primary mr-2"></span> Entradas registradas</span>
                                                    <span class="ml-auto font-medium">{{ $ticketInfo['soldTickets'] }} ({{ round(($ticketInfo['soldTickets'] / $ticketInfo['capacity']) * 100, 2) }}%)</span>
                                                </label>
                                                <label class="flex items-center space-x-2">
                                                    <input type="checkbox" class="chart-toggle" data-index="1" checked>
                                                    <span><span class="inline-block w-3 h-3 rounded-full bg-warning mr-2"></span> Entradas NO ingresadas</span>
                                                    <span class="ml-auto font-medium">{{ $ticketInfo['ticketTypeNoEntered'] }} ({{ round(($ticketInfo['ticketTypeNoEntered'] / $ticketInfo['capacity']) * 100, 2) }}%)</span>
                                                </label>
                                                <label class="flex items-center space-x-2">
                                                    <input type="checkbox" class="chart-toggle" data-index="2" checked>
                                                    <span><span class="inline-block w-3 h-3 rounded-full bg-pending mr-2"></span> Entradas Disponibles</span>
                                                    <span class="ml-auto font-medium">{{ $ticketInfo['availableTickets'] }} ({{ round(($ticketInfo['availableTickets'] / $ticketInfo['capacity']) * 100, 2) }}%)</span>
                                                </label>
                                            </div>

                                        </div>
                                    </div>
                                </div>
                                @endforeach
                            </x-base.tiny-slider>
                        </div>
                    </x-base.preview>
                </div>
            </x-base.preview-component>

            <!-- FILTRO POR TIPO DE TICKET -->
            <div class="mt-5 p-5 box">
                <h3 class="text-lg font-medium mb-3">Filtrar por tipo de ticket</h3>

                <form method="GET" action="{{ route('eventAssistant.index', ['idEvent' => $idEvent]) }}" class="flex flex-col sm:flex-row gap-3 items-center justify-center">

                    {{-- Mantener fechas si ya están definidas --}}
                    @if (!empty($startDate) && !empty($endDate))
                        <input type="hidden" name="startDate" value="{{ $startDate }}">
                        <input type="hidden" name="endDate" value="{{ $endDate }}">
                    @endif

                    {{-- MULTISELECT USANDO x-base.tom-select --}}
                    <x-base.tom-select
                        name="ticket_type_ids[]"
                        class="w-full sm:w-1/3"
                        multiple
                        placeholder="Selecciona uno o varios tipos de ticket"
                    >
                        @foreach ($tickets as $ticket)
                            <option
                                value="{{ $ticket->id }}"
                                {{ in_array($ticket->id, $selectedTicketIds ?? []) ? 'selected' : '' }}
                            >
                                {{ $ticket->name }}
                            </option>
                        @endforeach
                    </x-base.tom-select>

                    <button type="submit" class="btn btn-primary mt-2 sm:mt-0">
                        Filtrar
                    </button>
                </form>
            </div>
        <div class="intro-y col-span-12 mt-2 flex flex-wrap items-center sm:flex-nowrap">
            {{-- <a href="{{ route('eventAssistant.massAssign', ['idEvent' => $idEvent]) }}">
                <x-base.button class="mr-2 shadow-md" variant="primary">
                    Asignar Asistentes Masivamente
                </x-base.button>
            </a>
            <a href="{{ route('eventAssistant.singleAssignForm', ['idEvent' => $idEvent]) }}">
                <x-base.button class="mr-2 shadow-md" variant="secondary">
                    Asignar Asistente Manualmente
                </x-base.button>
            </a>
            <a href="{{ route('eventAssistant.singleCreateForm', ['idEvent' => $idEvent]) }}">
                <x-base.button class="mr-2 shadow-md" variant="secondary">
                    Crear Asistente Manualmente
                </x-base.button>
            </a> --}}
            <x-base.menu>
                <x-base.menu.button class="!box" as="x-base.button">
                    <span class="flex items-center justify-center">
                        <x-base.lucide class="h-4 w-4" icon="Plus" />Cargue Asistente
                    </span>
                </x-base.menu.button>
                <x-base.menu.items class="w-40">
                    <x-base.menu.item>
                        <a href="{{ route('eventAssistant.singleCreateForm', ['idEvent' => $idEvent]) }}">
                            <x-base.button class="mr-2 shadow-md" variant="secondary">
                                Crear Manualmente
                            </x-base.button>
                        </a>
                    </x-base.menu.item>
                    <x-base.menu.item>
                        <a href="{{ route('eventAssistant.massAssign', ['idEvent' => $idEvent]) }}">
                            <x-base.button class="mr-2 shadow-md" variant="primary">
                                Crear Masivamente
                            </x-base.button>
                        </a>
                    </x-base.menu.item>
                    {{-- <x-base.menu.item>
                        <a href="{{ route('eventAssistant.singleAssignForm', ['idEvent' => $idEvent]) }}">
                            <x-base.button class="mr-2 shadow-md" variant="secondary">
                                Asignar Manualmente
                            </x-base.button>
                        </a>
                    </x-base.menu.item> --}}
                </x-base.menu.items>
            </x-base.menu>
            <a href="{{ route('eventAssistant.massPayload', ['idEvent' => $idEvent]) }}">
                <x-base.button class="mr-2 shadow-md" variant="primary">
                    Asignar Pagos Masivamente
                </x-base.button>
            </a>
            <x-base.button
                class="mb-2 mr-1"
                data-tw-toggle="modal"
                data-tw-target="#superlarge-slide-over-size-preview"
                href="#"
                as="a"
                variant="primary"
            >
                Codigos de cortesia
            </x-base.button>
            <div class="mt-3 w-full sm:ml-auto sm:mt-0 sm:w-auto md:ml-0">
                <form method="GET" action="{{ route('eventAssistant.index', ['idEvent' => $idEvent]) }}">
                    <!-- Mantener rango de fechas si existen -->
                    @if (!empty($startDate) && !empty($endDate))
                        <input type="hidden" name="startDate" value="{{ $startDate }}">
                        <input type="hidden" name="endDate" value="{{ $endDate }}">
                    @endif

                    <div class="relative w-56 text-slate-500">
                        <input
                            type="text"
                            name="search"
                            class="!box w-56 pr-10"
                            value="{{ request()->input('search') }}"
                            placeholder="Buscar..."
                        />
                        <button type="submit" class="absolute inset-y-0 right-0 my-auto mr-3 h-4 w-4">
                            <x-base.lucide icon="Search" />
                        </button>
                    </div>
                </form>
            </div>


            <a class="ml-3" href="{{ route('eventAssistant.specificSearch', ['idEvent' => $idEvent]) }}">
                <x-base.button class="mr-2 shadow-md" variant="primary">
                    Busqueda especifica
                </x-base.button>
            </a>

            <x-base.menu>
                <x-base.menu.button class="!box" as="x-base.button">
                    <span class="flex items-center justify-center">
                        <x-base.lucide class="h-4 w-4" icon="Plus" />Reporte
                    </span>
                </x-base.menu.button>
                <x-base.menu.items class="w-40">
                    <x-base.menu.item>
                        <a class="mr-3" target="_blank" href="{{ route('eventAssistant.exportExcel', [
                            'idEvent' => $idEvent,
                            'search' => request()->input('search'),
                            'additionalParameters' => request()->input('additionalParameters', [])
                            ]) }}">
                            <x-base.lucide class="mr-2 h-4 w-4" icon="FileText" /> Exportar a Excel
                        </a>
                    </x-base.menu.item>
                </x-base.menu.items>
            </x-base.menu>
            <x-base.menu>
                <x-base.menu.button class="!box" as="x-base.button">
                    <span class="flex items-center justify-center">
                        <x-base.lucide class="h-4 w-4" icon="Plus" />Reporte Pagos
                    </span>
                </x-base.menu.button>
                <x-base.menu.items class="w-40">
                    <x-base.menu.item>
                        <a class="mr-3" target="_blank" href="{{ route('payment.exportExcel', [
                            'idEvent' => $idEvent,
                            'search' => request()->input('search'),
                            'additionalParameters' => request()->input('additionalParameters', [])
                            ]) }}">
                            <x-base.lucide class="mr-2 h-4 w-4" icon="FileText" /> Pagos Realizados (EXCEL)
                        </a>
                        <a class="mr-3" target="_blank" href="{{ route('paymentStatus.exportExcel', [
                            'idEvent' => $idEvent,
                            'search' => request()->input('search'),
                            'additionalParameters' => request()->input('additionalParameters', [])
                            ]) }}">
                            <x-base.lucide class="mr-2 h-4 w-4" icon="FileText" /> Status Pago (EXCEL)
                        </a>
                    </x-base.menu.item>
                </x-base.menu.items>
            </x-base.menu>
        </div>

        <!-- BEGIN: Data List -->
        @php
            // Obtener los parámetros guardados en registration_parameters
            $selectedFields = json_decode($event->registration_parameters, true) ?? [];
            $additionalParameters = json_decode($event->additionalParameters, true) ?? [];
        @endphp
        <div class="intro-y col-span-12 overflow-auto lg:overflow-visible">
            <div class="overflow-x-auto">
                <x-base.table class="-mt-2 border-separate border-spacing-y-[10px]">
                    <x-base.table.thead>
                        <x-base.table.tr>
                            <!-- Carga dinámica de columnas -->
                            @foreach($selectedFields as $field)
                                <x-base.table.th class="whitespace-nowrap border-b-0 text-center">
                                    {{ config("traductorColumnasUsers.$field", ucfirst(str_replace('_', ' ', $field))) }}
                                </x-base.table.th>
                            @endforeach
                            @foreach($additionalParameters as $parameter)
                                <x-base.table.th class="whitespace-nowrap border-b-0 text-center">{{ ucfirst(str_replace('_', ' ', $parameter['name'])) }}</x-base.table.th>
                            @endforeach
                            <x-base.table.th class="whitespace-nowrap border-b-0 text-center">Tipo de ticket</x-base.table.th>
                            <x-base.table.th class="whitespace-nowrap border-b-0 text-center">Entrada</x-base.table.th>
                            <x-base.table.th class="whitespace-nowrap border-b-0 text-center">Ticket</x-base.table.th>
                            <x-base.table.th class="whitespace-nowrap border-b-0 text-center">Acciones</x-base.table.th>
                        </x-base.table.tr>
                    </x-base.table.thead>
                    <x-base.table.tbody>
                        @foreach ($asistentes as $asistente)
                            <x-base.table.tr class="intro-x">
                                <!-- Carga dinámica de contenido de las filas -->
                                @foreach($selectedFields as $key => $field)
                                    @if($key == 0)
                                        <x-base.table.td class="box text-center">

                                            {{-- Si tiene acudiente, mostrar alerta con tooltip --}}
                                            @if($asistente->guardian_id)
                                                <x-base.tippy
                                                    content="Acudiente: {{ $asistente->guardian->name }} - {{ $asistente->guardian->document_number }}"
                                                    class="mr-1"
                                                >
                                                    <x-base.alert class="flex items-center mb-2" variant="soft-primary">
                                                        <x-base.lucide class="mr-2" icon="info" />
                                                        {{ $asistente->user->$field }}
                                                    </x-base.alert>
                                                </x-base.tippy>
                                            @else
                                                {{ $asistente->user->$field }}
                                            @endif

                                            {{-- Si tiene menores asociados, listarlos debajo --}}
                                            @if($asistente->minors && $asistente->minors->count() > 0)
                                                <div class="mt-2 text-xs text-slate-600 text-left">
                                                    <strong>Menores a cargo:</strong>
                                                    <ul class="list-disc list-inside mt-1">
                                                        @foreach($asistente->minors as $minor)
                                                            <li>{{ $minor->full_name }} ({{ $minor->age }} años)</li>
                                                        @endforeach
                                                    </ul>
                                                </div>
                                            @endif

                                        </x-base.table.td>
                                    @else
                                        <x-base.table.td class="box text-center">{{ $asistente->user->$field }}</x-base.table.td>
                                    @endif

                                @endforeach

                                @foreach ($additionalParameters as $parameter)
                                    @php
                                        $userParameter = $asistente->eventParameters->where('event_id', $idEvent)->where('additional_parameter_id', $parameter['id'])->first();
                                    @endphp
                                    <x-base.table.td class="box text-center">{{ $userParameter ? $userParameter->value : '-' }}</x-base.table.td>
                                @endforeach

                                <!-- Columna de acciones -->
                                <x-base.table.td class="box text-center">{{ $asistente->ticketType?->name ?? "SIN REGISTRO"  }}</x-base.table.td>
                                <x-base.table.td class="box text-center">
                                    @if ($asistente->has_entered)
                                        <div role="alert" class="alert rounded-md bg-success text-slate-900 dark:border-success p-1">
                                            Entrada
                                        </div>
                                    @else
                                        <div role="alert" class="alert rounded-md bg-warning text-slate-900 dark:border-warning p-1">
                                            No Entrada
                                        </div>
                                    @endif
                                </x-base.table.td>
                                <x-base.table.td class="box text-center">
                                    @if ($asistente->is_paid)
                                        <div role="alert" class="alert rounded-md bg-success text-slate-900 dark:border-success">
                                            Pagado
                                        </div>
                                    @else
                                        @if ($asistente->totalPayments() == 0)
                                        <div role="alert" class="alert relative border rounded-md bg-danger border-danger text-white dark:border-danger mb-2 p-1">
                                            No Pagado

                                        </div>
                                        @else
                                        <div role="alert" class="alert rounded-md bg-warning text-slate-900 dark:border-warning p-1">
                                            Pendiente
                                        </div>
                                        @endif
                                    @endif
                                </x-base.table.td>
                                <x-base.table.td class="box w-56">
                                    <div class="flex items-center justify-center">
                                        <x-base.tippy content="Mostrar Codigo QR" class="mr-1">
                                        <a class="text-info" href="{{ route('eventAssistant.qr', ['id' => $asistente->id]) }}">
                                            <x-base.lucide icon="QrCode" />
                                        </a>
                                        </x-base.tippy>

                                        <x-base.tippy content="Generar PDF Informativo" class="mr-1">
                                            <a class="text-info" href="{{ route('eventAssistant.pdf', ['id' => $asistente->id]) }}" target="_blank">
                                                <x-base.lucide icon="FileText" />
                                            </a>
                                        </x-base.tippy>
                                        <x-base.tippy content="Generar Boleta PDF" class="mr-1">
                                            <a class="text-info" href="{{ route('eventAssistant.getPDFTicket', ['id' => $asistente->id]) }}" target="_blank">
                                                <x-base.lucide icon="FileText" />
                                            </a>
                                        </x-base.tippy>
                                        <x-base.tippy content="Enviar Correo" class="mr-1">
                                        <a class="text-info" href="{{ route('eventAssistant.sendEmail', ['id' => $asistente->id]) }}" target="_blank">
                                            <x-base.lucide icon="send" />
                                        </a>
                                        </x-base.tippy>
                                        <x-base.tippy content="Gestion de Pagos" class="mr-1">
                                            <a class="text-warning" href="{{ route('eventAssistant.payment', ['id' => $asistente->id]) }}" target="_blank">
                                                <x-base.lucide icon="credit-card" />
                                            </a>
                                        </x-base.tippy>
                                        <x-base.tippy content="Editar" class="mr-1">
                                            <a class="" href="{{ route('eventAssistant.singleUpdateForm', ['idEventAssistant' => $asistente->id]) }}">
                                                <x-base.lucide icon="CheckSquare" />
                                            </a>
                                        </x-base.tippy>
                                        <x-base.tippy content="Borrar" class="mr-1">
                                            <a class="text-danger"
                                            data-tw-toggle="modal"
                                            data-tw-target="#delete-confirmation-modal"
                                            data-id="{{ $asistente->id }}"
                                            onclick="setDeleteAction(this)">
                                            <x-base.lucide icon="Trash" />
                                            </a>
                                        </x-base.tippy>
                                    </div>
                                </x-base.table.td>
                            </x-base.table.tr>
                        @endforeach
                    </x-base.table.tbody>
                </x-base.table>
            </div>
        </div>
        <!-- END: Data List -->

    <!-- BEGIN: Delete Confirmation Modal -->
    <x-base.dialog id="delete-confirmation-modal">
        <x-base.dialog.panel>
            <div class="p-5 text-center">
                <x-base.lucide
                    class="mx-auto mt-3 h-16 w-16 text-danger"
                    icon="XCircle"
                />
                <div class="mt-5 text-3xl">¿Está seguro?</div>
                <div class="mt-2 text-slate-500">
                    ¿Realmente desea eliminar estos registros? <br />
                    Este proceso no se puede deshacer.
                </div>
            </div>
            <div class="px-5 pb-8 text-center">
                <x-base.button
                    class="mr-1 w-24"
                    data-tw-dismiss="modal"
                    type="button"
                    variant="outline-secondary"
                >
                    Cancel
                </x-base.button>

                <!-- Formulario de eliminación -->
                <form id="delete-form" method="POST" action="">
                    @csrf
                    @method('DELETE')
                    <x-base.button
                        class="w-24"
                        type="submit"
                        variant="danger"
                    >
                        Delete
                    </x-base.button>
                </form>
            </div>
        </x-base.dialog.panel>
    </x-base.dialog>

    <x-base.slideover id="superlarge-slide-over-size-preview" size="xl">
        <x-base.slideover.panel>
            <x-base.slideover.title class="p-5">
                <h2 class="mr-auto text-base font-medium">
                    Códigos de cortesía
                </h2>
            </x-base.slideover.title>
            <x-base.slideover.description>
                <div class="flex justify-between mb-4">
                    <!-- Select para elegir el tipo de ticket -->
                    <div class="w-full">
                        <label for="ticketType" class="block text-sm font-medium text-gray-700">Seleccionar Ticket</label>
                        <select id="ticketType" name="ticketType" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
                            @foreach($tickets as $ticket)
                                <option value="{{ $ticket->id }}">{{ $ticket->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="w-full">
                        <label for="numberOfCoupons" class="block text-sm font-medium text-gray-700">N cupones</label>
                        <input type="number" id="numberOfCoupons">
                    </div>
                    <!-- Botón para generar nuevos códigos -->
                    <button id="generateCouponsButton" class="btn btn-primary ml-4 mt-4">
                        Generar nuevos códigos
                    </button>
                </div>

                <!-- Tabla para mostrar los cupones -->
                <div class="overflow-auto">
                    <table class="table-auto w-full text-center">
                        <thead>
                            <tr>
                                <th class="px-4 py-2">N°</th>
                                <th class="px-4 py-2">Código</th>
                                <th class="px-4 py-2">Ticket</th>
                                <th class="px-4 py-2">Consumido</th>
                            </tr>
                        </thead>
                        <tbody id="couponsTableBody">
                            <!-- Aquí se cargan dinámicamente los cupones -->
                        </tbody>
                    </table>
                </div>
            </x-base.slideover.description>
        </x-base.slideover.panel>
    </x-base.slideover>

    <script>
        function setDeleteAction(element) {
            // Obtener el ID desde el atributo data-id
            const id = element.getAttribute('data-id');
            // Establecer la acción del formulario con la ruta dinámica
            const form = document.getElementById('delete-form');
            form.action = `/assistants/delete/${id}`;
        }
        document.getElementById('generateCouponsButton').addEventListener('click', function () {
            const eventId = {{$idEvent}}; // ID del evento (modificar dinámicamente si es necesario)
            const numberOfCoupons = document.getElementById('ticketType').value;; // Número de cupones que quieres generar
            const ticketTypeId = document.getElementById('ticketType').value; // Obtener el ticket seleccionado

            fetch("{{ route('generateCoupons') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    event_id: eventId,
                    number_of_coupons: numberOfCoupons,
                    ticket_type_id: ticketTypeId // Enviar el ID del ticket
                })
            })
            .then(response => response.json())
            .then(data => {
                // Aquí puedes recargar los cupones y actualizar la tabla
                // loadCoupons(eventId);
            })
            .catch(error => console.error('Error:', error));
        });

        // Función para cargar los cupones y actualizar la tabla
        function loadCoupons(eventId) {
            fetch(`/get-coupons/${eventId}`)
                .then(response => response.json())
                .then(coupons => {
                    let tableBody = document.getElementById('couponsTableBody');
                    tableBody.innerHTML = ''; // Limpiar la tabla

                    coupons.forEach((coupon, index) => {
                        let row = `
                            <tr>
                                <td class="px-4 py-2">${index + 1}</td>
                                <td class="px-4 py-2">${coupon.numeric_code}</td>
                                <td class="px-4 py-2">${coupon.ticket_type.name}</td>
                                <td class="px-4 py-2">${coupon.is_consumed ? 'Sí' : 'No'}</td>
                            </tr>
                        `;
                        tableBody.insertAdjacentHTML('beforeend', row);
                    });
                });
        }
        // loadCoupons({{$idEvent}});
    </script>

    <!-- END: Delete Confirmation Modal -->

        <!-- BEGIN: Pagination -->
        <div class="intro-y col-span-12 flex flex-wrap items-center sm:flex-row sm:flex-nowrap">
            {{ $asistentes->appends(request()->query())->links() }}
        </div>
        <!-- END: Pagination -->
    </div>
    @endif
@endsection
