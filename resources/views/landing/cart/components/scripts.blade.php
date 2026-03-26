<script>
setTimeout(function() {
    document.querySelectorAll('.alert').forEach(alert => {
        alert.style.transition = 'opacity 0.5s ease';
        alert.style.opacity = '0';
        setTimeout(() => alert.remove(), 500);
    });
}, 5000);

document.querySelectorAll('form[action*="cart/"]').forEach(form => {
    if (!form.querySelector('input[name="jumlah_peserta"]')) {
        return;
    }

    form.addEventListener('submit', function(e) {
        const participantsInput = this.querySelector('input[name="jumlah_peserta"]');
        const dateInput = this.querySelector('input[name="tanggal_keberangkatan"]');
        const participants = parseInt(participantsInput?.value || '0', 10);
        const minParticipants = parseInt(participantsInput?.min || '1', 10);
        const maxParticipants = participantsInput?.max ? parseInt(participantsInput.max, 10) : null;
        const departureDate = dateInput?.value || '';

        if (!participants || participants < minParticipants) {
            e.preventDefault();
            alert(`This package requires at least ${minParticipants} participant${minParticipants > 1 ? 's' : ''}.`);
            return false;
        }

        if (maxParticipants !== null && participants > maxParticipants) {
            e.preventDefault();
            alert(`This package allows up to ${maxParticipants} participants.`);
            return false;
        }

        if (!departureDate) {
            e.preventDefault();
            alert('Please select a departure date.');
            return false;
        }

        const selectedDate = new Date(departureDate);
        const minDate = new Date();
        minDate.setHours(0, 0, 0, 0);
        minDate.setDate(minDate.getDate() + 3);

        if (selectedDate < minDate) {
            e.preventDefault();
            alert('Departure must be at least 3 days from today.');
            return false;
        }
    });
});
</script>