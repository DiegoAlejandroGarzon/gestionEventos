<?php

namespace App\Services\WhatsApp;

use App\Services\WhatsApp\NuevaGeneracion\InteractionService;
use App\Services\WhatsApp\QueryService;
use App\Services\ConversationService;
use App\Services\CustomersService;
use App\Services\WhatsApp\MessageService;

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
             return true;
            $message_whatsapp_id = $objectBody['entry'][0]['changes'][0]['value']['messages'][0]['id'];
            
            // validamos el ID del mensjae de whatsapp
            $conversationService = new ConversationService();
            //if(count($conversationService->validateMessageConversationIdWhat($message_whatsapp_id)) > 0){
             //   return true;
            //}
            
            $message = $objectBody['entry'][0]['changes'][0]['value']['messages'][0];
            $this->__externalPhoneNumber = $objectBody['entry'][0]['changes'][0]['value']['messages'][0]['from'];
            $context = ($objectBody['entry'][0]['changes'][0]['value']['messages'][0]['context'] ?? null);
            $profileName = ($objectBody['entry'][0]['changes'][0]['value']['contacts'][0]['profile']['name'] ?? $this->__externalPhoneNumber);
            $timestamp = $objectBody['entry'][0]['changes'][0]['value']['messages'][0]['timestamp'];
            
            switch($objectBody['entry'][0]['changes'][0]['value']['metadata']['phone_number_id']){
                
                // Alcaldia Palmira
                case "855752667617564":
                    $queryService = new QueryService($this->__externalPhoneNumber, "599165333274370");
                    // verificamos si el customer tiene sesion activa
                    $customersService = new CustomersService();
                    $objecCustomerIncome = $customersService->isSessionActive($this->__externalPhoneNumber, "599165333274370");
                    
                    // SESSION INACTIVA
                    if($objecCustomerIncome->session == null){
                        $interactionService = new InteractionService($this->__externalPhoneNumber, "599165333274370");
                        // verificamos si la session fue cerrada por inactividad
                        if($objecCustomerIncome->closed !== null){
                            $type_closed = "session_closed";
                            file_put_contents(storage_path().'/logs/log_webhook.txt', "<- closed ->" .($type_closed). PHP_EOL, FILE_APPEND);
                        }
                        switch($message['type']){
                            case "interactive":
                                $interactionService->actionInteractive($message, $message_whatsapp_id, $timestamp, ($type_closed??null));
                                break;
                            case "text":
                                // ingresamos el texto
                                $queryService->storeText($message['text']["body"], $message_whatsapp_id, $timestamp);
                                
                                if(isset($type_closed)){
                                    $interactionService->setMsgClosedSession();
                                }
                                
                                // verificamos
                                $interactionService->verifiedExist();
                                break;
                            
                        }
                    }else{
                        // actualizamos la session
                        $customersService->updateSession($this->__externalPhoneNumber, "599165333274370");
                        
                        // sesion activa
                        $interactionLoggedService = new InteractionLoggedService($this->__externalPhoneNumber, "599165333274370");
                        switch($message['type']){
                            case "interactive":
                                $interactionLoggedService->actionInteractive($message, $message_whatsapp_id, $timestamp);
                                break;
                            case "text":
                                // ingresamos el texto
                                $queryService->storeText($message['text']["body"], $message_whatsapp_id, $timestamp);
                                // verificamos
                                $interactionLoggedService->receivedText($message['text']["body"], $objecCustomerIncome->session);
                                break;
                        }
                    }
                    break;
                
            }
        }
    }
}
