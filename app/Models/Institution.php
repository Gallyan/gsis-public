<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

class Institution extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $guarded = [];

    public function getDateForHumansAttribute() { return $this->created_at->diffForHumans(); }

    public function getNameContractAttribute() { return $this->name.' / '.$this->contract; }

    public static function available() {
        return Institution::where(function($query) {
                $query->where('from', '<=', Carbon::today())
                      ->orWhereNull('from');
            })
            ->where(function($query) {
                $query->where('to', '>=', Carbon::today())
                      ->orWhereNull('to');
            })
            ->get();
    }

    // Initialise
    function __construct() { $this->wp = false; }
}
