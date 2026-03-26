@push('scripts')
<script>
    // Copy Order ID to clipboard
    function copyOrderId() {
        const orderId = '{{ $order->id_order }}';
        navigator.clipboard.writeText(orderId).then(() => {
            const btn = event.target.closest('button');
            const originalHTML = btn.innerHTML;
            btn.innerHTML = '<i class="bi bi-check"></i>';
            btn.classList.add('btn-success');
            btn.classList.remove('btn-outline-secondary');
            
            setTimeout(() => {
                btn.innerHTML = originalHTML;
                btn.classList.remove('btn-success');
                btn.classList.add('btn-outline-secondary');
            }, 2000);
        }).catch(err => {
            alert('Failed to copy: ' + orderId);
        });
    }

    // Copy Redeem Code to clipboard
    function copyRedeemCode() {
        const code = '{{ $order->redeem_code }}';
        navigator.clipboard.writeText(code).then(() => {
            const btn = event.target.closest('button');
            const originalHTML = btn.innerHTML;
            btn.innerHTML = '<i class="bi bi-check"></i> Copied!';
            btn.classList.remove('btn-outline-success');
            btn.classList.add('btn-success');
            
            setTimeout(() => {
                btn.innerHTML = originalHTML;
                btn.classList.remove('btn-success');
                btn.classList.add('btn-outline-success');
            }, 2000);
        }).catch(err => {
            alert('Failed to copy code. Please copy manually: ' + code);
        });
    }
</script>
@endpush
