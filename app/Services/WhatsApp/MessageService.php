<?php

namespace App\Services\WhatsApp;

use App\Services\WhatsApp\CurlService;
use App\Services\WhatsApp\MessageNotTemplateService;
use App\Services\WhatsApp\QueryService;
use Illuminate\Support\Facades\Log;

class MessageService
{
    private $__externalPhoneNumber;
    private $__numberWhatssAppId;
    public function __construct($__externalPhoneNumber, $numberWhatssAppId) {
        $this->__externalPhoneNumber = $__externalPhoneNumber;
        $this->__numberWhatssAppId = $numberWhatssAppId;
    }
    
    public function sendMessageNotTemplate($recipientNumber, $message, $headerText, $buttons = true, $footer=true) {
        $data = [];
        $structureToStore = []; // <-- Aquí preparas lo que vas a guardar en DB

        if ($buttons) {
            // Crear la estructura base del mensaje
            $data = [
                'messaging_product' => 'whatsapp',
                'to' => $recipientNumber,
                'type' => 'interactive',  // Tipo interactivo
                'interactive' => [
                    'type' => 'button',  // El tipo de interacción es botón
                    // Condición para incluir el 'header' solo si $headerText no es null
                    'body' => [
                        'text' => $message  // Cuerpo del mensaje
                    ]
                ]
            ];
            
            // Verificamos si $headerText no es null antes de agregar la sección 'header'
            if ($headerText !== null) {
                $data['interactive']['header'] = [
                    'type' => 'text',  // El header debe ser de tipo 'text'
                    'text' => $headerText  // Texto del header
                ];
            }
            
            $structureToStore['type'] = 'response_button';
            $structureToStore['header'] = $headerText;
            $structureToStore['body'] = $message;

            if($footer){
                $footerText = "Gracias por utilizar nuestros servicios.";
                $data['interactive']['footer'] = ['text' => $footerText];
                $structureToStore['footer'] = $footerText;
            }

            // Si se pasan botones, los agregamos a la estructura de la acción
            if(is_array($buttons)){
                $data['interactive']['action'] = $buttons;
                $structureToStore['buttons'] = $buttons['buttons'] ?? [];
            }else{
                $defaultButtons = [
                    [
                        'type' => 'reply',
                        'reply' => [
                            'id' => 'btn_yes_satisfactorio',
                            'title' => 'Muy buena respuesta'
                        ]
                    ],
                    [
                        'type' => 'reply',
                        'reply' => [
                            'id' => 'btn_no_satisfactorio',
                            'title' => 'No fue satisfactorio'
                        ]
                    ]
                ];
                $data['interactive']['action'] = ['buttons' => $defaultButtons];
                $structureToStore['buttons'] = $defaultButtons;
            }
        }
        else{

            $data = [
                'messaging_product' => 'whatsapp',
                'to' => $recipientNumber,
                'type' => 'text',
                'header' => null,
                'text' => [
                    'body' => $message
                ],
                'footer' => null
            ];
            
            // Para texto simple también puedes guardar estructura básica si quieres
            $structureToStore = [
                'type' => 'response_text',
                'header' => null,
                'body' => $message,
                'footer' => null
            ];
        }


        // Llamar a la función que envía la solicitud
        $CurlService = new CurlService($this->__numberWhatssAppId);
        $CurlService->curlFacebookApi($data);
        
        return $structureToStore;
    }

    public function sendMessage(
        $recipientNumber,
        $templateName,
        $templateParams = null,
        $flowToken = null,
        $data = false,
        $dynamicUrl = null,
        $urlFile = null,
        $nameFile = null
    ) {
        
        if($data == false){
            // Crear la base de la data
            $data = [
                'messaging_product' => 'whatsapp',
                'to' => $recipientNumber,
                'type' => 'template',
                'template' => [
                    'name' => $templateName,
                    'language' => [
                        'code' => "es_MX"
                    ],
                ]
            ];

            // Agregar componentes para el Flow si se proporciona un flowToken
            if ($flowToken != null) {
                $data['template']['components'] = [
                    [
                        'type' => 'button',
                        'sub_type' => 'flow',
                        'index' => '0',
                        'parameters' => [
                            [
                                'type' => 'action',
                                'action' => [
                                    //'flow_token' => $flowToken,  // Token del Flow
                                    //'flow_action_data' => $templateParams // Datos para el Flow
                                ]
                            ]
                        ]
                    ]
                ];
                
                if ($templateParams != null) {
                    // Agregar componentes solo si hay parámetros
                    $data['template']['components'][] = 
                            [
                                'type' => 'body',
                                'parameters' => $templateParams
                            ];
                }
            } elseif ($templateParams != null) {
                // Agregar componentes solo si hay parámetros
                $data['template']['components'] = [
                    [
                        'type' => 'body',
                        'parameters' => $templateParams // Aquí se añaden los parámetros
                    ]
                ];
            }
            
            if($dynamicUrl != null){
                // Añadir un componente de botón con la URL dinámica
                $data['template']['components'][] = [
                    'type' => 'button',
                    'sub_type' => 'url',  // Tipo de botón URL
                    'index' => '0',
                    'parameters' => [
                        [
                            'type' => 'text',
                            'text' => $dynamicUrl
                        ]
                    ]
                ];
            }
            
            $result = [
                'type' => 'response_flow',
                'header' => null,
                'body' => null,
                'footer' => null
            ];
            
            if (isset($data['template']['components'])) {
                foreach ($data['template']['components'] as $component) {
                    if ($component['type'] === 'header') {
                        $result['header'] = $component['parameters'][0]['text'] ?? null;
                    }
                    if ($component['type'] === 'body') {
                        $result['body'] = $component['parameters'][0]['text'] ?? null;
                    }
                    if ($component['type'] === 'footer') {
                        $result['footer'] = $component['parameters'][0]['text'] ?? null;
                    }
                }
            }
            
            /*if($urlFile != null){
                // Añadir un componente de botón con la URL dinámica
                $data['template']['components'][] = [
                    'type' => 'document',
                    'document' => [
                        [
                            'link' => $urlFile,
                            'filename' => $nameFile.'.pdf'
                        ]
                    ]
                ];
            }*/
        }
        
        // Llamar a la función que envía la solicitud
        $CurlService = new CurlService($this->__numberWhatssAppId);
        $response = $CurlService->curlFacebookApi($data);
        $response = json_decode($response, true);
        $messageId = null;
        $statusMsg = null;
        if(isset($response["messages"]) && is_array($response["messages"]) && count($response["messages"])>0 && isset($response["messages"][0]["id"])){
            $messageId = $response["messages"][0]["id"];
            $statusMsg = $response["messages"][0]["message_status"];
        }
        
        /*Log::info('Enviando plantilla a WhatsApp', [
            'to' => $recipientNumber,
            'template' => $templateName,
            'components' => $data['template']['components'] ?? null
        ]);*/

        
        return (object)[
            'result' => $result,
            'response' => $response,
            'messageId' => $messageId,
            'messageStatus' => $statusMsg
        ];

    }
    
    public function getDataMenuList($headerText, $bodyText, $footerText, $buttonText, $sections){
        return $data = [
            'messaging_product' => 'whatsapp',
            'recipient_type' => 'individual',
            'to' => $this->__externalPhoneNumber,
            'type' => 'interactive',
            'interactive' => [
                'type' => 'list',
                'header' => [
                        'type' => 'text',
                        'text' => $headerText
                ],
                'body' => [
                        'text' => $bodyText
                ],
                'footer' => [
                        'text' => $footerText
                ],
                'action' => [
                        'button' => $buttonText,
                        'sections' => $sections  // Aquí pasas las secciones con filas
                ]
            ]
        ];
    }
    
    public function sendMessageErrorLogin($responseText){
        $buttons = [
            'buttons' => [  
                [
                    'type' => 'reply',  // El tipo de botón es 'reply' para respuestas rápidas
                    'reply' => [
                            'id' => 'btn_yes_intentar_clave',
                            'title' => 'Volver a intentarlo'
                    ]
                ],
                [
                    'type' => 'reply',  // El tipo de botón es 'reply' para respuestas rápidas
                    'reply' => [
                            'id' => 'btn_no_intentar_clave',
                            'title' => 'No intentar más'
                    ]
                ]
            ]
        ];

        return $this->sendMessageNotTemplate($this->__externalPhoneNumber, $responseText, "Datos invalidos", $buttons);
    }
    
    public function sendReturnMenuPrincipal(){
        $messageNotTemplateService = new MessageNotTemplateService();
        $responseText = $messageNotTemplateService->getReturnMenuPrincipal();
        
        $buttons = [
            'buttons' => [  
                [
                    'type' => 'reply',  // El tipo de botón es 'reply' para respuestas rápidas
                    'reply' => [
                            'id' => 'btn_yes_volver_menu_principal',
                            'title' => 'Volver'
                    ]
                ],
                [
                    'type' => 'reply',  // El tipo de botón es 'reply' para respuestas rápidas
                    'reply' => [
                            'id' => 'btn_no_volver_menu_principal',
                            'title' => 'No volver'
                    ]
                ]
            ]
        ];
        
        $responseTplArr = $this->sendMessageNotTemplate($this->__externalPhoneNumber, $responseText, "🔄 Volver al menú principal", $buttons);
                
        // ingresamos la auto respuesta
        $queryService = new QueryService($this->__externalPhoneNumber, $this->__numberWhatssAppId);
        $queryService->storeResponseAutoBot(
            "Respuesta automatica", 
            null, 
            "text",
            "auto_text", 
            $responseTplArr
        );
    }
    
    public function sendReturnMenuPrivado(){
        $messageNotTemplateService = new MessageNotTemplateService();
        $responseText = $messageNotTemplateService->getReturnMenuPrivadoSessionActive();
        
        $buttons = [
            'buttons' => [  
                [
                    'type' => 'reply',  // El tipo de botón es 'reply' para respuestas rápidas
                    'reply' => [
                            'id' => 'btn_no_volver_menu_privado_cerrar_sesion',
                            'title' => 'Cerrar sesión'
                    ]
                ],
                [
                    'type' => 'reply',  // El tipo de botón es 'reply' para respuestas rápidas
                    'reply' => [
                            'id' => 'btn_yes_volver_menu_privado',
                            'title' => 'Menú privado'
                    ]
                ]
            ]
        ];
        
        $responseTplArr = $this->sendMessageNotTemplate($this->__externalPhoneNumber, $responseText, "🔄 Volver al menú privado", $buttons);
                
        // ingresamos la auto respuesta
        $queryService = new QueryService($this->__externalPhoneNumber, $this->__numberWhatssAppId);
        $queryService->storeResponseAutoBot(
            "Respuesta automatica", 
            null, 
            "text",
            "auto_text", 
            $responseTplArr
        );
    }
    
    public function sendRespuestaDetalleVolverMenu($buttonsExtras=null, $buttonsReturn=null){
        $messageNotTemplateService = new MessageNotTemplateService();
        $queryService = new QueryService($this->__externalPhoneNumber, $this->__numberWhatssAppId);
        
        $buttons["buttons"] = array();
        // Validar si hay botones extra y agregarlos
        if (!is_null($buttonsExtras) && is_array($buttonsExtras)) {
            $responseText = $messageNotTemplateService->getReturnMenuPrivadoDetalleResults();
            // Unir los arrays de botones
            $buttons['buttons'] = array_merge($buttons['buttons'], $buttonsExtras);
            $responseTplArr = $this->sendMessageNotTemplate($this->__externalPhoneNumber, $responseText, "⚠️ ¿Qué más te gustaría hacer?", $buttons, false);
                
            // ingresamos la auto respuesta
            $queryService->storeResponseAutoBot(
                "Respuesta automatica", 
                null, 
                "text",
                "auto_text", 
                $responseTplArr
            );
        }
        
        // enviamos el mensaje por separado de volver al menu principal
        $buttonsVolverMenu = [
            'buttons' => [
                [
                    'type' => 'reply',  // El tipo de botón es 'reply' para respuestas rápidas
                    'reply' => [
                            'id' => 'btn_yes_volver_menu_privado',
                            'title' => 'Menú privado'
                    ]
                ],
                /*[
                    'type' => 'reply',  // El tipo de botón es 'reply' para respuestas rápidas
                    'reply' => [
                            'id' => 'btn_no_volver_menu_privado_cerrar_sesion',
                            'title' => 'Cerrar sesión'
                    ]
                ]*/
            ]
        ];
        
        if(!is_null($buttonsReturn) && is_array($buttonsReturn)) {
            $buttonsVolverMenu['buttons'] = array_merge($buttonsVolverMenu['buttons'], $buttonsReturn);
        }
        
        $responseText = $messageNotTemplateService->getReturnMenuPrivado();
        $responseTplArr = $this->sendMessageNotTemplate($this->__externalPhoneNumber, $responseText, "🔄 ¿Volver al menú privado?", $buttonsVolverMenu, false);
        // ingresamos la auto respuesta
        $queryService->storeResponseAutoBot(
            "Respuesta automatica", 
            null, 
            "text",
            "auto_text", 
            $responseTplArr
        );
        
        return true;
    }
    
    public function setPromptSeleccioneResultadoDetalles(){
        $messageNotTemplateService = new MessageNotTemplateService();
        $responseText = $messageNotTemplateService->promptSelectResult();
        
        $responseTplArr = $this->sendMessageNotTemplate($this->__externalPhoneNumber, $responseText, "👉 Seleccione un resultado", null);
        
        // ingresamos la auto respuesta
        $queryService = new QueryService($this->__externalPhoneNumber, $this->__numberWhatssAppId);
        $queryService->storeResponseAutoBot(
            "Respuesta automatica", 
            null, 
            "text",
            "auto_text", 
            $responseTplArr
        );
    }
}
