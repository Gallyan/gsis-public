<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Purchase extends Model
{
    use HasFactory;

    const STATUSES = [
        'draft' => 'Draft',
        'on-hold' => 'On hold',
        'in-progress' => 'In progress',
        'processed' => 'Processed',
        'cancelled' => 'Cancelled',
    ];

    // Automatically switch between json and array of misc
    protected $casts = [
        'miscs' => 'array',
    ];

    protected $guarded = [];

    protected $fillable = [
        'miscs',
        'user_id',
        'wp',
        'status',
        'amount',
    ];

    public function user()
    {
        return $this->belongsTo(\App\Models\User::class);
    }

    public function institution()
    {
        return $this->belongsTo(\App\Models\Institution::class);
    }

    public function getDateForHumansAttribute()
    {
        return $this->created_at->diffForHumans();
    }

    public function getAllStatusesAttribute()
    {
        return Purchase::STATUSES;
    }

    public function getAllWPAttribute()
    {
        return Purchase::STATUSES;
    }

    /**
     * Get all of the order's documents.
     */
    public function documents()
    {
        return $this->morphMany(Document::class, 'documentable');
    }

    /**
     * Get all of the order's managers.
     */
    public function managers()
    {
        return $this->morphMany(Manager::class, 'manageable');
    }

    /**
     * Get all of the order's posts.
     */
    public function posts()
    {
        return $this->morphMany(Post::class, 'postable')->orderBy('id', 'desc');
    }

    /**
     * Get all of the purchase's reception.
     */
    public function receptions()
    {
        return $this->hasMany(Reception::class);
    }
}
