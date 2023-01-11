<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Institution extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $guarded = [];

    public function getDateForHumansAttribute() { return $this->created_at->diffForHumans(); }

    public function getNameContractAttribute() { return $this->name.' / '.$this->contract; }

    // Initialise
    function __construct() { $this->wp = false; }
}
