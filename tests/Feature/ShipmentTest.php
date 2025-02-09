<?php

namespace Tests\Feature;

use App\Mail\LostShipmentNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestCase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use PHPUnit\Framework\Attributes\Test;

class ShipmentTest extends TestCase
{
    use RefreshDatabase;

    #[Test] public function it_returns_tracking_info_for_valid_tracking_number(): void
    {
        Http::fake([
            'https://api.goshippo.com/tracks/*' => Http::response([
                'tracking_number' => 'TEST123',
                'tracking_status' => ['status' => 'In Transit'],
            ], 200),
        ]);

        $response = $this->getJson('/api/shipments/TEST123');

        $response->assertStatus(200)
            ->assertJson([
                'tracking_number' => 'TEST123',
                'status' => 'In Transit'
            ]);

        $this->assertDatabaseHas('shipments', [
            'tracking_number' => 'TEST123',
            'current_status' => 'In Transit'
        ]);

        $this->assertDatabaseHas('shipment_events', [
            'event_status' => 'In Transit',
        ]);
    }

    #[Test] public function it_returns_error_for_invalid_tracking_number(): void
    {
        Http::fake([
            'https://api.goshippo.com/tracks/' => Http::response([], 404),
        ]);

        $response = $this->getJson('/api/shipments/INVALID123');

        $response->assertStatus(503)
            ->assertJson(['error' => 'Service unavailable']);
    }

    #[Test] public function it_does_not_duplicate_events_if_status_has_not_changed(): void
    {
        Http::fake([
            'https://api.goshippo.com/tracks/*' => Http::response([
                'tracking_number' => 'TEST123',
                'tracking_status' => ['status' => 'In Transit'],
            ], 200),
        ]);

        $this->getJson('/api/shipments/TEST123');
        $this->getJson('/api/shipments/TEST123');

        $this->assertDatabaseCount('shipment_events', 1);
    }

    #[Test] public function it_triggers_email_for_lost_shipments(): void
    {
        Mail::fake();
        Http::fake([
            'https://api.goshippo.com/tracks/*' => Http::response([
                'tracking_number' => 'TEST123',
                'tracking_status' => ['status' => 'Lost'],
            ], 200),
        ]);

        $this->getJson('/api/shipments/TEST123');

        Mail::assertSent(LostShipmentNotification::class, function ($mail) {
            return $mail->hasTo('customer@example.com');
        });
    }
}
