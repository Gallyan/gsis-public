<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;

class Institution extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $guarded = [];

    /**
     * The model's default values for attributes.
     *
     * @var array
     */
    protected $attributes = [
        'wp' => false,
    ];

    protected $casts = [
        'from' => 'date:Y-m-d',
        'to' => 'date:Y-m-d',
    ];

    public function setFromAttribute($date)
    {
        $this->attributes['from'] = empty($date) ? null : Carbon::parse($date)->format('Y-m-d');
    }

    public function getFromFormattedAttribute()
    {
        return $this->attributes['from'] ? Carbon::parse($this->attributes['from'])->format('d/m/Y') : '';
    }

    public function setToAttribute($date)
    {
        $this->attributes['to'] = empty($date) ? null : Carbon::parse($date)->format('Y-m-d');
    }

    public function getToFormattedAttribute()
    {
        return $this->attributes['to'] ? Carbon::parse($this->attributes['to'])->format('d/m/Y') : '';
    }

    public function getDateForHumansAttribute()
    {
        return $this->created_at->diffForHumans();
    }

    public function getNameContractAttribute()
    {
        return $this->name.' / '.$this->contract;
    }

    public static function available()
    {
        return Cache::remember('instit_avail', 60, function () {
            return Institution::where(
                function ($query) {
                    $query->where('from', '<=', Carbon::today())
                        ->orWhereNull('from');
                }
            )
                ->where(
                    function ($query) {
                        $query->where('to', '>=', Carbon::today())
                            ->orWhereNull('to');
                    }
                )
                ->orderBy('name')
                ->orderBy('contract')
                ->get();
        });
    }

    public static function unavailable()
    {
        return Cache::remember('instit_unavail', 60, function () {
            return Institution::where('from', '>', Carbon::today())
                              ->orWhere('to', '<', Carbon::today())
                ->orderBy('name')
                ->orderBy('contract')
                ->get();
        });
    }
}
