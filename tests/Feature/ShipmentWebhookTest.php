<?php

namespace Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ShipmentWebhookTest extends TestCase
{
    use RefreshDatabase;

    #[Test] public function it_handles_invalid_webhook_payload()
    {
        $response = $this->postJson('/api/webhooks/shippo', [
            'event' => 'wrong_event'
        ]);

        $response->assertStatus(400)
            ->assertJson(['error' => 'Invalid event type or missing data payload']);
    }

    #[Test] public function it_processes_valid_webhook_payload()
    {
        $payload = [
            'event' => 'track_updated',
            'data' => [
                'tracking_number' => 'WEBHOOK123',
                'tracking_status' => ['status' => 'Delivered'],
            ],
        ];

        $response = $this->postJson('/api/webhooks/shippo', $payload);

        $response->assertStatus(200)
            ->assertJson(['success' => true]);

        $this->assertDatabaseHas('shipments', [
            'tracking_number' => 'WEBHOOK123',
            'current_status' => 'Delivered',
        ]);

        $this->assertDatabaseHas('shipment_events', [
            'event_status' => 'Delivered',
        ]);
    }
}
