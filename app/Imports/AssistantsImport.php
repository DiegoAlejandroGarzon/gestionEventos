<?php

namespace App\Imports;

use App\Models\Event;
use App\Models\User;
use App\Models\EventAssistant;
use App\Models\TicketType;
use Exception;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Facades\Validator;
use PhpOffice\PhpSpreadsheet\Shared\Date; // Asegúrate de incluir esto

class AssistantsImport implements ToModel, WithHeadingRow
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
            // Obtener el evento y los parámetros de inscripción
            $event = Event::findOrFail($this->eventId);
            $registrationParameters = json_decode($event->registration_parameters, true) ?? [];

            // Construir reglas de validación dinámicas
            $validationRules = [];
            foreach ($registrationParameters as $param) {
                switch ($param) {
                    case 'name':
                    case 'lastname':
                        $validationRules[$param] = 'required|string|max:255';
                        break;
                    case 'email':
                        $validationRules[$param] = 'required|email|max:255|unique:users,email';
                        break;
                    case 'type_document':
                        $validationRules[$param] = 'required|string|max:3';
                        break;
                    case 'document_number':
                        $validationRules[$param] = 'required|max:20|unique:users,document_number';
                        break;
                    case 'phone':
                        $validationRules[$param] = 'nullable|max:15';
                        break;
                    case 'city_id':
                        $validationRules[$param] = 'nullable|exists:cities,id';
                        break;
                    case 'birth_date':
                        $validationRules[$param] = 'nullable|date';
                        break;
                    // Agrega más parámetros según sea necesario
                }
            }

            // Convertir birth_date de Excel a una fecha válida
            if (isset($row['birth_date'])) {
                $birthDate = Date::excelToDateTimeObject($row['birth_date']);
                $row['birth_date'] = $birthDate ? $birthDate->format('Y-m-d') : null; // Formato deseado
            }

            // Validar el row (se podría hacer esto fuera de la función para optimizar)
            Validator::make($row, $validationRules)->validate();

            // Crear o encontrar el usuario
            $user = User::firstOrCreate(
                ['email' => $row['email']],
                array_intersect_key($row, array_flip($registrationParameters)) + [
                    'password' => bcrypt('12345678'),
                    'status' => true,
                ]
            );

            // Asignar el rol "Assistant" al usuario
            if (!$user->hasRole('assistant')) {
                $user->assignRole('assistant');
            }

            // Buscar el tipo de ticket por nombre y event_id
            $ticketType = TicketType::where('event_id', $this->eventId)
                                    ->where('name', $row['ticket_type'])
                                    ->first();

            if (!$ticketType) {
                throw new Exception("Ticket type '{$row['ticket_type']}' not found for event ID {$this->eventId}");
            }

            // Crear o actualizar el registro en la tabla `event_assistant`
            $eventAssistant = EventAssistant::updateOrCreate(
                [
                    'event_id' => $this->eventId,
                    'user_id' => $user->id,
                ],
                [
                    'ticket_type_id' => $ticketType->id,
                    'has_entered' => false, // O cualquier valor predeterminado que necesites
                ]
            );

            // Añadir usuario a la lista de importados
            $this->importedUsers[] = [
                'user' => $user,
                'ticket_type' => $ticketType->name,
            ];
        } catch (Exception $e) {
            // Guardar el mensaje de error
            $this->messages[] = "Error con usuario {$row['email']}: " . $e->getMessage();
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
