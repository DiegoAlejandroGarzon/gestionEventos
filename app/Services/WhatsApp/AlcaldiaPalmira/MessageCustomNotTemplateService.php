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

    public function getRegistrationConfirmationMessage(string $userName, string $eventName, string $location, string $dateTime, string $cedula, string $ticketId): string
    {
        $message = "--\n\n"
            . "✅ *Inscripción confirmada*\n\n"
            . "Hola *{$userName},* tu registro al evento *El Pesebre Más Grande del Mundo* ha sido exitoso. 🎉\n\n"
            . "📍 *Lugar:* {$location}\n"
            . "📅 *Fecha y hora:* {$dateTime}\n"
            . "🎟️ *Ticket ID:* TCK-{$ticketId}(guárdalo por si necesitas consultar tu proceso)\n"
            . "🪪 *Cédula asociada al registro:* {$cedula}\n\n"
            . "⚠️ *Importante:* El ingreso al evento será únicamente presentando tu *cédula de ciudadanía*. Es *obligatorio* portarla ese día, ya que será *validada al ingreso*.\n\n"
            . "🔸 Recuerda llegar con anticipación\n\n"
            . "¡Gracias por ser parte de esta gran experiencia!";

        return $message;
    }
    
    public function getAlreadyRegisteredMessage(string $cedula, string $date, string $time, string $ticketId): string
    {
        $message = "--\n\n"
            . "⚠️ *Ya tienes una reserva activa*\n\n"
            . "La cédula *{$cedula}* ya cuenta con una reserva para el evento *'El Pesebre Más Grande del Mundo'*.\n\n"
            . "📅 *Fecha:* {$date}\n"
            . "🕒 *Hora:* {$time}\n"
            . "🎟️ *Ticket ID:* TCK-{$ticketId}(guárdalo por si necesitas gestionar tu proceso)\n\n"
            . "🔸 Si necesitas cambiar tu reserva, por favor comunícate con el equipo de atención.\n\n"
            . "Gracias por tu interés en participar. ¡Nos vemos en el evento!";

        return $message;
    }

}
