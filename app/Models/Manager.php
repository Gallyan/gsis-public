<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Manager extends Model
{
    use HasFactory;

    protected $with = ['user'];

    protected $guarded = [];

    public function getNameAttribute()
    {
        return $this->user->name;
    }

    public function user()
    {
        return $this->belongsTo(\App\Models\User::class);
    }

    // A manager can manage ;-)
    public function manageable()
    {
        return $this->morphTo();
    }
}
