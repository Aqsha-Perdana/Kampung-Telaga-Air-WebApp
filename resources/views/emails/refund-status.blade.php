<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Refund Update</title>
</head>
<body style="margin:0;padding:0;background:#f5f8fb;font-family:Arial,sans-serif;color:#1f2937;">
    <div style="max-width:640px;margin:0 auto;padding:24px 16px;">
        <div style="background:#ffffff;padding:28px 24px;border-radius:20px;border:1px solid #e5edf5;">
            <h1 style="margin:0 0 8px;font-size:28px;">{{ $statusLabel }}</h1>
            <p style="margin:0 0 18px;line-height:1.7;">Hello {{ $order->customer_name ?: 'Guest' }},</p>
            <p style="margin:0 0 18px;line-height:1.7;">{{ $messageText }}</p>

            <div style="background:#f8fbfd;border:1px solid #e5edf5;border-radius:16px;padding:18px;margin-bottom:18px;">
                <p style="margin:0 0 8px;"><strong>Order ID:</strong> {{ $order->id_order }}</p>
                <p style="margin:0 0 8px;"><strong>Current order status:</strong> {{ ucfirst(str_replace('_', ' ', $order->status)) }}</p>
                <p style="margin:0 0 8px;"><strong>Refund status:</strong> {{ ucfirst(str_replace('_', ' ', (string) $order->refund_status)) }}</p>
                @if(!is_null($order->refund_amount))
                    <p style="margin:0 0 8px;"><strong>Refund amount:</strong> RM {{ number_format((float) $order->refund_amount, 2) }}</p>
                @endif
                @if(!is_null($order->refund_fee))
                    <p style="margin:0;"><strong>Refund fee:</strong> RM {{ number_format((float) $order->refund_fee, 2) }}</p>
                @endif
            </div>

            <p style="margin:0;line-height:1.7;color:#475467;">
                If you need more details, please review your order history in your visitor account or contact our team.
            </p>
        </div>
    </div>
</body>
</html>
