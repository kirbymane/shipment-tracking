<?php

namespace Tests\Unit;

use App\Services\ShippoService;
use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ShippoServiceTest extends TestCase
{
    #[Test] public function it_returns_tracking_data_for_a_valid_tracking_number()
    {
        Http::fake([
            'https://api.goshippo.com/tracks/' => Http::response([
                'tracking_number' => 'VALID123',
                'tracking_status' => ['status' => 'In Transit'],
            ], 200),
        ]);

        $service = new ShippoService();
        $trackingData = $service->trackShipment('VALID123');

        $this->assertEquals('In Transit', $trackingData['tracking_status']['status']);
    }

    #[Test] public function it_throws_exception_for_invalid_tracking_number()
    {
        Http::fake([
            'https://api.goshippo.com/tracks/' => Http::response([], 404),
        ]);

        $service = new ShippoService();

        $this->expectException(Exception::class);
        $this->expectExceptionMessage("Tracking service unavailable.");

        $service->trackShipment('INVALID123');
    }

    #[Test] public function it_logs_an_error_when_shippo_api_fails()
    {
        Http::fake([
            'https://api.goshippo.com/tracks/' => Http::response([], 500),
        ]);

        Log::shouldReceive('error')
            ->once()
            ->withArgs(fn($message) => str_contains($message, "Shippo API error"));

        $service = new ShippoService();

        $this->expectException(Exception::class);
        $this->expectExceptionMessage("Tracking service unavailable.");

        $service->trackShipment('ERROR123');
    }
}
