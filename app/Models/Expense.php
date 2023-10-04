<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Expense extends Model
{
    use HasFactory;

    const STATUSES = [
        'draft' => 'Draft',
        'on-hold' => 'On hold',
        'in-progress' => 'In progress',
        'processed' => 'Processed',
    ];

    const TRANSPORTS = [
        'train' => 'Train',
        'flight' => 'Flight',
        'public' => 'Public transport',
        'taxi' => 'Taxi',
        'personal' => 'Personal car',
    ];

    // Automatically switch between json and array
    protected $casts = [
        'actual_costs_meals' => 'array',
        'transports' => 'array',
        'hotels' => 'array',
        'registrations' => 'array',
        'miscs' => 'array',
    ];

    protected $guarded = [];

    public function user()
    {
        return $this->belongsTo('App\Models\User');
    }

    public function mission()
    {
        return $this->belongsTo('App\Models\Mission');
    }

    public function getAllStatusesAttribute()
    {
        return Expense::STATUSES;
    }

    /**
     * Get all of the mission's documents.
     */
    public function documents()
    {
        return $this->morphMany(Document::class, 'documentable');
    }

    /**
     * Get all of the expense's posts.
     */
    public function posts()
    {
        return $this->morphMany(Post::class, 'postable')->orderBy('id', 'desc');
    }
}
