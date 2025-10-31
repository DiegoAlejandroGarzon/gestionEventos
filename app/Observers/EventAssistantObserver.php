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
        $updateData = [];

        // Marcar pagado si el tipo de ticket es gratuito
        $ticketType = TicketType::find($eventAssistant->ticket_type_id);
        if ($ticketType && $ticketType->price == 0) {
            $updateData['is_paid'] = true;
        }

        // Obtener el evento y comprobar si está permitido generar QR
        $event = $eventAssistant->event; // relación belongsTo en el modelo
        $guid = $eventAssistant->guid ?? Str::uuid()->toString();
        if ($event && ($event->generate_qr ?? true)) {
            $qrContent = route('eventAssistant.infoQr', ['id' => $eventAssistant->id, 'guid' => $guid]);
            $qrCode = QrCode::format('svg')->size(300)->generate($qrContent);
            $updateData['qrCode'] = $qrCode;
        }

        $updateData['guid'] = $guid;

        // Actualizar sólo si hay datos a guardar
        if (!empty($updateData)) {
            $eventAssistant->update($updateData);
        }

        // Enviar correo sólo si se generó QR y existe email
        if (($event->send_email ?? true)  && $eventAssistant->user && !empty($eventAssistant->user->email)) {
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
