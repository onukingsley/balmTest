<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\Authentication;
use App\Http\Controllers\PaystackController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get('/test', function (Request $request) {
    return response()->json(['message'=>'test']);
});

/*Authentication Routes*/
Route::post('/login',[Authentication::class,'login']);
Route::post('/logout',[Authentication::class,'logout'])->middleware("auth:sanctum");
Route::post('/register',[Authentication::class,'register']);

/*User Page Routes*/
Route::get('/getProduct',[UserController::class,'getProduct']);
Route::get('/getOverallIndex',[UserController::class,'getOverallIndex'])->middleware('auth:sanctum');
Route::post('/addCart',[UserController::class,'addCart'])->middleware('auth:sanctum');
Route::post('/updateCart',[UserController::class,'updateCart'])->middleware('auth:sanctum');
Route::post('/deleteCart',[UserController::class,'deleteCart'])->middleware('auth:sanctum');
Route::post('/bulkAddCart',[UserController::class,'bulkAdd'])->middleware('auth:sanctum');
Route::post('/addOrder',[UserController::class,'addOrder'])->middleware('auth:sanctum');
Route::post('/addComplain',[UserController::class,'addComplain'])->middleware('auth:sanctum');
Route::post('/addReview',[UserController::class,'addReview'])->middleware('auth:sanctum');
Route::post('/updateProfile',[UserController::class,'updateProfile'])->middleware('auth:sanctum');
Route::post('/cancelOrder',[UserController::class,'updateOrder'])->middleware('auth:sanctum');

/*PayStack Endpoint*/
Route::post('/initiatePayment',[PaystackController::class,'initiatePayment'])->middleware('auth:sanctum');
Route::get('/paymentCallback',[PaystackController::class,'paymentCallback']);



/* Admin page Routes*/
Route::get('/getOverallAdmin',[AdminController::class,'getOverallAdmin'])->middleware('auth:sanctum');
Route::post('/addResponse',[AdminController::class,'addResponse'])->middleware('auth:sanctum');
Route::post('/addProduct',[AdminController::class,'addProduct'])->middleware('auth:sanctum');
Route::post('/addCategory',[AdminController::class,'addCategory'])->middleware('auth:sanctum');
Route::post('/addBrand',[AdminController::class,'addBrand'])->middleware('auth:sanctum');
Route::post('/updateProduct',[AdminController::class,'updateProduct'])->middleware('auth:sanctum');
Route::post('/updateCategory',[AdminController::class,'updateCategory'])->middleware('auth:sanctum');
Route::post('/updateBrand',[AdminController::class,'updateBrand'])->middleware('auth:sanctum');
Route::post('/updateResponse',[AdminController::class,'updateResponse'])->middleware('auth:sanctum');
Route::post('/updateUser',[AdminController::class,'updateUser'])->middleware('auth:sanctum');
Route::post('/updateSingleOrder',[AdminController::class,'updateSingleOrder'])->middleware('auth:sanctum');
Route::post('/updateOrder',[AdminController::class,'UpdateOrder'])->middleware('auth:sanctum');
Route::post('/refundOrder',[AdminController::class,'RefundOrder'])->middleware('auth:sanctum');

/* route to delete product, category and brand*/
Route::post('/deleteProduct',[AdminController::class,'deleteProduct'])->middleware('auth:sanctum');
Route::post('/deleteCategory',[AdminController::class,'deleteCategory'])->middleware('auth:sanctum');
Route::post('/deleteBrand',[AdminController::class,'deleteBrand'])->middleware('auth:sanctum');


