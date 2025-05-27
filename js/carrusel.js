document.addEventListener('DOMContentLoaded', () => {
    const imagenes = document.querySelectorAll('.img-coleccion img');

    imagenes.forEach(img => {
        img.addEventListener('click', () => {
            const idReloj = img.getAttribute('data-id-reloj');
            if (idReloj) {
                window.location.href = `informacion/informacion.html?id_reloj=${idReloj}`;
            } else {
                console.error('No se encontró el id_reloj para esta imagen');
            }
        });
    });
});
