<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = ['product_image', 'discount_price', 'price','title','quantity','description','status','category_id','brand_id'];


    public function cart(){
        return $this->hasMany(Cart::class);
    }

    public function order(){
        return $this->hasMany(Order::class);
    }

    public function review(){
        return $this->hasMany(Review::class);
    }

    /*belong to relationships*/

    public function brand(){
        return $this->belongsTo(Brand::class);
    }

    public function category(){
    return $this->belongsTo(Category::class);
}



}
