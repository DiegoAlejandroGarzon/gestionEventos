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
        $headerText = '🌆 Alcaldía de Palmira te da la bienvenida';
        $bodyText = '🙌 La *Alcaldía de Palmira* te invita a disfrutar de nuestras actividades culturales, recreativas y turísticas. Desde aquí podrás *reservar tus boletas* y conocer toda la información sobre los eventos de nuestra ciudad. #PalmiraSeTransforma';
        $footerText = '🎫 ¿Qué deseas hacer hoy?';
        $buttonText = 'Ver opciones';

        // Definir las secciones según el número de WhatsApp ID
        // Definimos la sección base
        $culturalRows = [];

        // Condicional solo para los rows
        if ($this->__numberWhatssAppId === '855752667617564') { // linea john
            // Si es este número, muestra este evento
            $culturalRows[] = [
                'id' => 'reservar_boletas_funcionarios',
                'title' => '🐪️ Pesebre en vivo',
                'description' => 'Vive la magia con el pesebre más grande del mundo'
            ];
        } elseif($this->__numberWhatssAppId === '845528951979695') { // oficial palmira
            // Para otros números, mostramos otra opción
            $culturalRows[] = [
                'id' => 'alcapalmira_register_panafest',
                'title' => '🎉 Pana Fest 2025',
                'description' => 'Un festival lleno de juventud, música y talento palmirano'
            ];
        }

        // Sección de eventos culturales (siempre presente)
        $sections[] = [
            'title' => 'Eventos culturales 🎭',
            'rows'  => $culturalRows
        ];

        // Esta sección se muestra siempre
        $sections[] = [
            'title' => 'Otra Información 🧾',
            'rows' => [
                [
                    'id' => 'informacion_evento',
                    'title' => 'ℹ️ Información de eventos',
                    'description' => 'Consulta horarios, ubicación y más detalles.'
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


    public function sendMenu_selectHorario(array $horarios, string $fecha = null, int $eventId)
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
                'id' => 'seleccion_horario_' . $fecha. "$" .$horario['start']."|".$horario['end']."|".$horario['ticket_type_id']."|".$eventId,
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

        foreach ($availableDays["days"] as $fecha) {
            // Ej: "Vie 15 Nov"
            $fechaFormateada = \Carbon\Carbon::parse($fecha)->translatedFormat('l d \d\e M');

            $rows[] = [
                'id' => 'seleccion_dia_' .$availableDays["eventId"]."|". $fecha,
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
