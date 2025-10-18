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
    
    public function handleWeBHook(Request $request){
        // Define tu token de verificación
        $verify_token = 'T3sting!B4nc4';

        // Maneja las notificaciones de eventos
        $requestBody = $request->getContent();
        // Decodifica la carga útil
        $objectBody = json_decode($requestBody, true);
        
        // Guarda la carga útil en un archivo de registro
        //file_put_contents(storage_path().'/logs/log_webhook.txt', "<<== inicio =>>". $requestBody . PHP_EOL, FILE_APPEND);
        
        $handleWebhookService = new HandleWebhookService();
        $handleWebhookService->init($objectBody);
        
        // Verifica el token de la solicitud de verificación
        if ($request->input('hub_verify_token') === $verify_token) {
            return response($request->input('hub_challenge'), 200);
        }
        
        // Responde con un 200 OK
        return response()->json(['status' => 'success'], 200);
    }
    
}
