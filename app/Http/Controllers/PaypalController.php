<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use srmklive\paypal\src\Service\PayPal;
use App\Models\Order;
use App\Models\User;
use DB;
use App\Notifications\invoicePaid;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Traits\HasRoles;
use App\Http\Controllers\PDFController;


class PaypalController extends Controller
{
    //
    public function create(Request $request){

        $data=json_decode($request->getContent(),true);
       

       

        //init paypal
        $provider=\PayPal::setProvider();
        $provider->setApiCredentials(config('paypal'));
        $token=$provider->getAccessToken();
        $provider->setAccessToken($token);
        
       
        
        $order=$provider->createOrder([
            "intent"=>"CAPTURE",
            "purchase_units"=> [
                [
                    "amount" => [
                    "currency_code"=>"USD",
                    "value"=> $data['quantity']
                    ],
                    "description" => $data['description']
                    ]
            ]
            ]);
        //save create order to database
        $pedido=Order::create([
           'nombres'=>$data['name'],
           'apellidos'=>$data['lastName'],
           'cedula'=>$data['id'],
           'telefono'=>$data['phone'],
           'reference_number'=>$order['id'],
           'description'=>$data['description'],
           'precio'=>$data['quantity'],
           'status'=>$order['status'],
        ]);
       
        $this-> makeOrderNotifications($pedido);

        return response()->json($order);
    }


    public function capture(Request $request){

        $data=json_decode($request->getContent(),true);
        $orderId=$data['orderId'];
       

        //init paypal
        $provider=\PayPal::setProvider();
        $provider->setApiCredentials(config('paypal'));
        $token=$provider->getAccessToken();
        $provider->setAccessToken($token);

        $result=$provider->capturePaymentOrder($orderId);

        //update database
        if($result['status'=='COMPLETED']){
            DB:table('orders')
            ->where('reference_number',$result['id'])
            ->update(['status'=>'COMPLETED','updated_at'=>\Carbon\Carbon::now()]);

            $eventAsistant = EventAssistant::find($data['event_assistant_id']);
            if($eventAsistant->isFullyPaid()){
                $eventAsistant->is_paid = true;
                $eventAsistant->save();
            }
            
            $PDFController=new PDFController();
            $meta=$PDFController->buildPDF_Mail($request->event_assistant_id);
            return view('email.return_email_ticketevent',compact('meta'));

        }

        #return response()->json($result);
    }


    public function makeOrderNotifications($order){

   
     $user=User::whereHas('roles', function ($query) {
        $query->where('name', 'Admin');
    })->get();

     $user->except($order->nombre);
     $user->each(function(User $user) use ($order){
        $user->notify(new invoicePaid($order));
     });

    }


    public function Paypal(){
      return view('paypal.checkout');
    }


}
