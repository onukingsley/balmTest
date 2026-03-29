<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class PaystackController extends Controller
{
    public function  initiatePayment(Request $request){
        $req = $request->all();

        $paymentInit = Http::withToken(env("PAYSTACK_SECRET_KEY"))
            ->post("https://api.paystack.co/transaction/initialize",[
                'email' => $req['email'],
                'amount' => $req['amount'] *100,
                'callback_url' => url("/api/paymentCallback?user_id={$request->user()->id}&address={$request['address']}")
            ]);

        return response()->json($paymentInit->json(),200);
    }

    public function paymentCallback(Request $request){
        $req = $request->all();



        $confirmPayment = Http::withToken(env("PAYSTACK_SECRET_KEY"))
            ->get("https://api.paystack.co/transaction/verify/{$req['reference']}");

/*        return response($confirmPayment);*/

        if ( $confirmPayment['data']['status'] === 'success'){



            $carts = Cart::where([
                ['user_id', $req['user_id']],
            ])->get();

            $invoice_id = Str::random(10);

            foreach ($carts as $cart){
                $payload = [
                    'user_id' => $cart['user_id'],
                    'transaction_id' => $confirmPayment['data']['id'],
                    'invoice_number' => $invoice_id,
                    'quantity' => $cart['quantity'],
                    'product_id' => $cart['product_id'],
                    'total_price' => $confirmPayment['data']['amount']/100,
                    'delivery_address' => $req['address'],
                ];

                Order::create($payload);

                $cart->delete();
            }

            return redirect(env('FRONT_END_URL')."/checkout?reference={$req['reference']}&status=success&tnxId={$invoice_id}");
        }
        else {
            return redirect(env('FRONT_END_URL')."/checkout?&status=failed");

        }
    }


}
