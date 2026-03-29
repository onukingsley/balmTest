<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $fillable = ['title','description','category_image'];

    public function product(){
        return $this->hasMany(Product::class);
    }
}
