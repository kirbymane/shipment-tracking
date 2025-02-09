<?php

namespace App\Services;

use App\Models\Shipment;
use App\Models\ShipmentEvent;

/**
 * ShipmentService
 */
class ShipmentService
{
    /**
     * Map Shippo's status values to internal statuses.
     *
     * @param string $rawStatus
     *
     * @return string
     */
    function mapStatus(string $rawStatus): string
    {
        return [
            'UNKNOWN' => 'Created',
            'TRANSIT' => 'In Transit',
            'DELIVERED' => 'Delivered',
            'LOST' => 'Lost',
            'CANCELED' => 'Canceled',
        ][$rawStatus] ?? $rawStatus;
    }

    /**
     * @param Shipment $shipment
     * @param array $trackingData
     *
     * @return bool
     */
    function hasNoNewUpdates(Shipment $shipment, array $trackingData): bool
    {
        $latestEvent = ShipmentEvent::where('shipment_id', $shipment->id)
            ->latest()
            ->first();

        return $latestEvent && $latestEvent->payload['tracking_history'] === ($trackingData['tracking_history'] ?? []);
    }
}
