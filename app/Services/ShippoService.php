<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * ShippoService
 */
class ShippoService
{
    /**
     * @var string
     */
    protected string $shippoApiToken;

    /**
     * Construct
     */
    public function __construct()
    {
        $this->shippoApiToken = config('services.shippo.token');
    }

    /**
     * Fetch tracking data from Shippo API.
     *
     * @param string $trackingNumber
     * @return array
     * @throws Exception
     */
    public function trackShipment(string $trackingNumber): array
    {
        try {
            if (Config::get('app.env') === 'dev') {
                $trackingNumber = 'SHIPPO_TRANSIT';
            }

            $response = Http::withToken($this->shippoApiToken, 'ShippoToken')
                ->post("https://api.goshippo.com/tracks/", [
                    'carrier' => 'shippo',
                    'tracking_number' => $trackingNumber,
                ]);

            if ($response->failed()) {
                throw new Exception('Unable to fetch tracking data');
            }

            return $response->json();
        } catch (Exception $e) {
            Log::error("Shippo API error for tracking number $trackingNumber: " . $e->getMessage());

            throw new Exception("Tracking service unavailable.");
        }
    }
}
