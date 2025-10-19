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

            // ✅ Verifica si viene del número válido
            $validPhoneNumberId = '855752667617564';
            $receivedPhoneId = $objectBody['entry'][0]['changes'][0]['value']['metadata']['phone_number_id'] ?? null;

            // ❌ Si no viene del número válido, no se hace absolutamente nada
            if ($receivedPhoneId !== $validPhoneNumberId) {
                return response()->json(['status' => 'IGNORED'], 200);
            }

            // ✅ Procesamiento normal
            Log::info('Webhook recibido: ' . json_encode($objectBody));

            $handleWebhookService = new HandleWebhookService();
            $handleWebhookService->init($objectBody);

            return response()->json(['status' => 'EVENT_RECEIVED'], 200);
        }

        return response('Método no permitido', 405);
    }
   
}
