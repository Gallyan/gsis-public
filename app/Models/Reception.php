<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Reception extends Model
{
    use HasFactory;

     // Automatically switch between json and array
    protected $casts = [
        'guests' => 'array',
    ];

    protected $guarded = [];

    public function purchase() { return $this->belongsTo('App\Models\Purchase'); }
}
