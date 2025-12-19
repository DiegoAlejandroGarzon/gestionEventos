<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Asistentes del Evento</title>
    <style>
        body {
            margin: 0;
            font-family: Arial, Helvetica, sans-serif;
            background-color: beige;
            text-align: center;
            font-size: 9px; /* Reducción del tamaño de fuente */
        }
        .content {
            width: 100%;
            padding: 10px; /* Menor padding para ajustar el contenido */
        }
        .card {
            margin: 0 auto;
            width: 100%;
            max-width: 800px;
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 0 5px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            padding: 10px; /* Agregado padding interno */
        }
        .header, .footer {
            background-color: #008083;
            color: white;
            padding: 5px; /* Menor padding */
        }
        .section {
            margin: 10px 0; /* Menor margen entre secciones */
        }
        h1, h2, h3 {
            margin: 5px 0; /* Menor margen en encabezados */
        }
        p {
            margin: 3px 0; /* Menor margen en párrafos */
        }
        .image-fit {
            width: 100%;
            max-width: 180px; /* Reducir el tamaño máximo de la imagen */
            height: auto;
            margin: 0 auto;
        }
        .qr-code {
            margin-top: 10px; /* Menor margen superior */
        }
        .status-alert {
            padding: 5px; /* Menor padding */
            border-radius: 5px;
            margin-top: 5px; /* Menor margen superior */
        }
        .bg-success { background-color: #28a745; color: white; }
        .bg-warning { background-color: #ffc107; color: black; }
        .bg-danger { background-color: #dc3545; color: white; }
    </style>
</head>
<body>
    <div class="content">
        <div class="card">
            <div class="header">
                <h1>Ticket del evento: {{$eventAssistant->event->name}}</h1>
            </div>
            <div class="section">
                <img class="image-fit" src="{{ storage_path('app/public/'. $eventAssistant->event->header_image_path) }}" alt="{{ $eventAssistant->event->name }}">
                <h2>Información del evento</h2>
                <p><strong>Nombre:</strong> {{ $eventAssistant->event->name }}</p>
                <p><strong>Fecha:</strong> {{ $eventAssistant->event->event_date }}</p>
                <p><strong>Hora de Inicio:</strong> {{ $eventAssistant->event->start_time }}</p>
                <p><strong>Ciudad:</strong> {{ $eventAssistant->event->city->name ?? 'N/A' }}</p>
                <p><strong>Dirección:</strong> {{ $eventAssistant->event->address }}</p>
            </div>

            <div class="section">
                <h2>Información del asistente</h2>
                @php
                    $selectedFields = collect(json_decode($eventAssistant->event->registration_parameters, true) ?? [])->sortBy('order');
                    $additionalParameters = json_decode($eventAssistant->event->additionalParameters, true) ?? [];
                        
                @endphp

                @foreach($selectedFields as $field)
                    @php
                        $fieldName = $field['name'];
                        $value = $eventAssistant->user->{$fieldName} ?? null;
                    @endphp

                    @if(!is_null($value))
                        <p>
                            <strong>
                                {{ config("traductorColumnasUsers.$fieldName", ucfirst(str_replace('_', ' ', $fieldName))) }}
                            </strong>:
                            {{ $value }}
                        </p>
                    @endif
                @endforeach
                @foreach($additionalParameters as $parameter)
                    @php
                        $userParameter = $eventAssistant->eventParameters->where('event_id', $eventAssistant->event_id)->where('additional_parameter_id', $parameter['id'])->first();
                    @endphp
                    <p><strong>{{ ucfirst(str_replace('_', ' ', $parameter['name'])) }}</strong>: {{ $userParameter ? $userParameter->value : '-' }}</p>
                @endforeach
                <p><strong>Fecha de Registro:</strong> {{ $eventAssistant->created_at->format('d/m/Y') }}</p>
            </div>

            <div class="section">
                <h2>Información del ticket</h2>
                @if($eventAssistant?->ticketType)
                    <p><strong>Tipo de Ticket:</strong> {{ $eventAssistant->ticketType->name ?? 'N/A' }}</p>
                    <ul>
                        @if (!empty($eventAssistant?->ticketType?->features))
							@foreach ($eventAssistant->ticketType->features as $feature)
								<li>
									<strong>{{ $feature->name }}:</strong>
									<span>{{ $feature->consumable ? 'Consumible' : 'Acceso' }}</span>
								</li>
							@endforeach
						@endif

                    </ul>
                @endif
                <div class="status-alert {{ $eventAssistant->is_paid ? 'bg-success' : ($eventAssistant->totalPayments() == 0 ? 'bg-danger' : 'bg-warning') }}">
                    <h3>ESTADO DEL PAGO DEL TICKET:</h3>
                    <p>{{ $eventAssistant->is_paid ? 'Pagado' : ($eventAssistant->totalPayments() == 0 ? 'No Pagado' : 'Pendiente') }}</p>
                </div>
            </div>

            <div class="qr-code">
                @if($eventAssistant->qrCode)
                    <img src="{{ $qrCodeBase64 }}" alt="QR Code" class="image-fit">
                @else
                    <p>Este asistente no tiene un código QR asociado.</p>
                @endif
            </div>
            <div class="footer">
                <h3>Credencial Virtual</h3>
            </div>
        </div>
    </div>
</body>
</html>
