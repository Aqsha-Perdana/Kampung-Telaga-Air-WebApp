<?php

namespace App\Mail;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class VisitorRefundStatusMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Order $order,
        public string $statusLabel,
        public string $messageText
    ) {
    }

    public function build(): self
    {
        return $this->subject('Refund Update - ' . $this->order->id_order)
            ->view('emails.refund-status')
            ->with([
                'order' => $this->order,
                'statusLabel' => $this->statusLabel,
                'messageText' => $this->messageText,
            ]);
    }
}
