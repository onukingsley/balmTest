<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Brand extends Model
{
    protected $fillable = ['title','description','brand_image'];

    public function product(){
        return $this->hasMany(Product::class);
    }
}
