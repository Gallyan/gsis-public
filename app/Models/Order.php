<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Order extends Model
{
    use HasFactory;
    use SoftDeletes;

    const STATUSES = [
        'on-hold' => 'On hold',
        'in-progress' => 'In progress',
        'validated' => 'Validated',
        'processes' => 'Processed',
    ];

    protected $guarded = [];

    public function user() { return $this->belongsTo('App\Models\User'); }

    public function institution() { return $this->belongsTo('App\Models\Institution'); }

    public function getDateForHumansAttribute() { return $this->created_at->diffForHumans(); }

}
