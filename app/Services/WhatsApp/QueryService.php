<?php

namespace App\Services\WhatsApp;

use App\Models\Conversations;
use App\Models\ConversationsMessages;
use App\Models\NumbersWhatsappSysUser;
use App\Services\ConversationService;
use Carbon\Carbon;
use App\Services\WhatsApp\CurlService;

class QueryService
{
    private $__externalPhoneNumber;
    private $__numberWhatssAppId;
    public function __construct($__externalPhoneNumber, $__numberWhatssAppId) {
        $this->__externalPhoneNumber = $__externalPhoneNumber;
        $this->__numberWhatssAppId = $__numberWhatssAppId;
    }
    
    public function storeText($text, $msgWhatId, $timestamp, $profileName=null) {
        
        $conversation = $this->validateExistConversation($text, $profileName);
        if ($conversation == null) {
            return null;
        }

        try {
            $conversationsMessages = ConversationsMessages::create([
                'users_id' => $conversation->users_id,
                'conversations_id' => $conversation->id,
                'content' => $text,
                'direction' => 'received',
                'message_what_id' => $msgWhatId,
                'type' => 'text',
                'origin' => 'user',
                'received_at' => Carbon::createFromTimestamp($timestamp)->toDateTimeString(),
            ]);

            return $conversationsMessages;

        } catch (\Illuminate\Database\QueryException $e) {
            file_put_contents(storage_path().'/logs/log_webhook.txt', "<- EXCEPION storeText ->" .json_encode([]). PHP_EOL, FILE_APPEND);
            // Si hay un error de duplicado (unique constraint), devolvemos null
            if ($e->getCode() === '23000') { // Código SQL de violación de UNIQUE
                return null;
            }
            // Re-lanzar cualquier otra excepción
            throw $e;
        }
    }
    
    public function storeResponseAutoBot($text, $msgWhatId, $type_response, $type_origin_bot, 
        $array_dinamic, $name_tpl_or_flow=null, $templateParams=null) {
        
        $conversation = $this->validateExistConversation($text);
        if($conversation != null){
            
            // creamos el message
            $conversationsMessages = new ConversationsMessages();
            $conversationsMessages->users_id = $conversation->users_id;
            $conversationsMessages->conversations_id = $conversation->id;        
            $conversationsMessages->content = $text;
            $conversationsMessages->content_bot = is_array($array_dinamic)
                ? json_encode($array_dinamic, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
                : null;
            $conversationsMessages->content_response = $name_tpl_or_flow; // name tpl_or_flow usado o opcion seleccionada
            $conversationsMessages->direction = "sent";
            $conversationsMessages->message_what_id = $msgWhatId;
            $conversationsMessages->type = $type_response;
            $conversationsMessages->origin = "bot";
            $conversationsMessages->origin_bot_type = $type_origin_bot;
            $conversationsMessages->received_at = now();
            $conversationsMessages->params_template = is_array($templateParams)
                ? json_encode($templateParams, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
                : null;
            $conversationsMessages->save();
            
            return $conversationsMessages;
        }else{
            return null;
        }
    }
    
    public function storeResponseAutoUser($text, $msgWhatId, $type_response, $response_text, $timestamp) {
        $conversation = $this->validateExistConversation($text);
        if ($conversation == null) {
            return null;
        }

        try {
            $conversationsMessages = ConversationsMessages::create([
                'users_id' => $conversation->users_id,
                'conversations_id' => $conversation->id,
                'content' => $text,
                'content_response' => $response_text,
                'direction' => 'received',
                'message_what_id' => $msgWhatId,
                'type' => $type_response,
                'origin' => 'user',
                'received_at' => Carbon::createFromTimestamp($timestamp)->toDateTimeString(),
            ]);
            
            return $conversationsMessages;
            
        } catch (\Illuminate\Database\QueryException $e) {
            file_put_contents(storage_path().'/logs/log_webhook.txt', "<- EXCEPION storeResponseAutoUser ->" .json_encode([]). PHP_EOL, FILE_APPEND);
            // Si hay un error de duplicado (unique constraint), devolvemos null
            if ($e->getCode() === '23000') { // Código SQL de violación de UNIQUE
                return null;
            }
            // Re-lanzar cualquier otra excepción
            throw $e;
        }
    }
    
    public function storeResponseFileAutoUser($text, $msgWhatId, $type_response, $response_text, $timestamp, $mediaId, $type_file=null) {
        
        $conversation = $this->validateExistConversation($text);
        if($conversation != null){
            
            // creamos el message
            $conversationsMessages = new ConversationsMessages();
            $conversationsMessages->sys_users_id = $conversation->sys_users_id;
            $conversationsMessages->conversations_id = $conversation->id;        
            $conversationsMessages->content = $text;
            $conversationsMessages->content_response = $response_text; // id de la opcion seleccionada
            $conversationsMessages->direction = "received";
            $conversationsMessages->message_what_id = $msgWhatId;
            $conversationsMessages->type = $type_response;
            $conversationsMessages->origin = "user";
            $conversationsMessages->received_at = Carbon::createFromTimestamp($timestamp)->toDateTimeString();
            $conversationsMessages->save();
            
            // obtenemos la url de la imagen
            $curlService = new CurlService($this->__numberWhatssAppId);
            $dataFile = $curlService->curlFacebookApiFile($mediaId);
            
            // descargamos el archivo
            $directorio = storage_path('app/whatsapp/messages/' . $conversation->id ."/". $conversationsMessages->id);
            if (!file_exists($directorio)) {
                mkdir($directorio, 0777, true); // Crear directorio si no existe
            }
            $archivoDestino = $directorio . '/' . $msgWhatId . '.' . $type_file;
            $descargado = $curlService->curlFacebookApiDownloadFile($dataFile["url"], $archivoDestino);
            
            if ($descargado) {
                $conversationsMessages->url_file = $msgWhatId . '.' . $type_file;
                $conversationsMessages->save(); // Guarda de nuevo con la ruta del archivo
            }
            
            // trigger pusher
            // aacamos la conversacion izquierda afectada
            $conversationService = new ConversationService();
            $conversationService->setTriggerPusher($conversationsMessages);
            
            return $conversationsMessages;
        }else{
            return null;
        }
    }
    
    private function validateExistConversation($textMessage, $nameFrom=null){
        // consultamos el id de whatsapp
        $numbersWhatsappSysUser = new NumbersWhatsappSysUser();
        $dataResponse = $numbersWhatsappSysUser->where("phone_number_id", $this->__numberWhatssAppId)->get();
        
        if(count($dataResponse)>0){
            $conversation = Conversations::firstOrCreate(
                ['external_phone_number' => $this->__externalPhoneNumber, 'number_whatsapp_sysuser_id' => $dataResponse[0]->id], // condición de búsqueda
                [
                    'users_id' => $dataResponse[0]->sys_users_id,
                    'number_whatsapp_sysuser_id' => $dataResponse[0]->id,
                    'external_phone_number' => $this->__externalPhoneNumber,
                    'started_at' => now(),
                    'last_message_at' => now(),
                    'last_message_truncated' => strlen($textMessage) > 15 
                            ? substr($textMessage, 0, 15) . '...' 
                            : $textMessage
                ]
            );
        

            // Ahora puedes actualizar cualquier campo adicional
            $conversation->last_message_at = now();
            $conversation->last_message_truncated = strlen($textMessage) > 15 
                ? substr($textMessage, 0, 15) . '...' 
                : $textMessage;
            $conversation->save();
            return $conversation;
        }else{
            return null;
        }
    }
    
    public function getSafeExtensionFromMime($mime)
    {
        $allowedMap = [
            'application/pdf' => 'pdf',
            'application/msword' => 'doc',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'docx',
            'application/vnd.ms-excel' => 'xls',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' => 'xlsx',
            'application/vnd.ms-powerpoint' => 'ppt',
            'application/vnd.openxmlformats-officedocument.presentationml.presentation' => 'pptx',
            'text/csv' => 'csv',
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/gif' => 'gif',
            'text/plain' => 'txt',
            'application/zip' => 'zip',
            // puedes agregar más tipos si los necesitas
        ];

        return $allowedMap[$mime] ?? null; // null si no es permitido
    }
    
    public function verifyRespuestaCustomerDataCurlExtra($input) {
        $customersService = new CustomersService();
        $session = $customersService->getSession($this->__externalPhoneNumber, $this->__numberWhatssAppId);

        $input = mb_strtoupper(trim($input));

        // Suponemos que data_curl_extra tiene la estructura ['response_customer' => ['SI', 'NO']]
        $response_customer = $session->session->data_curl_extra['response_customer'] ?? [];

        // Normalizamos los valores del array
        foreach ($response_customer as $value) {
            $normalized = mb_strtoupper(trim($value));
            if ($input === $normalized) {
                return (object)[
                    'match' => true,
                    'value' => $value // Se devuelve el valor original del array que coincide
                ];
            }
        }

        // No hubo coincidencia
        return (object)[
            'match' => false,
            'value' => null
        ];
    }

}
