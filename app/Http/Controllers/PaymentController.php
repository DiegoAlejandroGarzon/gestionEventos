<?php

namespace App\Http\Controllers;

use App\Exports\PaymentExport;
use App\Exports\TemplatePayloadExport;
use App\Imports\PayloadImport;
use App\Models\Coupon;
use App\Models\Event;
use App\Models\EventAssistant;
use App\Models\Payment;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class PaymentController extends Controller
{
    public function index(Request $request, $idEvent)
    {
        $event = Event::find($idEvent);

        // Crear el query base para los asistentes
        $query = EventAssistant::query()->where('event_id', $idEvent);

        // Aplicar el filtro de búsqueda si existe
        if ($request->has('search')) {
            $search = $request->input('search');

            $query->where(function ($q) use ($search) {
                // Buscar en la relación 'user'
                $q->whereHas('user', function ($query) use ($search) {
                    $query->where('name', 'like', "%{$search}%")
                          ->orWhere('email', 'like', "%{$search}%")
                          ->orWhere('phone', 'like', "%{$search}%");
                });

                // Buscar en la relación 'ticketType'
                $q->orWhereHas('ticketType', function ($query2) use ($search) {
                    $query2->where('name', 'like', "%{$search}%");
                });

                // Verificar si 'search' contiene "Entrada" y filtrar por 'has_entered'
                if (strtolower($search) === 'entrada') {
                    $q->orWhere('has_entered', 1); // Buscar entradas con valor 1
                } elseif (strtolower($search) === 'no entrada') {
                    $q->orWhere('has_entered', 0); // Buscar entradas con valor 0
                }

                // Verificar si 'search' contiene "pagado", "no pagado" o "pendiente"
                if (strtolower($search) === 'pagado') {
                    $q->orWhere(function ($q) {
                        $q->where('is_paid', true); // Buscar asistente con is_paid = true
                    });
                } elseif (strtolower($search) === 'no pagado') {
                    $q->orWhere(function ($q) {
                        $q->where('is_paid', false)
                          ->whereDoesntHave('payments'); // No hay pagos registrados
                    });
                } elseif (strtolower($search) === 'pendiente') {
                    $q->orWhere(function ($q) {
                        $q->where('is_paid', false)
                          ->whereHas('payments'); // Tiene al menos un pago registrado
                    });
                }
            });
        }

        // Obtener los asistentes filtrados
        $asistentes = $query->paginate(10);

        // Cálculo de valores con base en los asistentes filtrados
        $asistentesFiltrados = $query->get(); // Obtener todos los resultados sin paginación para cálculos

        // Recaudo total con base en los asistentes filtrados
        $recaudoTotal = Payment::whereHas('eventAssistant', function ($q) use ($asistentesFiltrados) {
            $q->whereIn('id', $asistentesFiltrados->pluck('id')); // Usar IDs de los asistentes filtrados
        })->sum('amount');

        // Asistentes pagados
        $asistentesPagos = $asistentesFiltrados->filter(function ($asistente) {
            return $asistente->payments->count() > 0; // Contar solo los que tienen pagos registrados
        })->count();

        // Asistentes que no registraron entrada
        $asistentesSinEntrada = $asistentesFiltrados->where('has_entered', false)->count();

        // Cupones redimidos con base en los asistentes filtrados
        $cuponesRedimidos = Coupon::whereIn('event_assistant_id', $asistentesFiltrados->pluck('id'))
            ->whereNotNull('event_assistant_id')
            ->count();

        // Cupones no redimidos con base en los asistentes filtrados
        $cuponesNoRedimidos = Coupon::where('event_id', $idEvent)
            ->whereNull('event_assistant_id')
            ->count();

        // Devolver la vista con los datos calculados y filtrados
        return view('payment.index', compact(
            'asistentes',
            'idEvent',
            'event',
            'recaudoTotal',
            'asistentesPagos',
            'asistentesSinEntrada',
            'cuponesRedimidos',
            'cuponesNoRedimidos'
        ));
    }


    public function generatePDF($id)
    {
        // Obtener el pago específico por ID
        $payment = Payment::findOrFail($id);

        // Generar la vista en formato PDF
        $pdf = PDF::loadView('pdf.payment', compact('payment'));

        return $pdf->stream('detalle_pago_'.$payment->payer_name.'.pdf');
        // return $pdf->download('payment_details_' . $payment->id . '.pdf');
    }

    public function downloadTemplate()
    {
        $headers = [
            'Numero de documentos del asistente',
            'Nombre del pagador',
            'Tipo Documento',
            'Numero Documento',
            'Valor a Pagada',
            'Formato de pago (transferencia, efectivo)',
        ];

        // Crear una instancia de la exportación con los encabezados
        $export = new TemplatePayloadExport($headers);

        // Descargar el archivo Excel
        return Excel::download($export, 'plantilla_Pagos.xlsx');
    }

    // Procesa el archivo de Excel y asigna los asistentes de forma masiva
    public function uploadMassPayload(Request $request, $idEvent)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv|max:2048', // Validar que sea un archivo Excel
        ]);

        try {
            // Instanciar el importador y recolectar los datos de las importaciones
            $import = new PayloadImport($idEvent);
            Excel::import($import, $request->file('file'));

            // Obtener los detalles de los usuarios agregados y las novedades
            $importedUsers = $import->getImportedUsers();
            $messages = $import->getMessages();

            return redirect()->back()
                            ->with('success', 'Asistentes pagados exitosamente.')
                            ->with('importedUsers', $importedUsers)
                            ->with('messages', $messages);
        } catch (\Exception $e) {
            return redirect()->back()
                            ->with('error', 'Hubo un error al procesar el archivo: ' . $e->getMessage());
        }
    }

    public function exportExcel(Request $request, $idEvent)
    {
        $event = Event::find($idEvent);
        // Obtener la búsqueda, campos seleccionados y parámetros adicionales desde el request
        $search = $request->input('search');
        $selectedFields = json_decode($event->registration_parameters, true) ?? [];
        $additionalParameters = $event->additionalParameters;
        // Exportar el archivo Excel usando los datos proporcionados
        return Excel::download(
            new PaymentExport($idEvent, $selectedFields, $additionalParameters, $search),
            'pagos_de_asistentes_del_evento_'.$event->name.'_'.date('d-m-Y').'.xlsx'
        );
    }

    public function exportExcelPaymentStatus(Request $request, $idEvent)
    {
        $event = Event::find($idEvent);
        // Obtener la búsqueda, campos seleccionados y parámetros adicionales desde el request
        $search = $request->input('search');
        $selectedFields = json_decode($event->registration_parameters, true) ?? [];
        $additionalParameters = $event->additionalParameters;
        // Exportar el archivo Excel usando los datos proporcionados
        return Excel::download(
            new PaymentExport($idEvent, $selectedFields, $additionalParameters, $search, $paymentStatus = true),
            'pagos_de_asistentes_del_evento_'.$event->name.'_'.date('d-m-Y').'.xlsx'
        );
    }

}
