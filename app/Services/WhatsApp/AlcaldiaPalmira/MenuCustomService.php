<?php

namespace App\Services\WhatsApp\AlcaldiaPalmira;

use App\Services\WhatsApp\MessageService;
use App\Services\WhatsApp\CurlService;

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

}
