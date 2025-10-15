@extends('../themes/' . $activeTheme . '/' . $activeLayout)

@section('subhead')
    <title>Detalles del Asistente y del Evento</title>
@endsection

@section('subcontent')
    <div class="box">
        <a class="m-3" href="{{ route('eventAssistant.index', ['idEvent' => $eventAssistant->event_id]) }}">Cargar Listado Asistentes</a>
    </div>
    <h2 class="intro-y mt-10 text-lg font-medium">Detalles del Asistente y del Evento</h2>
    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <x-base.alert
        class="mb-2 flex items-center"
        variant="danger"
        >
            <x-base.lucide
                class="mr-2 h-6 w-6"
                icon="AlertCircle"
            />
            {{ session('error') }}
        </x-base.alert>
    @endif

    @if(session('warning'))
        <x-base.alert
        class="mb-2 flex items-center"
        variant="warning"
        >
            <x-base.lucide
                class="mr-2 h-6 w-6"
                icon="AlertCircle"
            />
            {{ session('warning') }}
        </x-base.alert>
    @endif


    <div class="mt-5">
        <div class="">
            @if ($eventAssistant->is_paid)
                <div role="alert" class="alert rounded-md bg-success text-slate-900 dark:border-success box p-5">
                    <H1>ESTADO DEL PAGO DEL TICKET: </H1>Pagado
                </div>
            @else
                @if ($eventAssistant->totalPayments() == 0)
                <div role="alert" class="alert relative border rounded-md bg-danger border-danger text-white dark:border-danger mb-2 box p-5">
                    <H1>ESTADO DE LA BOLETA DEL TICKET: </H1>No Pagado

                </div>
                @else
                <div role="alert" class="alert rounded-md bg-warning text-slate-900 dark:border-warning box p-5">
                    <H1>ESTADO DE LA BOLETA DEL TICKET: </H1>Pendiente
                </div>
                @endif
            @endif
        </div>
    </div>
    <div class="mt-5">
        <div class="box p-5">
            <h3 class="text-lg font-medium">Información del Asistente</h3>

            <!-- Cargar informacion del asistente con sus parametros establecidos -->
            @php
                // Obtener los parámetros guardados en registration_parameters
                $selectedFields = json_decode($eventAssistant->event->registration_parameters, true) ?? [];
                $additionalParameters = json_decode($eventAssistant->event->additionalParameters, true) ?? [];
            @endphp
            @foreach($selectedFields as $field)
                <p class=""><strong>{{ config("traductorColumnasUsers.$field", ucfirst(str_replace('_', ' ', $field))) }}</strong>: {{ $eventAssistant->user->$field }}</p>
            @endforeach
            @foreach($additionalParameters as $parameter)
            @php
                $userParameter = $eventAssistant->eventParameters->where('event_id', $eventAssistant->event_id)->where('additional_parameter_id', $parameter['id'])->first();
            @endphp
                <p class=""><strong>{{ ucfirst(str_replace('_', ' ', $parameter['name'])) }}</strong>: {{ $userParameter ? $userParameter->value : '-' }}</p>
            @endforeach
            <!-- FIN Cargar informacion del asistente con sus parametros establecidos -->

            <p><strong>Tipo de Ticket:</strong> {{ $eventAssistant->ticketType->name ?? 'N/A' }}</p>
            <p><strong>Fecha de Registro:</strong> {{ $eventAssistant->created_at->format('d/m/Y') }}</p>
            <p><strong>Código QR:</strong></p>
            <div class="mt-2 max-w-full overflow-x-auto">
				<div class="w-full max-w-xs mx-auto">
					{!! $eventAssistant->qrCode !!}
				</div>
			</div>


            <br>

            <h3 class="text-lg font-medium mt-5">Información del Evento</h3>
            <p><strong>Nombre del Evento:</strong> {{ $eventAssistant->event->name }}</p>
            <p><strong>Descripción:</strong> {{ $eventAssistant->event->description }}</p>
            <p><strong>Fecha del Evento:</strong> {{ $eventAssistant->event->event_date }}</p>
            <p><strong>Hora de Inicio:</strong> {{ $eventAssistant->event->start_time }}</p>
            <p><strong>Hora de Finalización:</strong> {{ $eventAssistant->event->end_time }}</p>
            <p><strong>Ciudad:</strong> {{ $eventAssistant->event->city->name ?? 'N/A' }}</p>
            <p><strong>Capacidad:</strong> {{ $eventAssistant->event->capacity }}</p>
            <br>
            <h3 class="text-lg font-medium mt-5">Características del Ticket</h3>
            <ul>
                @if (!empty($eventAssistant?->ticketType?->features))

					@foreach ($eventAssistant?->ticketType?->features as $feature)
						<li>
							<strong>{{ $feature->name }}:</strong>
							@if ($feature->consumable)
							@php
								$featureConsumption = App\Models\FeatureConsumption::where('event_assistant_id', $eventAssistant->id)->where('ticket_feature_id', $feature->id)->first()
							@endphp
								@if (isset($featureConsumption))
										<span class="text-gray-600">Consumido - {{$featureConsumption->consumed_at}}</span>
								@else
									<form action="{{ route('eventAssistant.consumeFeature', [$eventAssistant->id, $feature->id]) }}" method="POST" class="inline-block">
										@csrf
										@method('PATCH')
										<x-base.button type="submit" variant="success">Consumir</x-base.button>
									</form>
								@endif
							@else
								<span class="text-gray-600">No Consumible</span>
							@endif
						</li>
					@endforeach
                @endif
            </ul>
            <br>
            <!-- Botón para Registrar Ingreso -->

            @if(!$eventAssistant->has_entered && !$eventAssistant->rejected)
            <form action="{{ route('eventAssistant.registerEntry', $eventAssistant->id) }}" method="POST">
                @csrf
                @method('PATCH')
                <x-base.button
                type="submit"
                variant="primary"
                >
                Registrar Ingreso
                </x-base.button>
            </form>
            <br>
            <form action="{{ route('eventAssistant.rejectEntry', $eventAssistant->id) }}" method="POST" class="inline-block">
                @csrf
                @method('PATCH')
                <x-base.button
                type="submit"
                variant="danger"
                >
                Rechazar Ingreso
                </x-base.button>
            </form>
            @else
            <div class="mt-2 flex items-center">
                @if($eventAssistant->has_entered)
                <x-base.alert
                class="mb-2 flex items-center"
                variant="warning"
                >
                    <x-base.lucide
                        class="mr-2 h-6 w-6"
                        icon="AlertCircle"
                    />
                    Status:
                    YA HA REGISTRADO EL INGRESO el {{$eventAssistant->entry_time}}
                </x-base.alert>
                @endif

                @if($eventAssistant->rejected)
                <x-base.alert
                class="mb-2 flex items-center"
                variant="danger"
                >
                    <x-base.lucide
                        class="mr-2 h-6 w-6"
                        icon="AlertCircle"
                    />
                    Status:
                    INGRESO RECHAZADO el {{$eventAssistant->rejected_time}}
                </x-base.alert>

                @if(!$eventAssistant->has_entered)
                <br>
                <form action="{{ route('eventAssistant.registerEntry', $eventAssistant->id) }}" method="POST">
                    @csrf
                    @method('PATCH')
                    <x-base.button
                    type="submit"
                    variant="primary"
                    >
                    Registrar Ingreso
                    </x-base.button>
                </form>
                @endif
                @endif
            </div>
            @endif
        </div>
        </div>
    </div>
@endsection
