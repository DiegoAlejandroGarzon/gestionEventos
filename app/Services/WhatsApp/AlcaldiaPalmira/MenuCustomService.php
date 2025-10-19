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

    public function sendMenu_selectHorario($availabilityData)
    {
        $headerText = '🎟️ Selecciona horario';
        $bodyText = 'Estos son los días y horarios con cupos disponibles. Elige el que prefieras para reservar tus boletas.';
        $footerText = '📅 Horarios disponibles';
        $buttonText = 'Ver horarios';

        $sections = [];

        foreach ($availabilityData as $fecha => $horarios) {
            // Convertimos la fecha a un formato más legible (ej. "Domingo 19 de Octubre")
            $fechaFormateada = \Carbon\Carbon::parse($fecha)->translatedFormat('l d \d\e F');

            $rows = [];

            foreach ($horarios as $horario) {
                if (empty($horario['available'])) continue; // Solo mostrar los que tienen cupo

                $rows[] = [
                    'id' => 'seleccion_horario_' . $horario['ticket_type_id'],
                    'title' => "🕒 {$horario['start']} - {$horario['end']}",
                    'description' => 'Haz clic para reservar este horario'
                ];
            }

            // Si hay al menos un horario disponible en esta fecha, agregar sección
            if (!empty($rows)) {
                $sections[] = [
                    'title' => "🗓️ $fechaFormateada",
                    'rows' => $rows
                ];
            }
        }

        // Si no hay horarios disponibles
        if (empty($sections)) {
            $responseText = "🚫 No hay horarios disponibles por ahora. Intenta más tarde.";

            $messageService = new MessageService($this->__externalPhoneNumber, $this->__numberWhatssAppId);
            $responseTplArr = $messageService->sendMessageNotTemplate($this->__externalPhoneNumber, $responseText, "No hay entradas disponibles", false, null);

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

        // ⚠️ Si solo hay una sección (es decir, un solo día con horarios), agregamos la fecha al footer
        if (count($sections) === 1) {
            $footerText .= ' para ' . strtolower($sections[0]['title']); // Ej: 📅 Horarios disponibles para domingo 19 de octubre
        }

        // Construir y enviar el menú tipo lista
        $messageService = new MessageService($this->__externalPhoneNumber, $this->__numberWhatssAppId);
        $data = $messageService->getDataMenuList($headerText, $bodyText, $footerText, $buttonText, $sections);

        $curlService = new CurlService($this->__numberWhatssAppId);
        $curlService->curlFacebookApi($data, $this->__numberWhatssAppId);

        file_put_contents(storage_path() . '/logs/log_webhook.txt', "<- MENU_BOLETAS ->" . json_encode($sections) . PHP_EOL, FILE_APPEND);

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
