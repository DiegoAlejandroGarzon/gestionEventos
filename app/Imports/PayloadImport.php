<?php

namespace App\Imports;

use App\Models\User;
use App\Models\EventAssistant;
use App\Models\Payment;
use App\Models\TicketType;
use Exception;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class PayloadImport implements ToModel, WithHeadingRow
{
    protected $eventId;
    protected $importedUsers = [];
    protected $messages = [];

    public function __construct($eventId)
    {
        $this->eventId = $eventId;
    }

    public function model(array $row)
    {
        try {
            $eventAsistant = EventAssistant::where('event_id', $this->eventId)
                ->whereHas('user', function ($query) use ($row) {
                    $query->where('document_number', $row['numero_de_documentos_del_asistente']);
                })
                ->first();
            $payment = Payment::create(
                [
                    'event_assistant_id' => $eventAsistant->id,
                    'payer_name' => $row['nombre_del_pagador'],
                    'payer_document_type' => $row['tipo_documento'],
                    'payer_document_number' => $row['numero_documento'],
                    'amount' => $row['valor_a_pagada'],
                    'payment_method' => $row['formato_de_pago_transferencia_efectivo'],
                    'description' => 'Pago de Ticket',
                ]
            );

            if($eventAsistant->isFullyPaid()){
                $eventAsistant->is_paid = true;
                $eventAsistant->save();
            }
            // Añadir usuario a la lista de importados
            $this->importedUsers[] = [
                'payment' => $payment,
                'eventAsistant' => $eventAsistant,
            ];
        } catch (Exception $e) {
            // Guardar el mensaje de error
            $this->messages[] = "Error con usuario {$row['numero_de_documentos_del_asistente']}: " . $e->getMessage();
        }
    }

    public function getImportedUsers()
    {
        return $this->importedUsers;
    }

    public function getMessages()
    {
        return $this->messages;
    }
}
