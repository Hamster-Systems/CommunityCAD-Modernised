<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('[data-subdivision-dropdown]').forEach(function (dropdown) {
        const summary = dropdown.querySelector('[data-subdivision-summary]');
        const options = dropdown.querySelectorAll('input[type="checkbox"][name="subdivisions[]"]');
        function updateSummary() {
            const selected = Array.from(options).filter(option => option.checked).map(option => option.value);
            summary.textContent = selected.length ? selected.join(', ') : 'None / General';
            summary.title = summary.textContent;
        }
        options.forEach(option => option.addEventListener('change', updateSummary));
        updateSummary();
    });
    document.querySelectorAll('[data-identifier-form]').forEach(function (form) {
        // Keep subdivision choices scoped to the selected MDT department.
        const department = form.querySelector('[name="user_department_id"]');
        const subdivisionDropdown = form.querySelector('[data-subdivision-dropdown]');
        let optionsByDepartment = {};
        try { optionsByDepartment = JSON.parse(form.dataset.departmentSubdivisions || '{}'); } catch (_) {}
        function updateDepartmentSubdivisions() {
            const allowed = new Set(optionsByDepartment[department?.value] || []);
            subdivisionDropdown?.querySelectorAll('[data-subdivision-option]').forEach(function (label) {
                const checkbox = label.querySelector('input[type="checkbox"]');
                const visible = allowed.has(label.dataset.subdivisionOption);
                label.hidden = !visible;
                if (checkbox) {
                    checkbox.disabled = !visible;
                    if (!visible) checkbox.checked = false;
                    checkbox.dispatchEvent(new Event('change', { bubbles: true }));
                }
            });
        }
        department?.addEventListener('change', updateDepartmentSubdivisions);
        updateDepartmentSubdivisions();

        const civilianSelect = form.querySelector('[name="civilian_id"]');
        const nameField = form.querySelector('[name="display_name"]');
        const hint = form.querySelector('[data-name-hint]');
        if (!civilianSelect || !nameField || !hint) return;
        let typedName = nameField.value;
        function syncName() {
            const option = civilianSelect.selectedOptions[0];
            if (option && option.dataset.fullName) {
                nameField.value = option.dataset.fullName;
                nameField.readOnly = true;
                nameField.required = false;
                hint.textContent = 'MDT name: ' + option.dataset.shortName + ' (taken from the linked civilian).';
            } else {
                nameField.readOnly = false;
                nameField.required = true;
                nameField.value = typedName;
                hint.textContent = 'Enter a full name, or link a civilian for automatic naming.';
            }
        }
        nameField.addEventListener('input', () => {
            if (!nameField.readOnly) typedName = nameField.value;
        });
        civilianSelect.addEventListener('change', syncName);
        syncName();
    });
});
</script>
