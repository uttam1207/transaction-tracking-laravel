<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MachineMaintenance extends Model
{
    use HasFactory;

    protected $fillable = [
        'machine_name',
        'maintenance_type',
        'service_date',
        'cost',
        'serviced_by',
        'description',
        'next_service_due',
    ];

    protected $casts = [
        'service_date' => 'date',
        'next_service_due' => 'date',
        'cost' => 'decimal:2',
    ];
}
