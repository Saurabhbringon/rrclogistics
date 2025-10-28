<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Distance extends Model
{
    use HasFactory;

    protected $fillable = [
        'from_location',
        'to_location',
        'trip_name',
        'distance',
        'status'
    ];

    protected $attributes = [
        'status' => '1', // Default to active
    ];

    protected $casts = [
        'distance' => 'decimal:2',
    ];

    // Scopes for filtering
    public function scopeActive($query)
    {
        return $query->where('status', '1');
    }

    public function scopeInactive($query)
    {
        return $query->where('status', '0');
    }

    public function scopeFromLocation($query, $location)
    {
        return $query->where('from_location', 'like', '%' . $location . '%');
    }

    public function scopeToLocation($query, $location)
    {
        return $query->where('to_location', 'like', '%' . $location . '%');
    }

    public function scopeByTrip($query, $trip)
    {
        return $query->where('trip_name', 'like', '%' . $trip . '%');
    }

    // Accessor to format distance with unit
    public function getFormattedDistanceAttribute()
    {
        return $this->distance . ' KM';
    }

    // Method to find distance between two locations
    public static function findDistance($from, $to)
    {
        return self::where('from_location', $from)
            ->where('to_location', $to)
            ->first();
    }

    // Method to find reverse route distance
    public static function findReverseDistance($from, $to)
    {
        return self::where('from_location', $to)
            ->where('to_location', $from)
            ->first();
    }
}
