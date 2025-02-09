<?php

namespace App\Http\Controllers;

use App\Mail\LostShipmentNotification;
use App\Models\Shipment;
use App\Models\ShipmentEvent;
use App\Services\ShipmentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class ShipmentWebhookController extends Controller
{
    /**
     * @var ShipmentService
     */
    protected ShipmentService $shipmentService;

    /**
     * @param ShipmentService $shipmentService
     */
    public function __construct(ShipmentService $shipmentService)
    {
        $this->shipmentService = $shipmentService;
    }

    /**
     * Submit incoming Shippo webhook events.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function submitShippo(Request $request): JsonResponse
    {
        // TODO: verify signature (Inbound IP allowlist, Self-generated tokens or HMAC)

        $payload = $request->json()->all();

        if (!$this->isValidEvent($payload)) {
            return response()->json(['error' => 'Invalid event type or missing data payload'], 400);
        }

        $trackingNumber = $payload['data']['tracking_number'] ?? null;
        if (!$trackingNumber) {
            return response()->json(['error' => 'Invalid payload: missing tracking_number'], 400);
        }

        $mappedStatus = $this->shipmentService->mapStatus(($payload['data']['tracking_status']['status']));

        $shipment = Shipment::updateOrCreate(
            ['tracking_number' => $trackingNumber],
            ['current_status' => $mappedStatus, 'payload' => $payload['data']]
        );

        ShipmentEvent::create([
            'shipment_id' => $shipment->id,
            'event_status' => $mappedStatus,
            'payload' => $payload['data'],
        ]);

        if ($mappedStatus === 'Lost') {
            Mail::to('customer@example.com')->send(new LostShipmentNotification($payload['data']));
        }

        Log::info("Received Shippo webhook for tracking number {$trackingNumber} with status {$mappedStatus}");

        return response()->json(['success' => true]);
    }

    /**
     * Validate the webhook event structure.
     *
     * @param array $payload
     * @return bool
     */
    private function isValidEvent(array $payload): bool
    {
        return isset($payload['event']) && $payload['event'] === 'track_updated' && isset($payload['data']);
    }
}
