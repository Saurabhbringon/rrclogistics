<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MileageRate extends Model
{
    use HasFactory;

    protected $fillable = [
        'vehicle_series',
        'load_from',
        'load_to',
        'mileage_rate',
        'status'
    ];

    protected $casts = [
        'load_from' => 'decimal:2',
        'load_to' => 'decimal:2',
        'mileage_rate' => 'decimal:2',
    ];

    /**
     * Scope to filter active rates
     */
    public function scopeActive($query)
    {
        return $query->where('status', 1);
    }

    /**
     * Scope to filter by vehicle series
     */
    public function scopeBySeries($query, $series)
    {
        return $query->where('vehicle_series', $series);
    }

    /**
     * Find mileage rate for a specific vehicle series and load
     */
    public static function findRateForLoad($vehicleSeries, $loadCategory)
    {
        return self::active()
            ->where('vehicle_series', $vehicleSeries)
            ->where('load_from', '<=', $loadCategory)
            ->where(function ($query) use ($loadCategory) {
                $query->whereNull('load_to')
                    ->orWhere('load_to', '>=', $loadCategory);
            })
            ->orderBy('load_from', 'desc')
            ->first();
    }
}
