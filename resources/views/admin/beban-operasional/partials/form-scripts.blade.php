<script>
    (function () {
        const fileInput = document.getElementById('bukti_pembayaran');
        const uploadArea = document.getElementById('uploadArea');
        const browseButton = document.getElementById('uploadBrowseButton');
        const clearButton = document.getElementById('clearUploadButton');
        const emptyState = document.getElementById('uploadEmptyState');
        const filledState = document.getElementById('uploadFilledState');
        const fileName = document.getElementById('fileName');
        const fileMeta = document.getElementById('fileMeta');
        const resetButton = document.getElementById('resetExpenseForm');
        const submitButton = document.getElementById('submitBtn');
        const form = document.getElementById('expenseForm');

        if (!fileInput || !uploadArea || !emptyState || !filledState) {
            return;
        }

        const syncFileState = () => {
            const file = fileInput.files && fileInput.files[0] ? fileInput.files[0] : null;

            if (!file) {
                emptyState.classList.remove('d-none');
                filledState.classList.add('d-none');
                if (fileName) {
                    fileName.textContent = '-';
                }
                if (fileMeta) {
                    fileMeta.textContent = '';
                }
                return;
            }

            emptyState.classList.add('d-none');
            filledState.classList.remove('d-none');

            if (fileName) {
                fileName.textContent = file.name;
            }

            if (fileMeta) {
                fileMeta.textContent = `${(file.size / 1024).toFixed(1)} KB`;
            }
        };

        browseButton?.addEventListener('click', () => fileInput.click());
        fileInput.addEventListener('change', syncFileState);
        clearButton?.addEventListener('click', () => {
            fileInput.value = '';
            syncFileState();
        });

        ['dragenter', 'dragover'].forEach((eventName) => {
            uploadArea.addEventListener(eventName, (event) => {
                event.preventDefault();
                event.stopPropagation();
                uploadArea.classList.add('is-dragover');
            });
        });

        ['dragleave', 'drop'].forEach((eventName) => {
            uploadArea.addEventListener(eventName, (event) => {
                event.preventDefault();
                event.stopPropagation();
                uploadArea.classList.remove('is-dragover');
            });
        });

        uploadArea.addEventListener('drop', (event) => {
            const files = event.dataTransfer?.files;
            if (!files || !files.length) {
                return;
            }

            fileInput.files = files;
            syncFileState();
        });

        uploadArea.addEventListener('click', (event) => {
            if (event.target.closest('button')) {
                return;
            }

            fileInput.click();
        });

        resetButton?.addEventListener('click', () => {
            window.setTimeout(syncFileState, 0);
        });

        form?.addEventListener('submit', (event) => {
            const amountInput = document.getElementById('jumlah');
            const amount = Number.parseFloat(amountInput?.value || '0');

            if (amount <= 0) {
                event.preventDefault();
                amountInput?.focus();
                return;
            }

            if (submitButton) {
                submitButton.disabled = true;
                submitButton.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Saving...';
            }
        });

        syncFileState();
    })();
</script>
