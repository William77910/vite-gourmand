document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('input[name="note"]').forEach(function (radio) {
        radio.addEventListener('change', function () {
            const groupe = radio.closest('.modal-body').querySelectorAll('input[name="note"]');
            groupe.forEach(function (r) {
                r.closest('label').classList.remove('active');
            });
            if (radio.checked) {
                radio.closest('label').classList.add('active');
            }
        });
    });
});
