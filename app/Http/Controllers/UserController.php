<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use App\Models\Cart;
use App\Models\Category;
use App\Models\Complaint;
use App\Models\Order;
use App\Models\Product;
use App\Models\Review;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class UserController extends Controller
{
    /*this function gets the overall detail attached to the user
        returns => products, carts, orders, complaint, response
    */

    function getProduct(){

        try{
            $product = Product::with(['category','brand'])->get();
            $category = Category::with('product')->get();
            $brand = Brand::with('product')->get();

            if ($product){
                return response()->json(['message'=>'Product Fetched Successfully','product'=>$product,'category'=>$category,'brand'=>$brand],200);
            }else{
                return response()->json(['message'=>'No product Found','data'=>$product],200);
            }


        }catch (\Exception $exception){
            return response()->json(['message'=>'Error while fetching Products','data'=>$exception->getMessage()],501);
        }

    }

    function getOverallIndex(Request $request){
        $req = $request->all();
        try {
            $user = User::where('id', $req['user_id'])->with(
                ['cart.product.category','cart','order.product','complaint.response']
            )->first();

            $product = Product::all();

            $sortedOrder = $user->order->sortBy('invoice_number');


            /*todo: add topproduct and discount Product, newProduct*/
            $response = [
                'cart' => $user->cart,
                'order' => $sortedOrder->groupBy('invoice_number'),
                'cancelledOrder' => $sortedOrder->where('status','cancelled')->groupBy('invoice_number')->sortByDesc('created_at'),
                'PendingOrder' => $sortedOrder->where('status','processing')->groupBy('invoice_number'),
                'Delivered' => $sortedOrder->where('status','delivered')->groupBy('invoice_number'),
                'complaint' => $user->complaint,
                'response' => $user->complaint->flatMap->response
            ];

            return response()->json(['data' => $response,'message'=> "User Data Fetcheed Successfully"],200);


        }catch (\Exception $exception){
            return response()->json(['message' => $exception->getMessage()],501);
        }

    }

    public function deleteCart(Request $request){
        $req = $request->all();
        $checkCart = Cart::where([
            ['user_id',$req['user_id']],
            ['product_id',$req['product_id']]
        ])->first();
        if ($checkCart){
            $checkCart->delete();
            return response()->json(['message'=>'Cart has been deleted Successfully'],200);

        }
        else{
            return response()->json(['message'=>'Cart not found'],200);

        }





    }

    public function updateCart(Request $request){
        $req = $request->all();

        /*return response($req);*/

        try {
            $product = Product::where('id', $req['product_id'])->first();

            $checkCart = Cart::where([
                ['user_id',$req['user_id']],
                ['product_id',$req['product_id']],

            ])->first();

            if ($checkCart){
                $checkCart->update(['quantity'=> $req['quantity']]);
                return response()->json(['message'=>'Product quantity has been updated','data' =>$checkCart],200);
            }else{
                $payload = [
                    'user_id' => $req['user_id'],
                    'product_id' => $req['product_id'],
                    'quantity' => $req['quantity'],
                ];
                $cart = Cart::create($payload);

                return response()->json(['message'=>"{$product->title} added to Cart ",'data'=>$cart],200);
            }


        }catch (\Exception $exception){
            return response()->json(['message'=> $exception],501);
        }
    }


    /*Add product to Cart*/
    public function addCart(Request $request){

        $req = $request->all();

        /*return response($req);*/

        try {
            $product = Product::where('id', $req['product_id'])->first();

            $checkCart = Cart::where([
                ['user_id',$req['user_id']],
                ['product_id',$req['product_id']]
            ])->first();

            if ($checkCart){
                $checkCart->update(['quantity'=>$checkCart['quantity'] + $req['quantity']]);
                return response()->json(['message'=>'Product quantity has been updated','data' =>$checkCart],200);
            }else{
                $payload = [
                    'user_id' => $req['user_id'],
                    'product_id' => $req['product_id'],
                    'quantity' => $req['quantity'],
                ];
                $cart = Cart::create($payload);

                return response()->json(['message'=>"{$product->title} added to Cart ",'data'=>$cart],200);
            }


        }catch (\Exception $exception){
            return response()->json(['message'=> $exception],501);
        }
    }

    public function bulkAdd(Request $request){
        $req = $request->all();

        try {
            foreach ($req['carts'] as $cart){
                $checkCart = Cart::where([
                    ['user_id',$req['user_id']],
                    ['product_id',$cart['product_id']]
                ])->first();

                if ($checkCart){
                    $checkCart->update(['quantity'=>$req['quantity']]);
                    return response()->json(['message'=>'Product quantity has been updated'],200);
                }else{
                    $payload = [
                        'user_id' => $req['user_id'],
                        'product_id' => $req['product_id'],
                        'quantity' => 1,
                    ];
                    $cart = Cart::create($payload);

                }
            }
            return response()->json(['message'=>"All products has been added to Cart ",'data'=>$cart],200);

        }catch (\Exception $exception){
            return response()->json(['message' => $exception->getMessage()],501);
        }


    }

    /* the order function will be called inside the Paystack function when its successful*/

    public function addOrder(Request $request){
        $req = $request->all();

        try {

            foreach ($req['cart'] as $cart){

               $cart['transaction_id'] = $req['transaction_id'];
               $cart['invoice_number'] = $req['invoice_number'];
               $cart['total_price'] = $req['total_price'];

               $dbCart = Cart::create($cart);

            }
            return response()->json(['message'=>"order {$req['invoice_number']} has been Processed "],200);

        }catch (\Exception $exception){
            return response()->json(['message'=> $exception],501);
        }
    }

    public function addComplain(Request $request){

        $req = $request->all();

        try {
            $complain = Complaint::create($req);


            return response()->json(['message'=>"Your complained has been Successfully Logged",'data'=>$complain],200);

        }catch (\Exception $exception){
            return response()->json(['message'=>"An error has occurred {$exception}"],401);
        }


    }

    public function  addReview(Request $request){
        $req = $request->all();

        try {

            $review = Review::create($req);

            return response()->json(['message' => "Thank you for your review",'data' => $review],200);

        }catch (\Exception $exception){
            return response()->json(['message' => "An error has occurred: {$exception}"],200);

        }
    }

    public function updateProfile(Request $request){
        $req = $request->all();

        $userProfile = User::where('id',$req['user_id'])->first();

        if ($userProfile){

            if ($request->hasFile('image')){
                $path = $request->file('image')->store('userImage','public');

                $req['image'] = $path;



            }

            $isUpdated = $userProfile->update($req);

            /*Deletes the old image if update is successful*/
            if ($isUpdated && $request->hasFile('image') ){
                Storage::delete($userProfile->image);
            }

            return response()->json(['message' => "Profile Updated Successfully",'user' => $userProfile],200);

        }else{
            return response()->json(['message' => "Failed to find User"],401);

        }


    }

    /*this function calls when the user wants to cancel an order and requests for a refund */
    public function updateOrder(Request $request){
        $req = $request->all();



        try {
            $order = Order::where('invoice_number',$req['invoice_number'])->update([
                'status' => $req['status'],
                'refund'=> 1
            ]);

            $user  = User::where('id',$req['user_id'])->update([
               'account_number' => $req['account']
            ]);

            return response()->json(['message' => "Your order has been {$req['status']}", 'data' => $order],200);

        }catch (\Exception $exception){
            return response()->json(['message' => "An error has occurred: {$exception}"],200);

        }



    }

}
