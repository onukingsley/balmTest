<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Complaint extends Model
{
    protected $fillable = ['title', 'complain', 'status', 'user_id','order_id'];

    public function response(){
        return $this->hasMany(Response::class);
    }

    public function user(){
        return $this->belongsTo(User::class);
    }

}
