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

    // Override for cascade deletion
    public function delete()
    {
        $doc = $this->guestslist;

        $res = parent::delete();
        if ( $res === true )
        {
            $doc->delete();
        }
    }

    /**
     * Get the purchase of the reception.
     */
    public function purchase() {
        return $this->belongsTo('App\Models\Purchase');
    }

    /**
     * Get all of the user's documents.
     */
    public function guestslist()
    {
        return $this->morphOne(Document::class, 'documentable');
    }

}
