<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    use HasFactory;

    protected $with = ['user'];

    protected $guarded = [];

    /**
     * Get all of the owning postable models.
     */
    public function postable()
    {
        return $this->morphTo();
    }

    public function getAuthorAttribute() { return $this->user->name; }

    /**
     * Get the user that owns the phone.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
