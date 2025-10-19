<?php

namespace App\Services\WhatsApp;

class MessageNotTemplateService
{
    public function getPreload(){
        $message = "--\n\n🔄 Estamos procesando tu solicitud... ⏳";

        return $message;
    }
    
    public function getMsgSessionNotValida(){
            $message = "⚠️ ¡Datos invalidos! 🚫\n\n" .
        "Verifica y vuelve a intentarlo. 🔐💻";

        return $message;
    }
    
    public function getYesRptaSatisfactorio(){
        $message = "🎉 ¡Gracias por tu respuesta! Nos alegra saber que fue útil. 😊";

        return $message;
    }
    
    public function getNoRptaSatisfactorio(){
        $message = "😔 Lamentamos que no haya sido útil. Estamos trabajando para mejorar. 🔧";

        return $message;
    }
    
    public function getSaludoVuelvePronto(){
        $message = "🙏 ¡Gracias por tu visita! 😊 Esperamos verte de nuevo muy pronto. 🚀✨";
        return $message;
    }
    
    public function getReturnMenuPrincipal(){
        $message = "¿Te gustaría ver nuevamente el menú principal? 👇\n";
        return $message;
    }
    
    public function getReturnMenuPrivado(){
        $message = "¿Te gustaría ver nuevamente el menú privado? 👇\n";
        return $message;
    }
    
    public function getSessionClosedMessage($minutes = 5)
    {
        // Construir el mensaje indicando que la sesión se cerró por inactividad
        $message = "🚫 Tu última sesión ha sido cerrada por inactividad después de $minutes minutos. 😕\n";
        $message .= "Si deseas continuar, por favor inicia una nueva sesión. 👇";

        return $message;
    }
    
    public function getReturnMenuPrivadoSessionActive()
    {
        // Construir el mensaje indicando que la sesión se cerró por inactividad
        $message = "💬 Vemos que tienes una sesión activa. ¿Te gustaría ver tu menú privado? 👇";

        return $message;
    }
    
    public function getReturnMenuPrivadoDetalleResults()
    {
        // Construir el mensaje indicando que la sesión se cerró por inactividad
        $message = "💬 Opciones disponibles para este resultado 👇";

        return $message;
    }
    
    public function getSessionClosedSuccessfullyMessage()
    {
        // Construir el mensaje indicando que la sesión se cerró correctamente
        $message = "✅ Tu sesión ha sido cerrada correctamente. Gracias por tu visita. 😊\n";
        $message .= "🙏 Esperamos verte de nuevo muy pronto. 🚀✨";

        return $message;
    }
    
    public function getMsgOnlyPhoneByFlows(){
        $responseText = "--\n\n⚠️ Importante: Las opciones interactivas solo están disponibles en la app móvil de WhatsApp. 📲";
        $responseText .= "Si estás en WhatsApp Web, no podrás verlas. ¡Usa tu móvil para aprovecharlas al máximo! 😊";
        
        return $responseText;
    }
    
    public function getCamposVaciosFormulario(){
        // Enviar mensaje de error
        $message = "❌ No has ingresado ninguna palabra clave. Por favor, ingresa al menos un campo para realizar la búsqueda en el formulario.";
        return $message;
    }
    
    public function getResultsEmpty(){
        // Enviar mensaje de error
        $message = "❌ No se obtuvieron resultados en la consulta, vuelve a intentarlo.";
        return $message;
    }

    public function promptSelectResult() {
        // Enviar mensaje informativo con íconos
        $message = "ℹ️ Por favor, ingresa el número correspondiente a uno de los resultados mostrados para ver más detalles.";
        return $message;
    }
    
    public function invalidNumberInput() {
        // Enviar mensaje de error con ícono y texto claro
        $message = "❌ El valor ingresado no es un número válido. Por favor, intenta nuevamente con un número de la lista.";
        return $message;
    }
    
    public function invalidValueInput() {
        // Enviar mensaje de error con ícono y texto claro
        $message = "❌ El valor ingresado no es válido. Por favor, intenta nuevamente con el valor solicitado.";
        return $message;
    }
    
    public function numberNotInList() {
        // Enviar mensaje de error cuando el número no corresponde a un resultado del listado
        $message = "⚠️ El número ingresado no corresponde a ninguno de los resultados mostrados. Por favor, ingresa un número válido de la lista.";
        return $message;
    }
}
