document.getElementById('formularioReloj').addEventListener('submit', function (e) {
    e.preventDefault();

    const form = e.target;
    const datos = new FormData(form);

    fetch('php/insertar_reloj.php', {
        method: 'POST',
        body: datos
    })
    .then(response => response.text())
    .then(mensaje => {
        document.getElementById('mensaje').innerText = mensaje;
        form.reset(); // limpia el formulario si todo sale bien
    })
    .catch(error => {
        console.error('Error:', error);
        document.getElementById('mensaje').innerText = 'Ocurrió un error al guardar.';
    });
});