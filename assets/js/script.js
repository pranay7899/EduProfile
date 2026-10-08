/**
 * Client-Side Application JavaScript
 * Student Management System
 */

document.addEventListener('DOMContentLoaded', function () {

    // --- Admin Dashboard Table Instant Filter ---
    const searchNameInput = document.getElementById('searchName');
    const filterClassSelect = document.getElementById('filterClass');
    const filterMinAgeInput = document.getElementById('filterMinAge');
    const filterMaxAgeInput = document.getElementById('filterMaxAge');
    const resetFiltersBtn = document.getElementById('resetFilters');
    const studentTable = document.getElementById('studentsTable');

    if (studentTable) {
        const tableRows = studentTable.querySelectorAll('tbody tr:not(.no-records-row)');
        const noRecordsRow = document.getElementById('noRecordsRow');

        function applyTableFilters() {
            const nameQuery = searchNameInput ? searchNameInput.value.toLowerCase().trim() : '';
            const selectedClass = filterClassSelect ? filterClassSelect.value.trim() : '';
            const minAge = filterMinAgeInput && filterMinAgeInput.value !== '' ? parseInt(filterMinAgeInput.value, 10) : null;
            const maxAge = filterMaxAgeInput && filterMaxAgeInput.value !== '' ? parseInt(filterMaxAgeInput.value, 10) : null;

            let visibleCount = 0;

            tableRows.forEach(row => {
                const rowName = (row.getAttribute('data-name') || '').toLowerCase();
                const rowClass = (row.getAttribute('data-class') || '').trim();
                const rowAge = parseInt(row.getAttribute('data-age') || '0', 10);

                let matchesName = !nameQuery || rowName.includes(nameQuery);
                let matchesClass = !selectedClass || rowClass === selectedClass;
                let matchesMinAge = minAge === null || rowAge >= minAge;
                let matchesMaxAge = maxAge === null || rowAge <= maxAge;

                if (matchesName && matchesClass && matchesMinAge && matchesMaxAge) {
                    row.style.display = '';
                    visibleCount++;
                } else {
                    row.style.display = 'none';
                }
            });

            if (noRecordsRow) {
                noRecordsRow.style.display = visibleCount === 0 ? '' : 'none';
            }
        }

        if (searchNameInput) searchNameInput.addEventListener('input', applyTableFilters);
        if (filterClassSelect) filterClassSelect.addEventListener('change', applyTableFilters);
        if (filterMinAgeInput) filterMinAgeInput.addEventListener('input', applyTableFilters);
        if (filterMaxAgeInput) filterMaxAgeInput.addEventListener('input', applyTableFilters);

        if (resetFiltersBtn) {
            resetFiltersBtn.addEventListener('click', function () {
                if (searchNameInput) searchNameInput.value = '';
                if (filterClassSelect) filterClassSelect.value = '';
                if (filterMinAgeInput) filterMinAgeInput.value = '';
                if (filterMaxAgeInput) filterMaxAgeInput.value = '';
                applyTableFilters();
            });
        }
    }

    // --- Password Visibility Toggle ---
    const togglePasswordButtons = document.querySelectorAll('.toggle-password-btn');
    togglePasswordButtons.forEach(button => {
        button.addEventListener('click', function () {
            const targetId = this.getAttribute('data-target');
            const targetInput = document.getElementById(targetId);
            if (targetInput) {
                const isPassword = targetInput.getAttribute('type') === 'password';
                targetInput.setAttribute('type', isPassword ? 'text' : 'password');
                this.innerHTML = isPassword ? '<i class="bi bi-eye-slash"></i>' : '<i class="bi bi-eye"></i>';
            }
        });
    });

    // --- Client-Side PDF File Upload Validation ---
    const aadhaarInput = document.getElementById('aadhaar_file') || document.getElementById('aadhaar');
    if (aadhaarInput) {
        aadhaarInput.addEventListener('change', function () {
            const file = this.files[0];
            const feedbackDiv = document.getElementById('aadhaar_feedback');
            
            if (file) {
                const fileName = file.name;
                const fileSizeMB = file.size / (1024 * 1024);
                const fileExt = fileName.split('.').pop().toLowerCase();

                if (fileExt !== 'pdf') {
                    alert('Invalid file format! Please upload a PDF document only.');
                    this.value = ''; // clear input
                    if (feedbackDiv) feedbackDiv.innerHTML = '<span class="text-danger">PDF files only are allowed.</span>';
                    return;
                }

                if (fileSizeMB > 5) {
                    alert('File too large! Maximum allowed size is 5 MB.');
                    this.value = '';
                    if (feedbackDiv) feedbackDiv.innerHTML = '<span class="text-danger">File size exceeds 5 MB.</span>';
                    return;
                }

                if (feedbackDiv) {
                    feedbackDiv.innerHTML = `<span class="text-success"><i class="bi bi-file-earmark-pdf me-1"></i> Selected: ${fileName} (${fileSizeMB.toFixed(2)} MB)</span>`;
                }
            }
        });
    }

});
