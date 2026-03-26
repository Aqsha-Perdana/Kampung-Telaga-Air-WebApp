@push('scripts')
<script>
function copyRedeemCode() {
    const code = '{{ $order->redeem_code ?? '' }}';
    navigator.clipboard.writeText(code).then(() => {
        const btn = event.target.closest('button');
        const originalHTML = btn.innerHTML;
        btn.innerHTML = '<i class="ti ti-check"></i> Copied!';
        btn.classList.remove('btn-outline-success');
        btn.classList.add('btn-success');
        
        setTimeout(() => {
            btn.innerHTML = originalHTML;
            btn.classList.remove('btn-success');
            btn.classList.add('btn-outline-success');
        }, 2000);
    }).catch(err => {
        alert('Failed to copy code: ' + code);
    });
}

function confirmPayment(orderId) {
    if (confirm('Are you sure you want to confirm this payment?')) {
        // TODO: Implement AJAX call to confirm payment
        alert('Payment confirmation feature - implement with Ajax to update order status');
        // Example:
        // fetch(`/admin/sales/${orderId}/confirm`, {
        //     method: 'POST',
        //     headers: {
        //         'X-CSRF-TOKEN': '{{ csrf_token() }}',
        //         'Content-Type': 'application/json'
        //     }
        // }).then(response => response.json())
        //   .then(data => {
        //       if(data.success) {
        //           location.reload();
        //       }
        //   });
    }
}

function cancelOrder(orderId) {
    if (confirm('Are you sure you want to cancel this order? This action cannot be undone.')) {
        // TODO: Implement AJAX call to cancel order
        alert('Cancel order feature - implement with Ajax');
        // Example:
        // fetch(`/admin/sales/${orderId}/cancel`, {
        //     method: 'POST',
        //     headers: {
        //         'X-CSRF-TOKEN': '{{ csrf_token() }}',
        //         'Content-Type': 'application/json'
        //     }
        // }).then(response => response.json())
        //   .then(data => {
        //       if(data.success) {
        //           location.reload();
        //       }
        //   });
    }
}
</script>
@endpush
