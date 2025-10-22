<?php

namespace App\Services\WhatsApp\AlcaldiaPalmira;

use App\Services\WhatsApp\MessageService;
use App\Services\WhatsApp\QueryService;
use App\Services\WhatsApp\AlcaldiaPalmira\MessageCustomNotTemplateService;
use App\Services\EventService;
use App\Services\WhatsApp\AlcaldiaPalmira\MenuCustomService;
use App\Services\PublicRegistrationService;
use Illuminate\Http\Request;
use App\Models\ConversationsMessages;

class InteractionNfmReplyService
{
    private $__externalPhoneNumber;
    private $__numberWhatssAppId;
    private $__messageCustomNotTemplateService;
    
    public function __construct($__externalPhoneNumber, $__numberWhatssAppId) {
        $this->__externalPhoneNumber = $__externalPhoneNumber;
        $this->__numberWhatssAppId = $__numberWhatssAppId;
        $this->__messageCustomNotTemplateService = new MessageCustomNotTemplateService();
    }
    
    public function verified($nfm_reply, $message_whatsapp_id, $timestamp, $type_closed=null, $context=null){
        
        $response_json = json_decode($nfm_reply['response_json'], true);
        // ingresamos la respuesta del usuario en BD y enviamos pusher
        $queryService = new QueryService($this->__externalPhoneNumber, $this->__numberWhatssAppId);
        $queryService->storeResponseAutoUser(
            "Envio de formulario", 
            $message_whatsapp_id, 
            "response_text_nfm_reply",
            $response_json['request_action'],
            $timestamp
        );
        file_put_contents(storage_path().'/logs/log_webhook.txt', "<- CONTEXT_NFM ->" .json_encode($response_json['request_action']). PHP_EOL, FILE_APPEND);
        // consultamos el id del ticket
        $conversationsMessages = new ConversationsMessages();
        $dataConvMessage = $conversationsMessages->where("message_what_id", $context["id"])->first();
        file_put_contents(storage_path().'/logs/log_webhook.txt', "<- CONTEXT_NFM 222 ->" .json_encode($dataConvMessage). PHP_EOL, FILE_APPEND);
        $paramsTemplate = $dataConvMessage->params_template;
        $arrParams = explode("$", $paramsTemplate[2]['text']);
        
        $messageCustomNotTemplateService = new MessageCustomNotTemplateService();
        // enviamos la respuesta al cliente
        $messageService = new MessageService($this->__externalPhoneNumber, $this->__numberWhatssAppId);
        
        switch($response_json['request_action']){
            
            // reserva
            case "register_boletas":
                $public_link = "f3c6707b-d9f7-4051-95ad-6228c555bd84";
                // Datos simulados del formulario que se enviarían por POST
                $formData = [
                    'name' => $response_json['first_name'] ?? 'Sin nombre',
                    'lastname' => null,
                    'email' => $response_json['email'] ?? null,
                    'type_document' => 'CC',
                    'document_number' => $response_json['number_identification'] ?? null,
                    'phone' => $response_json['phone'] ?? null,
                    'city_id' => null,
                    'birth_date' => null,

                    'id_ticket' => $arrParams[0],
                    'guid' =>  $arrParams[1],
                    'seat_id' => null, // solo si ese ticket usa asientos
                    'courtesy_code' => null, // opcional

                    'guardian_id' => null,

                    // Array de menores si se van a registrar
                    /*'minors' => [
                        ['full_name' => 'Lucía López', 'age' => 8],
                        ['full_name' => 'Tomás López', 'age' => 5],
                    ],*/

                ];

                // Crear objeto Request como si viniera del navegador
                $request = Request::create(
                    "/event/register/$public_link", // URL fake (solo para contexto)
                    'POST',
                    $formData
                );

                // Invocar el service
                $service = new PublicRegistrationService();
                $responseRaw = $service->handle($request, $public_link, true); // true = respuesta JSON
                $response = json_decode(json_encode($responseRaw->getData()), true);
                
                file_put_contents(storage_path().'/logs/log_webhook.txt', "<- RESPONSE 222 ->" .json_encode($response). PHP_EOL, FILE_APPEND);
                /*$queryService->storeResponseAutoBot(
                    "Respuesta automática",
                    null,
                    "text",
                    "auto_text",
                    $sendEstructuraa
                );*/
                // ✅ Validar que la inscripción fue exitosa
                if (isset($response['success']) && $response['success'] === true && isset($response['data'])) {
                    $data = $response['data'];

                    // Extraer datos necesarios
                    $userName = $data['userName'] ?? 'Asistente';
                    $event = $data['event'];

                    $eventName = $event['name'] ?? 'Evento sin nombre';
                    $location = $event['address'] ?? 'Ubicación no disponible';

                    // Formatear fecha y hora (usa Carbon)
                    $fecha = \Carbon\Carbon::parse($event['event_date'])->locale('es')->isoFormat('dddd D [de] MMMM');
                    $hora = \Carbon\Carbon::parse($event['start_time'])->format('h:i A');
                    $dateTime = $fecha . ', ' . $hora;

                    // QR Code URL (ajusta según tus rutas reales)
                    $qrCodeUrl = url("/event/qrcode/" . $data['idEventAssistant']);

                    // ✅ Generar mensaje de confirmación
                    $responseText = $messageCustomNotTemplateService->getRegistrationConfirmationMessage(
                        $userName,
                        $eventName,
                        $location,
                        $dateTime,
                        $qrCodeUrl
                    );

                    // ✅ Enviar mensaje por WhatsApp (sin plantilla)
                    $responseTplArr = $messageService->sendMessageNotTemplate(
                        $this->__externalPhoneNumber,
                        $responseText,
                        "Inscripción confirmada",
                        false,
                        null
                    );
                }else{
                    
                }
                break;
            
            
        }
    }
}
