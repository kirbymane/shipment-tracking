<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * ShipmentEvent
 */
class ShipmentEvent extends Model
{
    /**
     * @var string[]
     */
    protected $fillable = [
        'shipment_id',
        'event_status',
        'payload',
    ];

    /**
     * @var string[]
     */
    protected $casts = [
        'payload' => 'array',
    ];

    /**
     * Each event belongs to a shipment.
     */
    public function shipment(): BelongsTo
    {
        return $this->belongsTo(Shipment::class);
    }
}
