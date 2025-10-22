<?php

namespace App\Services\WhatsApp\AlcaldiaPalmira;

use App\Services\WhatsApp\AlcaldiaPalmira\MenuCustomService;
use App\Services\WhatsApp\AlcaldiaPalmira\InteractionListReplyService;
use App\Services\WhatsApp\AlcaldiaPalmira\InteractionNfmReplyService;
use App\Services\WhatsApp\QueryService;

class InteractionService
{
    private $__externalPhoneNumber;
    private $__numberWhatssAppId;
    private $__messageCustomNotTemplateService;
    
    public function __construct($__externalPhoneNumber, $__numberWhatssAppId) {
        $this->__externalPhoneNumber = $__externalPhoneNumber;
        $this->__numberWhatssAppId = $__numberWhatssAppId;
    }
    
    public function actionInteractive($message, $message_whatsapp_id, $timestamp, $type_closed=null, $context=null) {
        switch ($message['interactive']['type']) {
            case "nfm_reply":
                $interactionNfmReplyService = new InteractionNfmReplyService($this->__externalPhoneNumber, $this->__numberWhatssAppId);
                $interactionNfmReplyService->verified($message['interactive']['nfm_reply'], $message_whatsapp_id, $timestamp, $type_closed, $context);
                break;
            case "button_reply":
                break;
            case "list_reply":
                $interactionListReplyService = new InteractionListReplyService($this->__externalPhoneNumber, $this->__numberWhatssAppId);
                $interactionListReplyService->verified($message['interactive']['list_reply'], $message_whatsapp_id, $timestamp, $type_closed);
                break;
        }
    }
    
    public function verifiedExist(){
        // enviamos menu principal
        $menuCustomService = new MenuCustomService($this->__externalPhoneNumber, $this->__numberWhatssAppId);
        $menu_list = $menuCustomService->sendMenu_initial(array());

        // ingresamos la auto respuesta
        $queryService = new QueryService($this->__externalPhoneNumber, $this->__numberWhatssAppId);
        $queryService->storeResponseAutoBot(
            "Saludo y menú principal", 
            null, 
            "text_menu",
            "auto_greeting_menu", 
            $menu_list
        );
     
    }
}
