<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Response extends Model
{
    protected $fillable = ['title','description', 'complaint_id'];


    public function complaint (){
        return $this->belongsTo(Complaint::class);
    }
}
