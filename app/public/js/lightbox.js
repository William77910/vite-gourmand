// Lightbox generique : clic sur une image ".vg-zoomable" -> l'affiche en grand
document.addEventListener('DOMContentLoaded', function () {
    const modalEl = document.getElementById('modalImageAgrandie');
    const imageAgrandie = document.getElementById('imageAgrandie');

    if (!modalEl || !imageAgrandie) {
        return;
    }

    const modal = new bootstrap.Modal(modalEl);

    document.querySelectorAll('.vg-zoomable').forEach(function (img) {
        if (!img.title) {
            img.title = 'Cliquer pour agrandir';
        }
        img.addEventListener('click', function () {
            imageAgrandie.src = img.src;
            imageAgrandie.alt = img.alt;
            modal.show();
        });
    });
});
