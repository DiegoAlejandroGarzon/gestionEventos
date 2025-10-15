<!DOCTYPE html>
<html>
<head>
    <title>Email de Asistencia</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 600px;
            margin: 20px auto;
            background-color: #ffffff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        .header {
            text-align: center;
            background-color: #007bff;
            color: white;
            padding: 10px 0;
            border-radius: 8px 8px 0 0;
        }
        .content {
            padding: 20px;
            line-height: 1.6;
            color: #333333;
        }
        .content h2 {
            color: #007bff;
            font-size: 20px;
            margin-top: 0;
        }
        .content p {
            margin: 10px 0;
        }
        .footer {
            margin-top: 20px;
            font-size: 12px;
            color: #777777;
            text-align: center;
            border-top: 1px solid #e4e4e4;
            padding-top: 10px;
        }
        .qr-code {
            text-align: center;
            margin: 20px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Inscripción al Evento</h1>
        </div>
        <div class="content">
            <p>Se ha generado una inscripción para el evento de <strong>TuBoleta</strong>. A continuación, se envían los detalles de su inscripción:</p>

            <h2>Detalles del Asistente</h2>
            <p><strong>Nombre:</strong> {{ $eventAssistant->user->name }} {{ $eventAssistant->user->lastname }}</p>

            <h2>Detalles del Evento</h2>
            <p><strong>Evento:</strong> {{ $eventAssistant->event->name }}</p>
            <p><strong>Descripción:</strong> {{ $eventAssistant->event->description }}</p>
            <p><strong>Fecha y Hora:</strong> {{ $eventAssistant->event->startHour }}</p>
            <p><strong>Ubicación:</strong> {{ $eventAssistant->event->address }}, {{ $eventAssistant->event->city->name ?? 'N/A' }}, {{ $eventAssistant->event->city->department->name ?? 'N/A' }}</p>

            <div class="footer">
                <p>El/los pre-registros realizados a los diferentes eventos de la Agenda no garantizan la participación. Para garantizar el bienestar y seguridad de los asistentes, al completar el aforo, se restringirá el acceso por orden de llegada. Es decir, las personas que lleguen después de completar el aforo no podrán ingresar.</p>
            </div>

            <h2>Su Código QR</h2>
            <div class="qr-code">
                <img src="data:image/svg+xml;base64,{{ $qrCodeBase64 }}" alt="Código QR" style="max-width: 300px; height: auto;">
            </div>
            <p>Conserve este código QR para ingresar al evento.</p>

        </div>
    </div>
</body>
</html>
