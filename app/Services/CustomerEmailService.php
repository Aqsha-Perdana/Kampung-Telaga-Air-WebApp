<?php

namespace App\Services;

use App\Mail\VisitorOrderPaidMail;
use App\Mail\VisitorRefundStatusMail;
use App\Models\Order;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class CustomerEmailService
{
    public function sendOrderPaid(Order $order): void
    {
        if (!$this->canSendTo($order)) {
            return;
        }

        try {
            $order->loadMissing('items');

            $invoicePdf = Pdf::loadView('pdf.invoice', [
                'order' => $order,
            ])->output();

            Mail::to($order->customer_email)->send(new VisitorOrderPaidMail($order, $invoicePdf));
        } catch (\Throwable $e) {
            Log::error('Failed sending order confirmation email', [
                'order_id' => $order->id_order,
                'email' => $order->customer_email,
                'error' => $e->getMessage(),
            ]);
        }
    }

    public function sendRefundApproved(Order $order): void
    {
        $this->sendRefundStatus(
            $order,
            'Refund Approved',
            'Your refund has been approved and processed successfully. The refund amount and fee details are included below.'
        );
    }

    public function sendRefundRejected(Order $order): void
    {
        $message = 'Your refund request was reviewed by our team and could not be approved.';

        if (!empty($order->refund_rejected_reason)) {
            $message .= ' Reason: ' . $order->refund_rejected_reason;
        }

        $this->sendRefundStatus($order, 'Refund Rejected', $message);
    }

    private function sendRefundStatus(Order $order, string $statusLabel, string $messageText): void
    {
        if (!$this->canSendTo($order)) {
            return;
        }

        try {
            $order->loadMissing('items');

            Mail::to($order->customer_email)->send(
                new VisitorRefundStatusMail($order, $statusLabel, $messageText)
            );
        } catch (\Throwable $e) {
            Log::error('Failed sending refund status email', [
                'order_id' => $order->id_order,
                'email' => $order->customer_email,
                'status' => $statusLabel,
                'error' => $e->getMessage(),
            ]);
        }
    }

    private function canSendTo(Order $order): bool
    {
        return filled($order->customer_email) && filled(config('mail.from.address'));
    }
}
