<?php

namespace App\Mail;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class VisitorOrderPaidMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Order $order,
        private readonly string $invoicePdf
    ) {
    }

    public function build(): self
    {
        return $this->subject('Booking Confirmed - ' . $this->order->id_order)
            ->view('emails.order-paid')
            ->with([
                'order' => $this->order,
            ])
            ->attachData(
                $this->invoicePdf,
                'Invoice-' . $this->order->id_order . '.pdf',
                ['mime' => 'application/pdf']
            );
    }
}
