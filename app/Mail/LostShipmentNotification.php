<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

/**
 * LostShipmentNotification
 */
class LostShipmentNotification extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * @var string
     */
    public string $trackingNumber;

    /**
     * @param string $trackingNumber
     */
    public function __construct(string $trackingNumber)
    {
        $this->trackingNumber = $trackingNumber;
    }

    /**
     * @return LostShipmentNotification
     */
    public function build(): LostShipmentNotification
    {
        return $this->subject('Shipment Lost Notification')
            ->markdown('emails.shipments.lost')
            ->with(['trackingNumber' => $this->trackingNumber]);
    }
}
