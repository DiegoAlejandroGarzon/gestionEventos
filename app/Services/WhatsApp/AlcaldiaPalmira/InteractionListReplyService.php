<?php

namespace App\Services\WhatsApp\AlcaldiaPalmira;

use App\Services\WhatsApp\MessageService;
use App\Services\WhatsApp\QueryService;
use App\Services\WhatsApp\AlcaldiaPalmira\MessageCustomNotTemplateService;
use App\Services\EventService;
use App\Services\WhatsApp\AlcaldiaPalmira\MenuCustomService;
use Illuminate\Support\Str;

class InteractionListReplyService
{
    private $__externalPhoneNumber;
    private $__numberWhatssAppId;
    private $__messageCustomNotTemplateService;
    
    public function __construct($__externalPhoneNumber, $__numberWhatssAppId) {
        $this->__externalPhoneNumber = $__externalPhoneNumber;
        $this->__numberWhatssAppId = $__numberWhatssAppId;
        $this->__messageCustomNotTemplateService = new MessageCustomNotTemplateService();
    }
    
    public function verified($list_reply, $message_whatsapp_id, $timestamp, $type_closed=null){
        
        // ingresamos la respuesta del usuario en BD y enviamos pusher
        $queryService = new QueryService($this->__externalPhoneNumber, $this->__numberWhatssAppId);
        $queryService->storeResponseAutoUser(
            $list_reply["title"], 
            $message_whatsapp_id, 
            "response_text_list_reply",
            $list_reply["id"],
            $timestamp
        );
        
        $messageCustomNotTemplateService = new MessageCustomNotTemplateService();
        // enviamos la respuesta al cliente
        $messageService = new MessageService($this->__externalPhoneNumber, $this->__numberWhatssAppId);
        
        switch($list_reply['id']){
            
            // reserva pesebre mas grande
            case "reservar_boletas_funcionarios":
                // consultamos los aforos de los 7 dias actuales
                $eventService = new EventService();
                $arrDataDaysFrees = $eventService->getAvailableDaysOnly(5, 3, "2025-11-28");
                $menuCustomService = new MenuCustomService($this->__externalPhoneNumber, $this->__numberWhatssAppId);
                $sendEstructuraa = $menuCustomService->sendMenu_selectDia($arrDataDaysFrees);
                
                $queryService->storeResponseAutoBot(
                    "Respuesta automática",
                    null,
                    "text",
                    "auto_text",
                    $sendEstructuraa
                );
                break;
            
            // reserva pesebre mas grande
            case "reservar_boletas":
                // consultamos los aforos de los 7 dias actuales
                $eventService = new EventService();
                $arrDataDaysFrees = $eventService->getAvailableDaysOnly(5, 3, "2025-12-01");
                $menuCustomService = new MenuCustomService($this->__externalPhoneNumber, $this->__numberWhatssAppId);
                $sendEstructuraa = $menuCustomService->sendMenu_selectDia($arrDataDaysFrees);
                
                $queryService->storeResponseAutoBot(
                    "Respuesta automática",
                    null,
                    "text",
                    "auto_text",
                    $sendEstructuraa
                );
                break;
            
            // reserva pesebre mas grande
            case "alcapalmira_register_panafest":
                
                $eventService = new EventService();
                $availabilityData = $eventService->getAvailableDaysHoursOne(4, 435);

                $fechaFormateada = \Carbon\Carbon::parse($availabilityData->entry_date)->translatedFormat('l d \d\e M');
                $guid = (string) Str::uuid();
                
                $hourStart = \Carbon\Carbon::parse($availabilityData->entry_start_time)->format('h:i A');
                $hourEnd = \Carbon\Carbon::parse($availabilityData->entry_end_time)->format('h:i A');

                // enviamos FLOW
                // Parámetros para la plantilla
                $templateParams = [
                    ['type' => 'text', 'text' => $fechaFormateada],
                    ['type' => 'text', 'text' => $hourStart." a ".$hourEnd],
                    ['type' => 'text', 'text' => (435)."$".$guid."$".(4)] // ID TICKET // GUID // EVENT_ID
                ];
                $arrResponse = $messageService->sendMessage(
                    $this->__externalPhoneNumber, 
                    "alcapalmira_register_panafest", 
                    $templateParams, 
                    true
                );
                $whatsId = $arrResponse->messageId;
                // ingresamos la auto respuesta
                $queryService->storeResponseAutoBot(
                    "Respuesta automatica", 
                    $whatsId, 
                    "text_flow",
                    "auto_text", 
                    null,
                    "alcapalmira_register_panafest",
                    $templateParams // mejorar esto para q inserte los params en bd
                );
                break;
            
            case "informacion_evento":
                $responseText = "ℹ️ *Otra Información sobre Palmira*\n\n";
                $responseText .= "📘 En el sitio web oficial del municipio podrás encontrar más detalles sobre eventos, noticias, trámites y servicios disponibles para la comunidad.\n\n";
                $responseText .= "🌐 Visita: https://palmira.gov.co/\n\n";
                $responseText .= "✅ Mantente informado sobre todo lo que sucede en Palmira y participa en las actividades que tenemos para ti.";

                $responseTplArr = $messageService->sendMessageNotTemplate($this->__externalPhoneNumber, $responseText, $list_reply["title"], false, null);
                $queryService->storeResponseAutoBot(
                    "Respuesta automática",
                    null,
                    "text",
                    "auto_text",
                    $responseTplArr
                );

                break;
            
            // preguntas
            case "preguntas_frecuentes":
                $responseText = "❓ *Preguntas Frecuentes – El Pesebre Más Grande del Mundo 2025*\n\n";
                $responseText .= "🔸 *¿La entrada tiene costo?*\n";
                $responseText .= "No, la entrada es gratuita pero debes reservar tus boletas previamente desde el menú principal.\n\n";

                $responseText .= "🔸 *¿Dónde se realiza el evento?*\n";
                $responseText .= "En el *Bosque Municipal de Palmira*. Puedes ver la ubicación aquí:\n";
                $responseText .= "📍 https://maps.app.goo.gl/oqFJ21xZWmnkTDGz7";

                $responseText .= "\n\n🔸 *¿Puedo asistir con mi familia?*\n";
                $responseText .= "Sí, el evento está diseñado para todas las edades. Es un espacio familiar y seguro.\n\n";

                $responseText .= "🔸 *¿Qué debo llevar?*\n";
                $responseText .= "Debes presentar tu *documento de identidad* y la *reserva enviada por WhatsApp* (boleta digital o impresa). También te recomendamos asistir con ropa cómoda.\n\n";

                $responseText .= "🧑‍🎄 *¿Tienes más dudas?*\n";
                $responseText .= "Escribe *MENU* para volver al inicio y explorar otras opciones.";

                $responseTplArr = $messageService->sendMessageNotTemplate($this->__externalPhoneNumber, $responseText, $list_reply["title"], false, null);
                $queryService->storeResponseAutoBot(
                    "Respuesta automática",
                    null,
                    "text",
                    "auto_text",
                    $responseTplArr
                );

                break;
            
            default:
                if (str_starts_with($list_reply['id'], 'seleccion_dia_')) {
                    $fechaSeleccionada = str_replace('seleccion_dia_', '', $list_reply['id']);
                    $arrDays = explode("|", $fechaSeleccionada); // [0] eventID
                    $fechaSeleccionada = $arrDays[1];

                    $eventService = new EventService();
                    
                    if($arrDays[0] == 2 || $arrDays[0] == 5){
                        $availabilityData = $eventService->getDaysAndTimesFrees($arrDays[1], $arrDays[0]);
                    }
                    elseif($arrDays[0] == 4){
                        $availabilityData = $eventService->getDaysAndTimesFreesPanaFest($arrDays[1]);
                    }

                    if (!empty($availabilityData)) {
                        $menuCustomService = new MenuCustomService($this->__externalPhoneNumber, $this->__numberWhatssAppId);
                        $sendEstructuraa = $menuCustomService->sendMenu_selectHorario($availabilityData, $arrDays[1], $arrDays[0]);
                        $responseText = "Respuesta automática";
                    } else {
                        $sendEstructuraa = [];
                        $responseText = "🚫 No se encontraron horarios disponibles para el día seleccionado.";
                        $messageService = new MessageService($this->__externalPhoneNumber, $this->__numberWhatssAppId);
                        $responseTplArr = $messageService->sendMessageNotTemplate($this->__externalPhoneNumber, $responseText, $list_reply["title"], false, null);

                        $queryService = new QueryService($this->__externalPhoneNumber, $this->__numberWhatssAppId);
                        $queryService->storeResponseAutoBot("Respuesta automática", null, "text", "auto_text", $responseTplArr);
                    }
                    
                    $queryService->storeResponseAutoBot(
                        $responseText,
                        null,
                        "text",
                        "auto_text",
                        $sendEstructuraa
                    );

                }
                elseif (str_starts_with($list_reply['id'], 'seleccion_horario_')) {
                    $fechaSeleccionada = str_replace('seleccion_horario_', '', $list_reply['id']);
                    $fechaSeleccionada = explode("$", $fechaSeleccionada);
                    
                    $ticketIdSelected = $fechaSeleccionada[1];
                    $fechaSeleccionada = $fechaSeleccionada[0];
                    
                    $ticketIdSelected = explode("|", $ticketIdSelected);
                    file_put_contents(storage_path().'/logs/log_webhook.txt', "<- EXPLODE ->" .json_encode($ticketIdSelected). PHP_EOL, FILE_APPEND);
                    $hourStart = $ticketIdSelected[0];
                    $hourEnd = $ticketIdSelected[1];
                    $hourStart = \Carbon\Carbon::parse($hourStart)->format('h:i A');
                    $hourEnd = \Carbon\Carbon::parse($hourEnd)->format('h:i A');
                    
                    $eventId = $ticketIdSelected[3];
                    $ticketIdSelected = $ticketIdSelected[2];
                    

                    $eventService = new EventService();
                    $availabilityData = $eventService->getDaysAndTimesFrees($fechaSeleccionada);

                    $fechaFormateada = \Carbon\Carbon::parse($fechaSeleccionada)->translatedFormat('l d \d\e M');
                    $guid = (string) Str::uuid();
                    
                    // enviamos FLOW PESEBRE
                    if($eventId == 2){
                        // Parámetros para la plantilla
                        $templateParams = [
                            ['type' => 'text', 'text' => $fechaFormateada],
                            ['type' => 'text', 'text' => $hourStart." a ".$hourEnd],
                            ['type' => 'text', 'text' => $ticketIdSelected."$".$guid."$".$eventId]
                        ];
                        $arrResponse = $messageService->sendMessage(
                                $this->__externalPhoneNumber, 
                                "alcapalmira_register_visite", 
                                $templateParams, 
                                true
                        );
                        $whatsId = $arrResponse->messageId;
                        // ingresamos la auto respuesta
                        $queryService->storeResponseAutoBot(
                            "Respuesta automatica", 
                            $whatsId, 
                            "text_flow",
                            "auto_text", 
                            null,
                            "alcapalmira_register_visite",
                            $templateParams // mejorar esto para q inserte los params en bd
                        );
                    }

                }
                break;

        }
    }
}
