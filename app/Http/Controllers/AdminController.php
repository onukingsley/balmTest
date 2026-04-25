<?php

namespace App\Http\Controllers;

use App\Events\OrderConfirmed;
use App\Models\Brand;
use App\Models\Cart;
use App\Models\Category;
use App\Models\Complaint;
use App\Models\Order;
use App\Models\Product;
use App\Models\Response;
use App\Models\Review;
use App\Models\User;
use http\Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AdminController extends Controller
{
    /* This function, returns, products, carts, pending orders, cancelledOrders, DeliveredOrders
        complaint, pendingComplaint, reviews, users
    */
    public function getOverallAdmin(){



        try {
            $products = Product::with(['review','category','brand'])->get();
            $category = Category::with(['product'])->get();
            $brand = Brand::with(['product'])->get();




            /*Gets all the cart and group by both user and product*/
            $carts = Cart::all();

            $userCart = $carts->groupBy('user_id');
            $productInCart = $carts->groupBy('product_id');



            $orders = Order::with('product','user')->get();

            $userOrder = $orders->groupBy('invoice_number');

            $cancelledOrder = $orders->where('status','cancelled')->groupBy('invoice_number');
            $pendingOrder = $orders->where('status','processing')->groupBy('invoice_number');
            $confirmedOrder = $orders->where('status','confirmed')->groupBy('invoice_number');
            $pendingAndDelivered = $orders->whereIn('status',['confirmed','delivered'])->groupBy('invoice_number');
            $delivered = $orders->where('status','delivered')->groupBy('invoice_number');
            $awaitRefund = $orders->where('refund','1')->where('status','cancelled')->groupBy('invoice_number');


            $totalRevenue = 0;
            if($pendingAndDelivered){

                foreach ($pendingAndDelivered as $ord){
                    $totalRevenue += $ord[0]['total_price'];
                }

            }


            /*gets all the complaints and then also the pending complaint*/
            $allComplaint = Complaint::with('response')->get();

            $pendingComplaint = Complaint::with('response')->where('status','pending')->get();

/*            $lowReview = Review::with('product')->where('rating','<=','2')->groupBy('product_id')->get();*/


            $userManagement = User::with(['order'])->where('user_role','1')->get();


            $data = [
                'products' => $products,
                'category' => $category,
                'brand' => $brand,
                'carts' => $userCart,
                'orders' => $userOrder,
                'productInCart' => $productInCart,
                'pendingOrder' => $pendingOrder,
                'confirmedOrder' => $confirmedOrder,
                'deliveredOrder' => $delivered,
                'cancelledOrder' => $cancelledOrder,
                'awaitingRefund' =>$awaitRefund,
                'allComplaint' => $allComplaint,
                'pendingComplaint' => $pendingComplaint,
/*                'lowReview' => $lowReview,*/
                'userManagement' => $userManagement,
                'totalRevenue' => $totalRevenue
            ];

            return response()->json(['data'=>$data, 'message'=>'Admin Data Fetched Successfully'],200);


        }catch (\Exception $exception){
            return response()->json([ 'message'=>"Failed to load Admin Data: {$exception}"],501);
        }


    }


    /* Handles Adding a new response to the database*/
    public function addResponse(Request $request){
        $req = $request->all();

        try {
            $complaintResponse = Response::create($req);

            $updateComplain = Complaint::where('id', $req['complaint_id'])->update([
                'status' => 'resolved'
            ]);

            return response()->json(['message'=>"Complaint {$req['complaint_id']} has been responded", 'data'=> $complaintResponse],200);

        }catch (\Exception $exception){
            return response()->json(['message'=>"Failed to respond to Complaint {$req['complaint_id']} at this moment"]);

        }

    }



    /* Handles adding a new Product record to the Database. */
    public function addProduct(Request $request){
        $req = $request->all();

        if ($request->hasFile('product_image')){
            $path = $request->file('product_image')->store('products','public');

            $req['product_image'] = $path;
        }

        try {
            $product = Product::create($req);

            return response()->json(['message'=>"{$req['title']} has been added successfully", 'data'=> $product],200);

        }catch (\Exception $exception){
            return response()->json(['message'=>"Failed to add {$req['title']} at this moment"]);

        }

    }



    /* Handles Product update. Parameters- product_id, (any product to be updated)*/
    public function updateProduct(Request $request){
        $req = $request->all();


        if ($request->hasFile('product_image')){
            $path = $request->file('product_image')->store('products','public');

            $req['product_image'] = $path;
        }

        try {
            $product = Product::where('id',$req['product_id'])->first();

            $product_image = $product->product_image;


            $updated = $product->update($req);

            if ($updated && $request->hasFile('product_image')){
                Storage::disk('public')->delete($product_image);
            }


            return response()->json(['message'=>"Product has been updated", 'data'=> $product],200);

        }catch (Exception $exception){
            return response()->json(['message'=>"Failed to update product at this moment",'error'=>$exception]);

        }

    }


    /* This handles user Update. Parameters - user_id, (any column to be updated)*/
    public function updateUser(Request $request){
        $req = $request->all();

        try {
            $user = User::where('id',$req['id'])->update($req);

            return response()->json(['message'=>"User has been updated", 'data'=> $user],200);

        }catch (\Exception $exception){
            return response()->json(['message'=>"Failed to update User at this moment"]);

        }

    }


    /*this function handles updating a single orderStatus by the id */
    public function updateSingleOrder(Request $request){
        $req = $request->all();

        try {
            $order = Order::where('id',$req['order_id'])->update($req);

            return response()->json(['message'=>"Order has been updated", 'data'=> $order],200);

        }catch (\Exception $exception){
            return response()->json(['message'=>"Failed to update order at this moment"]);

        }

    }



    /* Handles batch order update by the invoice_number*/

    public function UpdateOrder(Request $request){
        $req = $request->all();

        $payload = [
            'status' => $req['status']
        ];

        try {
            $orders = tap(Order::where('invoice_number',$req['order_id']))->update($payload)->get();

            if ($req['status'] == 'confirmed'){
                broadcast(new OrderConfirmed($req['user_id'],$orders))->toOthers();
            }


            return response()->json(['message'=>"Order has been updated", 'data'=> $orders],200);

        }catch (\Exception $exception){
            return response()->json(['message'=>"Failed to update order at this moment"]);

        }

    }
    public function RefundOrder(Request $request){
        $req = $request->all();

        $payload = [
            'refund' => $req['refund']
        ];

        try {
            $order = Order::where('invoice_number',$req['order_id'])->update($payload);
            return response()->json(['message'=>"Order has been updated", 'data'=> $order],200);

        }catch (\Exception $exception){
            return response()->json(['message'=>"Failed to update order at this moment"]);

        }

    }


    public function addCategory(Request $request){
        $req = $request->all();

        if ($request->hasFile('category_image')){
            $path = $request->file('category_image')->store('category','public');

            $req['category_image'] = $path;
        }

        try {
            $category = Category::create($req);

            return response()->json(['message'=>"{$req['title']} has been added successfully", 'data'=> $category],200);

        }catch (\Exception $exception){
            return response()->json(['message'=>"Failed to add {$req['title']} at this moment",'error'=>$exception]);

        }

    }

    public function updateCategory(Request $request){
        $req = $request->all();


        if ($request->hasFile('category_image')){
            $path = $request->file('category_image')->store('category','public');

            $req['category_image'] = $path;
        }

        try {
            $category = Category::where('id',$req['category_id'])->first();

            $category_image = $category->category_image;


            $updated = $category->update($req);

            if ($updated && $request->hasFile('category_image')){
                Storage::disk('public')->delete($category_image);
            }


            return response()->json(['message'=>"Category has been updated", 'data'=> $category],200);

        }catch (Exception $exception){
            return response()->json(['message'=>"Failed to update category at this moment",'error'=>$exception]);

        }

    }




    public function addBrand(Request $request){
        $req = $request->all();

        if ($request->hasFile('brand_image')){
            $path = $request->file('brand_image')->store('brand','public');

            $req['brand_image'] = $path;
        }

        try {
            $brand = Brand::create($req);

            return response()->json(['message'=>"{$req['title']} has been added successfully", 'data'=> $brand],200);

        }catch (\Exception $exception){
            return response()->json(['message'=>"Failed to add {$req['title']} at this moment",'error'=>$exception]);

        }

    }

    public function updateBrand(Request $request){
        $req = $request->all();


        if ($request->hasFile('brand_image')){
            $path = $request->file('brand_image')->store('brand','public');

            $req['brand_image'] = $path;
        }

        try {
            $brand = Brand::where('id',$req['brand_id'])->first();

            $brand_image = $brand->brand_image;


            $updated = $brand->update($req);

            if ($updated && $request->hasFile('brand_image')){
                Storage::disk('public')->delete($brand_image);
            }


            return response()->json(['message'=>"Category has been updated", 'data'=> $brand],200);

        }catch (Exception $exception){
            return response()->json(['message'=>"Failed to update category at this moment",'error'=>$exception]);

        }

    }

    public function updateResponse(Request $request){
        $req = $request->all();



        try {
            $responseUpdate = Response::where('id',$req['response_id'])->update([
                'title' => $req['title'],
                'description' => $req['description'],
            ]);



            return response()->json(['message'=>"Product has been updated", 'data'=> $responseUpdate],200);

        }catch (Exception $exception){
            return response()->json(['message'=>"Failed to update product at this moment",'error'=>$exception]);

        }

    }



    public function deleteProduct(Request $request){
        $req = $request->all();



        try {
            $deleteProd = Product::where('id',$req['product_id'])->first();

            if ($deleteProd){

                Storage::disk('public')->delete($deleteProd->product_image);
                $deleteProd->delete();
            }

            return response()->json(['message'=>"Product has been deleted", 'data'=> $deleteProd],200);

        }catch (Exception $exception){
            return response()->json(['message'=>"Failed to update product at this moment",'error'=>$exception]);

        }

    }


    public function deleteCategory(Request $request){
        $req = $request->all();



        try {
            $deleteCarte = Category::where('id',$req['category_id'])->first();

            if ($deleteCarte){

                Storage::disk('public')->delete($deleteCarte->category_image);
                $deleteCarte->delete();
            }

            return response()->json(['message'=>"Category has been deleted", 'data'=> $deleteCarte],200);

        }catch (Exception $exception){
            return response()->json(['message'=>"Failed to update product at this moment",'error'=>$exception]);

        }

    }


    public function deleteBrand(Request $request){
        $req = $request->all();


        try {
            $deleteBrand = Brand::where('id',$req['brand_id'])->first();

            if ($deleteBrand){

                Storage::disk('public')->delete($deleteBrand->brand_image);
                $deleteBrand->delete();
            }

            return response()->json(['message'=>"Brand has been deleted", 'data'=> $deleteBrand],200);

        }catch (Exception $exception){
            return response()->json(['message'=>"Failed to update product at this moment",'error'=>$exception]);

        }

    }





}
