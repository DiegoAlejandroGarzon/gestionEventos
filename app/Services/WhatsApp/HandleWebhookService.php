<?php

namespace App\Services\WhatsApp;

use App\Services\WhatsApp\AlcaldiaPalmira\InteractionService;
use App\Services\WhatsApp\QueryService;
use App\Services\ConversationService;

class HandleWebhookService
{
    private $__externalPhoneNumber; 
    
    public function init($objectBody) {
        file_put_contents(storage_path().'/logs/log_webhook.txt', "<- INIT ->" .json_encode($objectBody). PHP_EOL, FILE_APPEND);
        if (isset($objectBody['entry'][0]['changes'][0]['value']['metadata'])){
            
            if (!isset($objectBody['entry'][0]['changes'][0]['value']['messages'])){
                file_put_contents(storage_path().'/logs/log_webhook.txt', "<- No messages found ->" .json_encode($objectBody). PHP_EOL, FILE_APPEND);
                return true;
            }
             //return true;
            $message_whatsapp_id = $objectBody['entry'][0]['changes'][0]['value']['messages'][0]['id'];
            
            // validamos el ID del mensjae de whatsapp
            $conversationService = new ConversationService();
            if(count($conversationService->validateMessageConversationIdWhat($message_whatsapp_id)) > 0){
                return true;
            }
            
            $message = $objectBody['entry'][0]['changes'][0]['value']['messages'][0];
            $this->__externalPhoneNumber = $objectBody['entry'][0]['changes'][0]['value']['messages'][0]['from'];
            $context = ($objectBody['entry'][0]['changes'][0]['value']['messages'][0]['context'] ?? null);
            $profileName = ($objectBody['entry'][0]['changes'][0]['value']['contacts'][0]['profile']['name'] ?? $this->__externalPhoneNumber);
            $timestamp = $objectBody['entry'][0]['changes'][0]['value']['messages'][0]['timestamp'];
            
            // Verificar si el mensaje es muy antiguo (más de 5 minutos)
            if (time() - (int)$timestamp > 300) { // 300 segundos = 5 minutos
                file_put_contents(storage_path() . '/logs/log_webhook.txt', "<- Mensaje antiguo ignorado -> ID: $message_whatsapp_id, Timestamp: $timestamp" . PHP_EOL, FILE_APPEND);
                return true;
            }
            
            switch($objectBody['entry'][0]['changes'][0]['value']['metadata']['phone_number_id']){
                
                // Alcaldia Palmira
                case "855752667617564":
                    $queryService = new QueryService($this->__externalPhoneNumber, "855752667617564");
                    
                    // SESSION INACTIVA
                    $interactionService = new InteractionService($this->__externalPhoneNumber, "855752667617564");
                    switch($message['type']){
                        case "interactive":
                            $interactionService->actionInteractive($message, $message_whatsapp_id, $timestamp, ($type_closed??null));
                            break;
                        case "text":
                            // ingresamos el texto
                            $queryService->storeText($message['text']["body"], $message_whatsapp_id, $timestamp);

                            // verificamos
                            $interactionService->verifiedExist();
                            break;

                    }
                    break;
                
            }
        }
    }
}
