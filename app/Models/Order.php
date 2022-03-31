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
        'draft'       => 'Draft',
        'on-hold'     => 'On hold',
        'in-progress' => 'In progress',
        'processed'   => 'Processed',
        'cancelled'   => 'Cancelled',
    ];

    const EDITION = [
        'paper'   => 'Paper book',
        'digital' => 'Digital book',
    ];

    // Automatically switch between json and array of books
    protected $casts = [
        'quotations' => 'array',
        'books' => 'array',
    ];

    protected $guarded = [];

    protected $fillable = [
        'books',
        'quotations',
        'user_id',
    ];

    public function user() { return $this->belongsTo('App\Models\User'); }

    public function institution() { return $this->belongsTo('App\Models\Institution'); }

    public function getDateForHumansAttribute() { return $this->created_at->diffForHumans(); }

    public function getAllStatusesAttribute() { return Order::STATUSES; }

    public function getAllEditionsAttribute() { return Order::EDITION; }

    public function getDisabledStatusesAttribute() {
        return [];
        // @ToDo: Développer la gestion des status ici
    }

    /**
     * Get all of the order's documents.
     */
    public function documents()
    {
        return $this->morphMany(Document::class, 'documentable');
    }
}
