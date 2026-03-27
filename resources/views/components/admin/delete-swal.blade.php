@once
<script>
    (function () {
        function getCsrfToken() {
            const tokenMeta = document.querySelector('meta[name="csrf-token"]');
            return tokenMeta ? tokenMeta.getAttribute('content') : '';
        }

        function buildDeleteForm(actionUrl, method) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = actionUrl;
            form.style.display = 'none';

            const token = getCsrfToken();
            if (token) {
                const csrfInput = document.createElement('input');
                csrfInput.type = 'hidden';
                csrfInput.name = '_token';
                csrfInput.value = token;
                form.appendChild(csrfInput);
            }

            if ((method || 'DELETE').toUpperCase() !== 'POST') {
                const methodInput = document.createElement('input');
                methodInput.type = 'hidden';
                methodInput.name = '_method';
                methodInput.value = method || 'DELETE';
                form.appendChild(methodInput);
            }

            document.body.appendChild(form);
            return form;
        }

        function submitDelete(config) {
            if (config.formId) {
                const existingForm = document.getElementById(config.formId);
                if (existingForm) {
                    existingForm.submit();
                    return;
                }
                console.warn('[adminDeleteSwal] Missing formId target: ' + config.formId);
            }

            if (!config.actionUrl) {
                console.warn('[adminDeleteSwal] Missing actionUrl.');
                return;
            }

            const tempForm = buildDeleteForm(config.actionUrl, config.method || 'DELETE');
            tempForm.submit();
        }

        window.adminDeleteSwal = function (config = {}) {
            const itemLabel = config.itemLabel || 'this item';
            const title = config.title || 'Delete item?';
            const html = config.html || `This will permanently delete <strong>${itemLabel}</strong>. This action cannot be undone.`;
            const confirmButtonText = config.confirmButtonText || 'Yes, delete';
            const cancelButtonText = config.cancelButtonText || 'Cancel';
            const icon = config.icon || 'warning';

            const runDelete = function () {
                submitDelete(config);
            };

            if (window.Swal && typeof window.Swal.fire === 'function') {
                window.Swal.fire({
                    icon,
                    title,
                    html,
                    showCancelButton: true,
                    confirmButtonText,
                    cancelButtonText,
                    reverseButtons: true,
                    allowOutsideClick: false,
                    allowEscapeKey: true,
                    confirmButtonColor: '#dc3545',
                    cancelButtonColor: '#6c757d',
                }).then((result) => {
                    if (result.isConfirmed) {
                        runDelete();
                    }
                });
                return;
            }

            if (window.confirm(title + ' ' + itemLabel + '?')) {
                runDelete();
            }
        };
    })();
</script>
@endonce
