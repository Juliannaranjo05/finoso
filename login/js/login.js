document.addEventListener('DOMContentLoaded', function() {
    const formulario = document.getElementById('loginForm');
    const mensajeDiv = document.getElementById('mensaje');

    formulario.addEventListener('submit', function(e) {
        e.preventDefault();

        // Obtener valores del formulario y mostrarlos en consola para depuración
        const nombre = document.getElementById('nombre').value;
        const password = document.getElementById('password').value;

        console.log("Datos a enviar:", { nombre, password }); // Para depuración

        // Validar campos en el cliente (opcional pero útil)
        if (!nombre || !password) {
            mostrarMensaje('Por favor, completa todos los campos', 'error');
            return;
        }

        // Crear objeto FormData
        const formData = new FormData();
        formData.append('nombre', nombre);
        formData.append('password', password);
        formData.append('action', 'login');

        // Mostrar mensaje de carga
        mostrarMensaje('Procesando...', 'info');

        // Enviar petición fetch con datos JSON
        fetch('php/login.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: new URLSearchParams({
                'nombre': nombre,
                'password': password,
                'action': 'login'
            })
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Error en la respuesta del servidor: ' + response.status);
            }
            return response.json();
        })
        .then(data => {
            console.log("Respuesta recibida:", data); // Para depuración
            
            if (data.success) {
                mostrarMensaje(data.message, 'success');
                setTimeout(() => {
                    window.location.href = data.redirect;
                }, 1500);
            } else {
                mostrarMensaje(data.message, 'error');
                console.log("Error detallado:", data.debug);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            mostrarMensaje('Ocurrió un error al procesar la solicitud: ' + error.message, 'error');
        });
    });

    // Función para mostrar mensajes
    function mostrarMensaje(mensaje, tipo) {
        mensajeDiv.textContent = mensaje;
        mensajeDiv.className = tipo;
        
        if (tipo === 'error') {
            mensajeDiv.style.color = '#e74c3c';
        } else if (tipo === 'success') {
            mensajeDiv.style.color = '#2ecc71';
        } else if (tipo === 'info') {
            mensajeDiv.style.color = '#3498db';
        }
    }
});