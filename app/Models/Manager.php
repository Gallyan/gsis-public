<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Manager extends Model
{
    use HasFactory;

    protected $guarded = [];

    // A manager can manage ;-)
    public function manageable()
    {
        return $this->morphTo();
    }

}
