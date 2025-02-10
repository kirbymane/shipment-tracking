<?php

namespace App\Http\Controllers;

use App\Mail\LostShipmentNotification;
use App\Models\Shipment;
use App\Models\ShipmentEvent;
use App\Services\ShipmentService;
use App\Services\ShippoService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

/**
 * ShipmentController
 */
class ShipmentController extends Controller
{
    /**
     * @param ShippoService $shippoService
     * @param ShipmentService $shipmentService
     */
    public function __construct(
        protected ShippoService   $shippoService,
        protected ShipmentService $shipmentService
    )
    {
    }

    /**
     * @param Request $request
     * @param string $trackingNumber
     * @return JsonResponse
     */
    public function get(Request $request, string $trackingNumber): JsonResponse
    {
        try {
            $trackingData = $this->shippoService->trackShipment($trackingNumber);
            $mappedStatus = $this->shipmentService->mapStatus($trackingData['tracking_status']['status']);

            $shipment = Shipment::where('tracking_number', $trackingNumber)->first();

            if ($shipment && $this->shipmentService->hasNoNewUpdates($shipment, $trackingData)) {
                return response()->json([
                    'tracking_number' => $trackingNumber,
                    'status' => $mappedStatus,
                    'message' => 'No new updates',
                ]);
            }

            $shipment = Shipment::updateOrCreate(
                ['tracking_number' => $trackingNumber],
                ['current_status' => $mappedStatus, 'payload' => $trackingData]
            );

            ShipmentEvent::create([
                'shipment_id' => $shipment->id,
                'event_status' => $mappedStatus,
                'payload' => $trackingData,
            ]);

            if ($mappedStatus === 'Lost') {
                Mail::to('customer@example.com')->send(new LostShipmentNotification($trackingNumber));
            }

            return response()->json([
                'tracking_number' => $trackingNumber,
                'status' => $mappedStatus,
                'data' => $trackingData,
            ]);
        } catch (Throwable $e) {
            Log::error("Error tracking shipment $trackingNumber: " . $e->getMessage());

            $shipment = Shipment::where('tracking_number', $trackingNumber)->first();

            if ($shipment) {
                return response()->json([
                    'tracking_number' => $trackingNumber,
                    'status' => $shipment->current_status,
                    'message' => 'Using last known status from local storage.',
                    'data' => $shipment->payload,
                ]);
            }

            return response()->json(['error' => 'Service unavailable'], 503);
        }
    }
}
