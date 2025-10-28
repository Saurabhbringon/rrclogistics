<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Vehicle extends Model
{
    use HasFactory;

    protected $fillable = [
        'vehical_number',
        'company',
        'vehical_series',
        'fuel_type',
        'status'
    ];

    protected $attributes = [
        'status' => '1', // Default to active
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

    public function scopeByCompany($query, $company)
    {
        return $query->where('company', $company);
    }

    public function scopeByFuelType($query, $fuelType)
    {
        return $query->where('fuel_type', $fuelType);
    }
}
