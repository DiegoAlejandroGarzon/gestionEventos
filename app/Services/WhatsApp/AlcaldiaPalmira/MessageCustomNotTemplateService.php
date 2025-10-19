<?php
namespace App\Services\WhatsApp\AlcaldiaPalmira;

class MessageCustomNotTemplateService
{
    
    public function getWelcomeMessage(): string
    {
        $message = "--\n\n"
            . "🎄 *La Alcaldía de Palmira te da la bienvenida.*\n\n"
            . "Estás a un paso de vivir la experiencia de *'El Pesebre Más Grande del Mundo'*, un evento único para disfrutar en familia.\n\n"
            . "📌 En el *MENÚ PRINCIPAL* encontrarás la opción para *reservar tus boletas* de forma rápida y sencilla.\n"
            . "Gracias por hacer parte de esta gran celebración. ¡Te esperamos!";

        return $message;
    }


}
