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
                    'seat_id' => null,
                    'courtesy_code' => null,
                    'guardian_id' => null,
                ];
                
                // minors
                $minors = [];
                $index = 1;
                while (isset($response_json["name_menor_{$index}"]) && !empty($response_json["name_menor_{$index}"])) {
                    $nombre = trim($response_json["name_menor_{$index}"]);
                    $edad = $response_json["edad_menor_{$index}"] ?? null;
                    if (!empty($nombre) && !empty($edad)) {
                        $minors[] = [
                            'full_name' => $nombre,
                            'age' => (int) $edad,
                        ];
                    }
                    $index++;
                }
                if (!empty($minors)) {
                    $formData['minors'] = $minors;
                }
                
                // Crear objeto Request como si viniera del navegador
                $request = Request::create("/event/register/$public_link",'POST',$formData);

                // Invocar el service
                $service = new PublicRegistrationService();
                $responseRaw = $service->handle($request, $public_link, true); // true = respuesta JSON
                $response = json_decode(json_encode($responseRaw->getData()), true);
                
                file_put_contents(storage_path().'/logs/log_webhook.txt', "<- RESPONSE 222 ->" .json_encode($response). PHP_EOL, FILE_APPEND);
                
                /// Acompañante
                if (!empty($response_json['radAcompanante']) && $response_json['radAcompanante'] == 'Si') {
                    $acompFormData = [
                        'name' => trim($response_json['first_name_acomp']),
                        'lastname' => null,
                        'email' => $response_json['email_acomp'] ?? null,
                        'type_document' => 'CC',
                        'document_number' => $response_json['number_identification_acomp'] ?? null,
                        'phone' => $response_json['phone_acomp'] ?? null,
                        'city_id' => null,
                        'birth_date' => null,

                        'id_ticket' => $arrParams[0],
                        'guid' => $arrParams[1],
                        'guardian_id' => null
                    ];

                    $acompRequest = Request::create("/event/register/$public_link", 'POST', $acompFormData);
                    $acompResponseRaw = $service->handle($acompRequest, $public_link, true);
                    $acompResponse = json_decode(json_encode($acompResponseRaw->getData()), true);

                    file_put_contents(
                        storage_path().'/logs/log_webhook.txt',
                        "<- ACOMPAÑANTE ->" . json_encode($acompFormData) . PHP_EOL .
                        "<- RESPONSE ACOMPAÑANTE ->" . json_encode($acompResponse) . PHP_EOL,
                        FILE_APPEND
                    );
                }
                
                // ✅ Validar que la inscripción fue exitosa
                if (isset($response['success']) && $response['success'] === true && isset($response['data'])) {
                    $data = $response['data'];

                    // Extraer datos necesarios
                    $userName = $data['userName'] ?? 'Asistente';
                    $event = $data['event'];
                    $ticketType = $data['ticketType'];
                    $numberIdentification = $data['numberIdentification'];
                    $assistantGuid = $ticketType["id"]."$".$data['assistantGuid'];

                    $eventName = $event['name'] ?? 'Evento sin nombre';
                    $location = $event['address'] ?? 'Ubicación no disponible';

                    // Formatear fecha y hora (usa Carbon)
                    $fecha = \Carbon\Carbon::parse($ticketType['entry_date'])->locale('es')->isoFormat('dddd D [de] MMMM');
                    $hora = \Carbon\Carbon::parse($ticketType['entry_start_time'])->format('h:i A');
                    $hora_fin = \Carbon\Carbon::parse($ticketType['entry_end_time'])->format('h:i A');
                    $dateTime = $fecha . ', ' . $hora." a ".$hora_fin;

                    // QR Code URL (ajusta según tus rutas reales)
                    $qrCodeUrl = url("/event/qrcode/" . $data['idEventAssistant']);

                    // ✅ Generar mensaje de confirmación
                    $responseText = $messageCustomNotTemplateService->getRegistrationConfirmationMessage(
                        $userName,
                        $eventName,
                        $location,
                        $dateTime,
                        $numberIdentification,
                        $assistantGuid
                    );

                    // ✅ Enviar mensaje por WhatsApp (sin plantilla)
                    $responseTplArr = $messageService->sendMessageNotTemplate($this->__externalPhoneNumber,$responseText,"Inscripción confirmada",false,null);
                    // ingresamos la auto respuesta
                    $queryService->storeResponseAutoBot("Respuesta automática",null,"text","auto_text",$responseTplArr);
                }else{
                    $fecha = $paramsTemplate[0]['text'];
                    $hora = $paramsTemplate[1]['text'];
                    $cedula = $response_json['number_identification'];
                    $guid = $paramsTemplate[2]['text'];
                    switch($response["message"]){
                        case "El usuario ya está inscrito en este evento.":
                            // ✅ Generar mensaje de confirmación
                            $responseText = $messageCustomNotTemplateService->getAlreadyRegisteredMessage($cedula,$fecha,$hora,$guid);

                            // ✅ Enviar mensaje por WhatsApp (sin plantilla)
                            $responseTplArr = $messageService->sendMessageNotTemplate($this->__externalPhoneNumber,$responseText,"Inscripción confirmada",false,null);
                            // ingresamos la auto respuesta
                            $queryService->storeResponseAutoBot("Respuesta automática",null,"text","auto_text",$responseTplArr);
                            break;
                    }
                }
                break;
            
            
            // reserva
            case "register_boletas_panafest":
                $public_link = "901844a8-7fe6-4523-8281-1340d2c53a0c";
                $formData = [
                    'name' => $response_json['first_name'] ?? 'Sin nombre',
                    'lastname' => null,
                    'email' => null,
                    'type_document' => 'CC',
                    'document_number' => $response_json['number_identification'] ?? null,
                    'phone' => $response_json['phone'] ?? null,
                    'city_id' => null,
                    'birth_date' => null,

                    'id_ticket' => $arrParams[0],
                    'guid' =>  $arrParams[1],
                    'seat_id' => null,
                    'courtesy_code' => null,
                    'guardian_id' => null,
                ];
                
                // minors
                $minors = [];
                
                // Crear objeto Request como si viniera del navegador
                $request = Request::create("/event/register/$public_link",'POST',$formData);

                // Invocar el service
                $service = new PublicRegistrationService();
                $responseRaw = $service->handle($request, $public_link, true); // true = respuesta JSON
                $response = json_decode(json_encode($responseRaw->getData()), true);
                
                file_put_contents(storage_path().'/logs/log_webhook.txt', "<- RESPONSE PANAFEST ->" .json_encode($response). PHP_EOL, FILE_APPEND);
                
                
                // ✅ Validar que la inscripción fue exitosa
                if (isset($response['success']) && $response['success'] === true && isset($response['data'])) {
                    $data = $response['data'];

                    // Extraer datos necesarios
                    $userName = $data['userName'] ?? 'Asistente';
                    $event = $data['event'];
                    $ticketType = $data['ticketType'];
                    $numberIdentification = $data['numberIdentification'];
                    $assistantGuid = $ticketType["id"]."$".$data['assistantGuid'];

                    $eventName = $event['name'] ?? 'Evento sin nombre';
                    $location = $event['address'] ?? 'Ubicación no disponible';

                    // Formatear fecha y hora (usa Carbon)
                    $fecha = \Carbon\Carbon::parse($ticketType['entry_date'])->locale('es')->isoFormat('dddd D [de] MMMM');
                    $hora = \Carbon\Carbon::parse($ticketType['entry_start_time'])->format('h:i A');
                    $hora_fin = \Carbon\Carbon::parse($ticketType['entry_end_time'])->format('h:i A');
                    $dateTime = $fecha . ', ' . $hora." a ".$hora_fin;

                    // QR Code URL (ajusta según tus rutas reales)
                    $qrCodeUrl = url("/event/qrcode/" . $data['idEventAssistant']);

                    // ✅ Generar mensaje de confirmación
                    $responseText = $messageCustomNotTemplateService->getRegistrationConfirmationMessagePanaFest(
                        $userName,
                        $eventName,
                        $location,
                        $dateTime,
                        $numberIdentification,
                        $assistantGuid
                    );

                    // ✅ Enviar mensaje por WhatsApp (sin plantilla)
                    $responseTplArr = $messageService->sendMessageNotTemplate($this->__externalPhoneNumber,$responseText,"Inscripción confirmada",false,null);
                    // ingresamos la auto respuesta
                    $queryService->storeResponseAutoBot("Respuesta automática",null,"text","auto_text",$responseTplArr);
                }else{
                    $fecha = $paramsTemplate[0]['text'];
                    $hora = $paramsTemplate[1]['text'];
                    $cedula = $response_json['number_identification'];
                    $guid = $paramsTemplate[2]['text'];
                    switch($response["message"]){
                        case "El usuario ya está inscrito en este evento.":
                            // ✅ Generar mensaje de confirmación
                            $responseText = $messageCustomNotTemplateService->getAlreadyRegisteredMessage($cedula,$fecha,$hora,$guid);

                            // ✅ Enviar mensaje por WhatsApp (sin plantilla)
                            $responseTplArr = $messageService->sendMessageNotTemplate($this->__externalPhoneNumber,$responseText,"Inscripción confirmada",false,null);
                            // ingresamos la auto respuesta
                            $queryService->storeResponseAutoBot("Respuesta automática",null,"text","auto_text",$responseTplArr);
                            break;
                    }
                }
                break;
            
            
        }
    }
}
