<?php

namespace App\Services\WhatsApp\AlcaldiaPalmira;

use App\Services\WhatsApp\MessageService;
use App\Services\WhatsApp\CurlService;
use App\Services\WhatsApp\QueryService;

class MenuCustomService
{
    private $__externalPhoneNumber;
    private $__numberWhatssAppId;
    public function __construct($__externalPhoneNumber, $__numberWhatssAppId) {
        $this->__externalPhoneNumber = $__externalPhoneNumber;
        $this->__numberWhatssAppId = $__numberWhatssAppId;
    }
    
    public function sendMenu_initial($dataCurl)
    {
        $headerText = '🎄 Alcaldía de Palmira te da la bienvenida';
        $bodyText = '📍 La Alcaldía de Palmira te invita a disfrutar de *El Pesebre Más Grande del Mundo*, un evento mágico para toda la familia. Desde aquí podrás *reservar tus boletas* y acceder a toda la información del evento.';
        $footerText = '🎫 ¿Qué deseas hacer hoy?';
        $buttonText = 'Ver opciones';

        $sections = [
            [
                'title' => 'Opciones disponibles ✨',
                'rows' => [
                    [
                        'id' => 'reservar_boletas',
                        'title' => '🎟️ Reservar boletas',
                        'description' => 'Asegura tu entrada al evento.'
                    ],
                    [
                        'id' => 'informacion_evento',
                        'title' => 'ℹ️ Información del evento',
                        'description' => 'Horarios, ubicación y más detalles.'
                    ],
                    [
                        'id' => 'preguntas_frecuentes',
                        'title' => '❓ Preguntas frecuentes',
                        'description' => 'Resolvemos tus dudas más comunes.'
                    ]
                ]
            ]
        ];

        $messageService = new MessageService($this->__externalPhoneNumber, $this->__numberWhatssAppId);
        $data = $messageService->getDataMenuList($headerText, $bodyText, $footerText, $buttonText, $sections);

        $curlService = new CurlService($this->__numberWhatssAppId);
        $curlService->curlFacebookApi($data, $this->__numberWhatssAppId);

        return [
            'type' => 'menu_list',
            'header' => $headerText,
            'body' => $bodyText,
            'footer' => $footerText,
            'button' => $buttonText,
            'sections' => $sections
        ];
    }

    public function sendMenu_selectHorario(array $horarios, string $fecha = null)
    {
        $headerText = '🎟️ Selecciona horario';
        $bodyText =  "Estos son los horarios disponibles para el día *" . \Carbon\Carbon::parse($fecha)->translatedFormat('l d \d\e F') . "*. Elige el que prefieras para reservar tus boletas.";

        $footerText = '📅 Horarios disponibles';
        $buttonText = 'Ver horarios';

        $sections = [];

        // Si no se pasa fecha, usamos una genérica
        $fechaFormateada = \Carbon\Carbon::parse($fecha)->translatedFormat('D d M'); 

        $rows = [];

        foreach ($horarios as $horario) {
            if (empty($horario['available'])) continue; // Solo mostrar los que tienen cupo

            $hourStart = \Carbon\Carbon::parse($horario['start'])->format('h:i A');
            $hourEnd = \Carbon\Carbon::parse($horario['end'])->format('h:i A');
            $rows[] = [
                'id' => 'seleccion_horario_' . $fecha. "$" .$horario['start']."|".$horario['end']."|".$horario['ticket_type_id'],
                'title' => "🕒 {$hourStart} - {$hourEnd}",
                'description' => "Clic para reservar — 📦 {$horario['remaining']} de {$horario['capacity']} disponibles"
            ];
        }

        if (!empty($rows)) {
            $sections[] = [
                'title' => "🗓️ $fechaFormateada",
                'rows' => $rows
            ];
        }

        $messageService = new MessageService($this->__externalPhoneNumber, $this->__numberWhatssAppId);
        $data = $messageService->getDataMenuList($headerText, $bodyText, $footerText, $buttonText, $sections);

        $curlService = new CurlService($this->__numberWhatssAppId);
        $curlService->curlFacebookApi($data, $this->__numberWhatssAppId);
        file_put_contents(storage_path().'/logs/log_webhook.txt', "<- MENU_SECTIONS_JSON ->" .json_encode($sections). PHP_EOL, FILE_APPEND);
        return [
            'type' => 'menu_list',
            'header' => $headerText,
            'body' => $bodyText,
            'footer' => $footerText,
            'button' => $buttonText,
            'sections' => $sections
        ];
    }

    public function sendMenu_selectDia(array $availableDays)
    {
        $headerText = '📆 Selecciona un día';
        $bodyText = 'Estos son los días con horarios disponibles para reservar tus boletas. Selecciona el día que prefieras.';
        $footerText = '✨ Días disponibles';
        $buttonText = 'Ver días';

        $rows = [];

        foreach ($availableDays as $fecha) {
            // Ej: "Vie 15 Nov"
            $fechaFormateada = \Carbon\Carbon::parse($fecha)->translatedFormat('l d \d\e M');

            $rows[] = [
                'id' => 'seleccion_dia_' . $fecha,
                'title' => "🗓️ $fechaFormateada", // Máximo 24 caracteres
                'description' => 'Ver horarios disponibles para este día' // Máximo 72 caracteres
            ];
        }


        // Si no hay días disponibles
        if (empty($rows)) {
            $responseText = "🚫 No hay días con cupos disponibles por ahora. Intenta más tarde.";

            $messageService = new MessageService($this->__externalPhoneNumber, $this->__numberWhatssAppId);
            $responseTplArr = $messageService->sendMessageNotTemplate($this->__externalPhoneNumber, $responseText, "No hay días disponibles", false, null);

            $queryService = new QueryService($this->__externalPhoneNumber, $this->__numberWhatssAppId);
            $queryService->storeResponseAutoBot(
                "Respuesta automática",
                null,
                "text",
                "auto_text",
                $responseTplArr
            );

            return;
        }

        // Construir la única sección del menú
        $sections = [
            [
                'title' => '📅 Dias disponibles',
                'rows' => $rows
            ]
        ];

        // Enviar el menú
        $messageService = new MessageService($this->__externalPhoneNumber, $this->__numberWhatssAppId);
        $data = $messageService->getDataMenuList($headerText, $bodyText, $footerText, $buttonText, $sections);

        $curlService = new CurlService($this->__numberWhatssAppId);
        $curlService->curlFacebookApi($data, $this->__numberWhatssAppId);

        return [
            'type' => 'menu_list',
            'header' => $headerText,
            'body' => $bodyText,
            'footer' => $footerText,
            'button' => $buttonText,
            'sections' => $sections
        ];
    }

}
