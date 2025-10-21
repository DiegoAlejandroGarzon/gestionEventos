<?php

namespace App\Services\WhatsApp\AlcaldiaPalmira;

use App\Services\WhatsApp\MessageService;
use App\Services\WhatsApp\QueryService;
use App\Services\WhatsApp\AlcaldiaPalmira\MessageCustomNotTemplateService;
use App\Services\EventService;
use App\Services\WhatsApp\AlcaldiaPalmira\MenuCustomService;

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
    
    public function verified($list_reply, $message_whatsapp_id, $timestamp, $type_closed=null){
        
        // ingresamos la respuesta del usuario en BD y enviamos pusher
        $queryService = new QueryService($this->__externalPhoneNumber, $this->__numberWhatssAppId);
        $queryService->storeResponseAutoUser(
            $list_reply["title"], 
            $message_whatsapp_id, 
            "response_text_bttn_replay",
            $list_reply["id"],
            $timestamp
        );
        
        $messageCustomNotTemplateService = new MessageCustomNotTemplateService();
        // enviamos la respuesta al cliente
        $messageService = new MessageService($this->__externalPhoneNumber, $this->__numberWhatssAppId);
        
        switch($list_reply['id']){
            
            // reserva
            case "reservar_boletas":
                // consultamos los aforos de los 3 dias actuales
                $eventService = new EventService();
                $arrDataDaysFrees = $eventService->getAvailableDaysOnly();
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
            
            case "informacion_evento":
                $responseText = "🎄 *El Pesebre Más Grande del Mundo – Palmira 2025*\n\n";
                $responseText .= "📍 *Ubicación:* Bosque Municipal, Palmira, Valle del Cauca.\n";
                $responseText .= "🔗 Ver en Google Maps: https://maps.app.goo.gl/oqFJ21xZWmnkTDGz7"; // <- reemplaza este enlace por el correcto
                $responseText .= "\n📅 *Fechas:* Del 1 al 31 de diciembre de 2025.\n";
                $responseText .= "🕐 *Horario:* Todos los días de 5:00 P.M. a 11:00 P.M.\n\n";
                $responseText .= "🎟️ *Entrada con boleta reservada previamente.*\n";
                $responseText .= "Puedes hacer la reserva desde el menú principal seleccionando *'Reservar boletas'*. \n\n";
                $responseText .= "🙌 ¡Te esperamos para vivir juntos la magia de la Navidad en Palmira!";

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

                    $eventService = new EventService();
                    $availabilityData = $eventService->getDaysAndTimesFrees($fechaSeleccionada);

                    if (!empty($availabilityData)) {
                        $menuCustomService = new MenuCustomService($this->__externalPhoneNumber, $this->__numberWhatssAppId);
                        $sendEstructuraa = $menuCustomService->sendMenu_selectHorario($availabilityData, $fechaSeleccionada);
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
                break;

        }
    }
}
