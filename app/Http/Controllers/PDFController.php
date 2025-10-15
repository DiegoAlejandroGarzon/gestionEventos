<?php

namespace App\Http\Controllers;


use Illuminate\Http\Request;
use App\Models\EventAssistant;
use App\Models\TicketType;
use App\Models\TicketFeatures;
use App\Models\Event;
use App\Models\City;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use App\Mail\EnvioNotificacionTickenGenerado;
use App\Mail\PruebaEmail;
use Pdf;
use Srmklive\PayPal\Services\PayPal as PayPalClient;

class PDFController extends Controller
{
    public function getPDF(){


		$name = 'Juanito Perez';
		$pdf = Pdf::loadView('pdf.PDF_TicketEvento', compact('name'));
		return $pdf->stream('prueba.pdf');
	}

    public function getPDFEvento($output = false, $id = null)
    {
        $id = (is_numeric($output)?$output:$id);
        $eventAssistant = EventAssistant::find($id);
    
        // Generar el QR en base64 para el PDF
        $qrCodeBase64 = 'data:image/png;base64,' . base64_encode($eventAssistant->qrCode);
    
        // Cargar la vista y generar el PDF
        $pdf = Pdf::loadView('pdf.PDF_TicketEvento2', compact('eventAssistant', 'qrCodeBase64'));
    
        $output = false;
        // Si $output es true o cualquier valor que no sea false
        if ($output == false) {
            //return $pdf->output(); // Devuelve el PDF como binario (sin headers de descarga)
        }
    
        // Si $output es false, forzar la descarga del archivo
        return $pdf->download('ticket' . $id . '.pdf');
    }
    
    public function getPDFEventoBinary($id = null)
    {
        $eventAssistant = EventAssistant::find($id);
    
        // Generar el QR en base64 para el PDF
        $qrCodeBase64 = 'data:image/png;base64,' . base64_encode($eventAssistant->qrCode);
    
        // Cargar la vista y generar el PDF
        $pdf = Pdf::loadView('pdf.PDF_TicketEvento2', compact('eventAssistant', 'qrCodeBase64'));
    
        $output = false;
        // Si $output es true o cualquier valor que no sea false
        //if ($output == false) {
            return $pdf->output(); // Devuelve el PDF como binario (sin headers de descarga)
        //}
    
        // Si $output es false, forzar la descarga del archivo
        //return $pdf->download('ticket' . $id . '.pdf');
    }


	public function getPDFEventoQuery($id){

		$query=EventAssistant::select('events.id','events.name as evento_name','events.header_image_path',
		'events.created_at','events.event_date','events.start_time','users.id','users.name',
		'users.lastname','users.email','users.type_document','users.document_number','event_assistants.event_id','event_assistants.qrCode',
		'event_assistants.guid','ticket_types.name as localidad','event_assistants.has_entered')
		->join('events','events.id','=','event_assistants.event_id')
		->join('users','users.id','=','event_assistants.user_id')
		->join('ticket_types','ticket_types.id','=','event_assistants.ticket_type_id')
		->where('event_assistants.id',$id);
		return $query->get();
	}

	public function buildPDF_Mail($id)
	{
	    $registros=$this->getPDFEventoQuery($id);
		foreach ($registros as &$registro){
            $qrCodeBase64 = 'data:image/png;base64,' . base64_encode($registro->qrCode);
		    $pdf = Pdf::loadView('pdf.PDF_TicketEvento', compact('registros','qrCodeBase64'));
		    $pdf->setPaper(array(0,0,170,500));
		    $pdf->save(storage_path('app/public/'.$registro->evento_name.'.pdf'));
			$meta['id'] = $registro->event_id;
            $meta['user_id'] = $registro->user_id;
			$meta['title'] = $registro->evento_name;
			$meta['name'] = $registro->name;
			$meta['email'] = $registro->email;
			$meta['fileName'] = $registro->evento_name.'.pdf';
			$this->enviarEmailticket($meta);
            return $meta;
        }
    }


    public function enviarEmailticket($meta)
	{

	   Mail::to($meta['email'])->send(new EnvioNotificacionTickenGenerado($meta));



	}



	public function return_email($id)
	{

	   return view(event.index);



	}

	public function paypal($id){

		$provider = new PayPalClient;
		$infoPago['valor']=100.00;
		return view('paypal.client.checkout',compact('infoPago'));
	}

}
