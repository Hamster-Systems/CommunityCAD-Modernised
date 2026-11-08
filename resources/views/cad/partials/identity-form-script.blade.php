<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('[data-identifier-form]').forEach(function (form) {
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
