<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Confirmed</title>
</head>
<body style="margin:0;padding:0;background:#f5f8fb;font-family:Arial,sans-serif;color:#1f2937;">
    <div style="max-width:640px;margin:0 auto;padding:24px 16px;">
        <div style="background:linear-gradient(135deg,#96defb 0%,#2a93cc 100%);padding:28px 24px;border-radius:20px 20px 0 0;color:#fff;">
            <h1 style="margin:0 0 8px;font-size:28px;">Booking confirmed</h1>
            <p style="margin:0;font-size:15px;line-height:1.6;">Your payment was received successfully. We have attached your invoice to this email.</p>
        </div>

        <div style="background:#ffffff;padding:24px;border-radius:0 0 20px 20px;border:1px solid #e5edf5;border-top:0;">
            <p style="margin:0 0 16px;">Hello {{ $order->customer_name ?: 'Guest' }},</p>
            <p style="margin:0 0 18px;line-height:1.7;">
                Thank you for booking with Kampung Telaga Air. Your order is now marked as paid and ready for the next step.
            </p>

            <div style="background:#f8fbfd;border:1px solid #e5edf5;border-radius:16px;padding:18px;margin-bottom:20px;">
                <p style="margin:0 0 8px;"><strong>Order ID:</strong> {{ $order->id_order }}</p>
                <p style="margin:0 0 8px;"><strong>Payment method:</strong> Credit/Debit Card (Stripe)</p>
                <p style="margin:0 0 8px;"><strong>Total paid:</strong> RM {{ number_format((float) $order->base_amount, 2) }}</p>
                <p style="margin:0;"><strong>Redeem code:</strong> {{ $order->redeem_code ?: 'Will appear once payment is confirmed' }}</p>
            </div>

            <table style="width:100%;border-collapse:collapse;margin-bottom:20px;">
                <thead>
                    <tr>
                        <th style="text-align:left;padding:10px 8px;border-bottom:1px solid #e5edf5;font-size:13px;color:#475467;">Package</th>
                        <th style="text-align:left;padding:10px 8px;border-bottom:1px solid #e5edf5;font-size:13px;color:#475467;">Departure</th>
                        <th style="text-align:left;padding:10px 8px;border-bottom:1px solid #e5edf5;font-size:13px;color:#475467;">Participants</th>
                        <th style="text-align:right;padding:10px 8px;border-bottom:1px solid #e5edf5;font-size:13px;color:#475467;">Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($order->items as $item)
                        <tr>
                            <td style="padding:12px 8px;border-bottom:1px solid #eef2f6;">{{ $item->nama_paket }}</td>
                            <td style="padding:12px 8px;border-bottom:1px solid #eef2f6;">{{ optional($item->tanggal_keberangkatan)->format('d M Y') ?: '-' }}</td>
                            <td style="padding:12px 8px;border-bottom:1px solid #eef2f6;">{{ number_format((int) $item->jumlah_peserta) }}</td>
                            <td style="padding:12px 8px;border-bottom:1px solid #eef2f6;text-align:right;">RM {{ number_format((float) $item->subtotal, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <p style="margin:0;line-height:1.7;color:#475467;">
                You can also review your order and download the invoice again from your visitor account whenever needed.
            </p>
        </div>
    </div>
</body>
</html>
