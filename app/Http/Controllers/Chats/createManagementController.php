<?php
namespace App\Http\Controllers\Chats;

use Illuminate\Routing\Controller;
use App\Services\WhatsApp\HandleWebhookService;
use Illuminate\Http\Request;
use App\Services\ConversationService;
use App\Services\WhatsApp\MessageService;
use Illuminate\Support\Facades\Log;

class createManagementController extends Controller{

    public function __construct() {}
    
    /**
     * methodo main de retorno del view
     *
     * @return void
    */
    public function main(){
        try{} catch (\Exception $ex) {
            throw $ex;
        }
    }
    
    public function handleWeBHook(Request $request)
    {
        // Tu token definido en Facebook Developer
        $verify_token = 'T3sting!B4nc4';

        // Paso 1: Verificación inicial (cuando Facebook hace GET para validar)
        if ($request->isMethod('get')) {
            if ($request->input('hub_verify_token') === $verify_token) {
                return response($request->input('hub_challenge'), 200);
            } else {
                return response('Token de verificación inválido', 403);
            }
        }

        // Paso 2: Recepción de eventos (POST)
        if ($request->isMethod('post')) {
            $requestBody = $request->getContent();
            $objectBody = json_decode($requestBody, true);

            // Log opcional
            Log::info('Webhook recibido:', $objectBody);

            // Procesar evento vía servicio
            $handleWebhookService = new HandleWebhookService();
            $handleWebhookService->init($objectBody);

            // Facebook espera una respuesta 200 con contenido
            return response()->json(['status' => 'EVENT_RECEIVED'], 200);
        }

        // Para otros métodos, retornar 405
        return response('Método no permitido', 405);
    }

    
}
