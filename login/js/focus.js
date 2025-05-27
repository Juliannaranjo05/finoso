// Seleccionar el input de tipo password dentro de .box
const passwordInput = document.querySelector('.box input[type="password"]');
// Seleccionar el párrafo dentro de .box
const paragraph = document.querySelector('.box p');

// Guardar el ancho original del párrafo para restaurarlo después
const originalWidth = '85%';

// Añadir un event listener para cuando el input recibe el focus
passwordInput.addEventListener('focus', function() {
    // Cambiar el ancho del párrafo a 97%
    paragraph.style.width = '100%';
});

// Añadir un event listener para cuando el input pierde el focus
passwordInput.addEventListener('blur', function() {
    // Restaurar el ancho original del párrafo
    paragraph.style.width = originalWidth;
});