<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Address;
use App\Models\Payment;
use Auth;
use PaytmWallet;


class OrderController extends Controller
{


    public function myOrder(){
        $order = Order::where([["status",true], ["user_id", Auth::id()]])->first();
        if (!$order) {
            return redirect()->route('cart')->with('error', 'No completed order found.');
        }
        $data['payment'] = Payment::where('order_id', $order->id)->first(); 
        $data['order'] = $order;
        return view("home.myorder", $data);
    }
    public function manageCarts(){
        $data['totalCarts'] = Order::where("status", false)->get();
        $data['carts'] = Order::where("status",false)->orderBy("id","desc")->paginate(2);

        return view("admin.manageCart", $data);
    }
    public function addToCart(Request $request, $id){
        $product = Product::find($id);
        $user = Auth::user();

        if($product){
            $order = Order::where([["status",false], ["user_id", $user->id]])->first();

            if($order){
                $orderItem = OrderItem::where("status",false)->where("product_id", $id)->where("order_id", $order->id)->first();
                if($orderItem){
                    // if orderItem already in a cart 
                    $orderItem->qty += 1;
                    $orderItem->save();
                }
                else{
                    $oi = new OrderItem();
                    $oi->status = false;
                    $oi->product_id = $id;
                    $oi->order_id = $order->id;
                    $oi->save();
                }
            }
            else{
                // if order not exist in cart
                $address = Address::where('user_id', $user->id)->first();
                if (!$address) {
                    return redirect()->route('checkout')->with('error', 'Please add an address before adding items to your cart.');
                }
                $o = new Order();
                $o->user_id = $user->id;
                $o->status = false;
                $o->address_id = $address->id;
                $o->save();

                $oi = new OrderItem();
                $oi->status = false;
                $oi->product_id = $id;
                $oi->order_id = $o->id;
                $oi->qty = 1;
                $oi->save();
            }
            return redirect()->route('cart')->with('success', "product added or updated on cart successfully");
        }
        else{
            return redirect()->route('home.index')->with("error","product not found");
        }
        
    }

    public function removeFormCart(Request $request, $id){
        $product = Product::find($id);
        $user = Auth::user();

        if($product){
            $order = Order::where([["status",false], ["user_id", $user->id]])->first();

            if($order){
                $orderItem = OrderItem::where("status",false)->where("product_id", $id)->where("order_id", $order->id)->first();
                if($orderItem){
                    // if orderItem already in a cart
                    if($orderItem->qty > 1){
                        $orderItem->qty -= 1;
                        $orderItem->save();
                        
                    }
                    else{
                        $orderItem->delete();
                    }
                }
            }
           
            return redirect()->route('cart')->with('success', "product updated on cart successfully");
        }
        else{
            return redirect()->route('home.index')->with("error","product not found");
        }
        
    }


    public function cart(){
        $data['order'] = Order::where([["user_id", Auth::id()], ["status",false]])->first();
        
        return view("home.cart",$data);
    }
    public function checkout(Request $req){
        $data['addresses'] = Address::where("user_id", Auth::id())->get();

        if($req->isMethod("post")){
            $data = $req->validate([
                'street_name' => 'required',
                'landmark' => 'required',
                'area' => 'required',
                'pincode' => 'required',
                'city' => 'required',
                'state' => 'required',
                'type' => 'required',
            ]);

            $data['user_id'] = Auth::id();

            Address::create($data);

            return redirect()->back()->with("success", "Address Inserted Successfully");
        }
        return view("home.checkout", $data);
    }



    public function order(Request $req)
    {


        $payment = PaytmWallet::with('receive');
        
        $order_id = rand(100,99999);
       

        // order fetch
        $order = Order::where([["status",false], ["user_id", Auth::id()]])->first();
        if (!$order) {
            return redirect()->route('cart')->with('error', 'No active order found. Please add items to your cart first.');
        }
        $order->address_id = $req->address_id;
        $order->save();

        $record = [
            'order_id' => $order->id,
            "user_id" => Auth::id(),
            "ORDERID" => $order_id,
            "TXNAMOUNT" => $req->amount, 
        ];

        Payment::create($record);


        $data = [
            'order' => $order_id,
            'user' => Auth::id(),
            'mobile_number' => 9546805580,
            'email' => Auth::user()->email,
            'amount' => $req->amount,
            'callback_url' => route("status")
        ];
    

        $payment->prepare($data);


        return $payment->receive();
    }

  
    public function paymentCallback()
    {
        $transaction = PaytmWallet::with('receive');
        
        $response = $transaction->response(); // To get raw response as array
        //Check out response parameters sent by paytm here -> http://paywithpaytm.com/developer/paytm_api_doc?target=interpreting-response-sent-by-paytm
        
        if($transaction->isSuccessful()){

            $payment = Payment::where('ORDERID',  $transaction->getOrderId())->first();
            // dd($response['BANKTXNID']);
            if($payment){
                $payment->BANKTXNID = $response['BANKTXNID'];
                $payment->CURRENCY = $response['CURRENCY'];
                $payment->GATEWAYNAME = $response['GATEWAYNAME'];
                $payment->PAYMENTMODE = $response['PAYMENTMODE'];
                $payment->RESPCODE = $response['RESPCODE'];
                $payment->RESPMSG = $response['RESPMSG'];
                $payment->STATUS = $response['STATUS'];
                $payment->TXNAMOUNT = $response['TXNAMOUNT'];
                $payment->TXNDATE = $response['TXNDATE'];
                $payment->TXNID = $response['TXNID'];

                $payment->save();
        }

            $order = Order::where([["status",false], ["user_id", Auth::id()]])->first();
            $order->status = true;
            $order->save();

           
            return redirect()->route("home.index")->with("success", "Order Placed Successfully");
          //Transaction Successful
        }else if($transaction->isFailed()){
          //Transaction Failed
        }else if($transaction->isOpen()){
          //Transaction Open/Processing
        }
        $transaction->getResponseMessage(); //Get Response Message If Available
        //get important parameters via public methods
        $transaction->getOrderId(); // Get order id
        $transaction->getTransactionId(); // Get transaction id
    }   


}
