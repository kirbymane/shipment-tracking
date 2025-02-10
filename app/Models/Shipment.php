<?php

namespace App\Models;

use Database\Factories\ShipmentFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Shipment
 */
class Shipment extends Model
{
    /** @use HasFactory<ShipmentFactory> */
    use HasFactory;

    /**
     * @var string[]
     */
    protected $fillable = [
        'tracking_number',
        'current_status',
        'payload',
    ];

    /**
     * @var string[]
     */
    protected $casts = [
        'payload' => 'array',
    ];

    /**
     * A shipment can have many events.
     */
    public function events(): HasMany
    {
        return $this->hasMany(ShipmentEvent::class);
    }
}
