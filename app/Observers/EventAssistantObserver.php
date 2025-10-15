<?php

namespace App\Observers;

use App\Models\EventAssistant;
use App\Models\TicketType;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Illuminate\Support\Str;

class EventAssistantObserver
{
    /**
     * Handle the EventAssistant "created" event.
     */
    public function created(EventAssistant $eventAssistant)
    {
        // Generar un GUID
        $guid = Str::uuid()->toString();
        // Generar el QR Code basado en el ID o alguna otra información del modelo
        $qrContent = route('eventAssistant.infoQr', ['id' => $eventAssistant->id, 'guid' => $guid]);
        $qrCode = QrCode::format('svg')->size(300)->generate($qrContent);
        // Inicializar el array para los valores que se actualizarán
        $updateData = [
            'qrCode' => $qrCode,
            'guid' => $guid,
        ];
        // Verificar si el ticket_type_id está relacionado con un TicketType cuyo precio sea 0
        $ticketType = TicketType::find($eventAssistant->ticket_type_id);
        if ($ticketType && $ticketType->price == 0) {
            $updateData['is_paid'] = true;
        }
        // Actualizar el modelo con los valores generados
        $eventAssistant->update($updateData);

        // Verificar si el user_id tiene un valor de email y enviar el correo
        if ($eventAssistant->user && !empty($eventAssistant->user->email)) {
            // Instanciar el controlador y llamar a la función sendEmail
            app(\App\Http\Controllers\EventAssistantController::class)->sendEmail($eventAssistant->id);
        }
    }

    /**
     * Handle the EventAssistant "updated" event.
     */
    public function updated(EventAssistant $eventAssistant): void
    {
        //
    }

    /**
     * Handle the EventAssistant "deleted" event.
     */
    public function deleted(EventAssistant $eventAssistant): void
    {
        //
    }

    /**
     * Handle the EventAssistant "restored" event.
     */
    public function restored(EventAssistant $eventAssistant): void
    {
        //
    }

    /**
     * Handle the EventAssistant "force deleted" event.
     */
    public function forceDeleted(EventAssistant $eventAssistant): void
    {
        //
    }
}
