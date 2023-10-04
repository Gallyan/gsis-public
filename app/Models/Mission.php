<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Mission extends Model
{
    use HasFactory;

    const STATUSES = [
        'draft' => 'Draft',
        'on-hold' => 'On hold',
        'in-progress' => 'In progress',
        'processed' => 'Processed',
        'cancelled' => 'Cancelled',
    ];

    protected $casts = [
        'hotels' => 'array', // Automatically switch between json and array
        'departure' => 'date:Y-m-d',
        'return' => 'date:Y-m-d',
        'tickets' => 'array', // Automatically switch between json and array
    ];

    protected $guarded = [];

    public function user()
    {
        return $this->belongsTo('App\Models\User');
    }

    public function institution()
    {
        return $this->belongsTo('App\Models\Institution');
    }

    public function expense()
    {
        return $this->hasOne('App\Models\Expense');
    }

    public function getDateForHumansAttribute()
    {
        return $this->created_at->diffForHumans();
    }

    public function getAllStatusesAttribute()
    {
        return Mission::STATUSES;
    }

    public function getProgrammeAttribute()
    {
        return $this->morphMany(
            Document::class,
            'documentable')
            ->where('type', '=', 'programme')
            ->first();
    }

    /**
     * Get all of the mission's documents.
     */
    public function documents()
    {
        return $this->morphMany(Document::class, 'documentable');
    }

    /**
     * Get all of the mission's managers.
     */
    public function managers()
    {
        return $this->morphMany(Manager::class, 'manageable');
    }

    /**
     * Get all of the mission's posts.
     */
    public function posts()
    {
        return $this->morphMany(Post::class, 'postable')->orderBy('id', 'desc');
    }
}
