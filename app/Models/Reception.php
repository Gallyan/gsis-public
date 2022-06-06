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
        'date' => 'date:Y-m-d',
    ];

    // Propager le update_at à l'achat
    protected $touches = ['purchase'];

    protected $guarded = [];

    public function purchase() { return $this->belongsTo('App\Models\Purchase'); }
}
