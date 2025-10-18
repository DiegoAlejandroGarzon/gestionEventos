<?php

namespace App\Services;

use App\SyModels\Conversations;
use App\SyModels\ConversationsMessages;
use Illuminate\Support\Facades\Auth;
use App\Services\WhatsApp\MessageService;
use App\Events\NewMessageWhatsAppReceived;
use App\SyModels\NumbersWhatsappSysUser;
use App\SyModels\ConversationsCustomer;

class ConversationService
{
    /**
     * Obtener los números de WhatsApp asignados al usuario en sesión.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getConversationsByUser($request, $userId, $message_whatsapp_id=null)
    {
        $query = Conversations::with('conversationCustomer')
                ->where('sys_users_id', $userId)
                ->where('number_whatsapp_sysuser_id', $request->whatsapp_number_id);

        if ($message_whatsapp_id) {
            // Solo la conversación relacionada al mensaje recibido
            $query->whereHas('conversationsMessages', function ($q) use ($message_whatsapp_id) {
                $q->where('message_what_id', $message_whatsapp_id);
            });
        }

        return $query->orderBy('last_message_at', 'desc')->get();
    }
    
    public function getConversationsMessageByUser($request, $userId, $convMsgId=null, $messageWhatId=null)
    {
        // Construir query base
        $query = ConversationsMessages::where('sys_users_id', $userId);

        // Si se pasa conversación ID, filtrar por ella
        if (isset($request->convoId) && !empty($request->convoId)) {
            $query->where('conversations_id', $request->convoId);
        }

        // Si se pasa un convMsgId, filtrar desde ese mensaje en adelante (o el mismo)
        if (!empty($convMsgId)) {
            $query->where('id', '=', $convMsgId);
        }
        
        // si se pasa un message_what_id
        if (!empty($messageWhatId)) {
            $query->where('message_what_id', '=', $messageWhatId);
        }

        // Obtener mensajes ordenados cronológicamente
        $messages = $query->orderBy('created_at', 'asc')->get();

        // Agrupar por fecha (Y-m-d)
        $groupedByDate = $messages->groupBy(function ($item) {
            return $item->created_at->format('Y-m-d'); // Agrupamos por fecha (sin la hora)
        });

        // Agrupar dentro de cada fecha por dirección (y respetar el orden cronológico)
        $grouped = [];

        foreach ($groupedByDate as $date => $messages) {
            $tempGroup = []; // Para almacenar los mensajes agrupados
            $currentUserGroup = []; // Grupo temporal para los mensajes del mismo usuario

            foreach ($messages as $message) {
                // Si no hay grupo actual o el mensaje tiene la misma dirección (enviado o recibido), agruparlo
                if (empty($currentUserGroup) || $currentUserGroup[0]->direction === $message->direction) {
                    $currentUserGroup[] = $message;
                } else {
                    // Si cambia la dirección (usuario diferente), almacenar el grupo anterior y empezar uno nuevo
                    $tempGroup[] = $currentUserGroup;
                    $currentUserGroup = [$message];
                }
            }

            // Agregar el último grupo de mensajes
            if (!empty($currentUserGroup)) {
                $tempGroup[] = $currentUserGroup;
            }

            // Guardar los mensajes agrupados por fecha y usuario
            $grouped[$date] = $tempGroup;
        }

        return $grouped;
    }

    public function createConversationFromApi($number_phone, $phone_number_id, $name_cliente){
        // Buscar un número de WhatsApp asociado al usuario
        $numberWhatsapp = NumbersWhatsappSysUser::where("phone_number_id", $phone_number_id)->first();

        if (!$numberWhatsapp) {
            // No hay un número de WhatsApp asignado al usuario
            return false;
        }

        $user = Auth::user();

        // Verificar si ya existe una conversación con ese número externo para el usuario
        $existingConversation = Conversations::where('external_phone_number', $number_phone)
            ->where('sys_users_id', $user->id)
            ->where("number_whatsapp_sysuser_id", $numberWhatsapp->id)
            ->first();

        if ($existingConversation) {
            // Ya existe una conversación con ese número
            return $existingConversation;
        }

        
        // Crear nueva conversación
        $conversation = new Conversations();
        $conversation->sys_users_id = $user->id;
        $conversation->created_by = $user->id;
        $conversation->updated_by = $user->id;
        $conversation->external_phone_number = $number_phone;
        $conversation->number_whatsapp_sysuser_id = $numberWhatsapp->id;
        $conversation->last_message_at = now();
        $conversation->save();
        
        // Crear registro en conversations_customer
        $this->createConversationCustomer($conversation, $name_cliente);

        return $conversation;
    }
    
    public function createConversationCustomer($conversation, $nameCustomer)
    {
        // Asumiendo que estás usando Auth
        $user = Auth::user();

        // Crear registro en conversations_customer
        $conversationCustomer = new ConversationsCustomer();
        $conversationCustomer->conversations_id = $conversation->id;
        $conversationCustomer->number_whatsapp_sysuser_id = $conversation->number_whatsapp_sysuser_id;
        $conversationCustomer->name = "~ ".$nameCustomer ?? null; // Puedes llenarlo si tienes datos
        $conversationCustomer->phone = $conversation->external_phone_number;
        $conversationCustomer->email = null;
        $conversationCustomer->created_by = $conversation->sys_users_id;
        $conversationCustomer->updated_by = $conversation->sys_users_id;
        $conversationCustomer->created_at = now();
        $conversationCustomer->updated_at = now();
        $conversationCustomer->save();

        return $conversationCustomer;
    }

    
    public function createMessage($request, $onlyCreate = false)
    {
        $conversation = Conversations::with('numberWhatsappSysuser') // carga la relación
            ->where('id', $request->conversation_id)
            ->first();

        if (!$conversation) {
            return false;
        }

        $message = new ConversationsMessages();
        $message->sys_users_id = $conversation->sys_users_id;
        $message->created_by = $conversation->sys_users_id;
        $message->conversations_id = $request->conversation_id;
        $message->content = $request->content;
        $message->direction = "sent";
        $message->message_what_id = $request->message_what_id ?? null;
        $message->type = "text";
        $message->origin = "admin";
        $message->received_at = now();

        $message->save();
        
        // actualizamos el registro principal de conversations
        $conversation->last_message_at = @date("Y-m-d H:i:s");
        $conversation->last_message_truncated = strlen($request->content) > 15 
            ? substr($request->content, 0, 15) . '...' 
            : $request->content;
        $conversation->updated_by = $conversation->sys_users_id;
        $conversation->save();
        
        if($onlyCreate === false){
            // enviamos el mensaje a whatsapp api
            $messageService = new MessageService($conversation->external_phone_number, $conversation->numberWhatsappSysuser->phone_number_id);
            $messageService->sendMessageNotTemplate(
                $conversation->external_phone_number, 
                $request->content, 
                null, 
                null, 
                null
            );
        }

        return true;
    }
    
    public function setTriggerPusher($conversationMessage){
        // sacamos los datos del numberWhatsppID
        $conversation = Conversations::find($conversationMessage->conversations_id);
        
        // aacamos la conversacion izquierda afectada
        $conversationIzq = $this->getConversationsByUser((object)array(
            "whatsapp_number_id" => $conversation->number_whatsapp_sysuser_id
        ), $conversationMessage->sys_users_id, $conversationMessage->message_what_id);

        //file_put_contents(storage_path().'/logs/log_webhook.txt', "<- PUSHER ->" .$conversationsArray[0]->conversationsMessages[0]->id. PHP_EOL, FILE_APPEND);

        // sacamos la conversacion derecha afectada
        $conversationsDer = $this->getConversationsMessageByUser((object)[
            "convoId" => $conversation->id
        ], $conversation->sys_users_id, $conversationMessage->id);

        // Lanza el evento broadcast
        broadcast(new NewMessageWhatsAppReceived(
            $conversationIzq[0],
            $conversationsDer
        ))->toOthers();
    }
    
    public function validateMessageConversationIdWhat($whatId){
        return ConversationsMessages::where("message_what_id", $whatId)->get();
    }
}
