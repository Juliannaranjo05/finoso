const passwordField = document.getElementById("contrasena");
const togglePassword = document.getElementById("togglePassword");
const eyeOpen = document.getElementById("eye-open");
const eyeClosed = document.getElementById("eye-closed");

togglePassword.addEventListener("click", () => {
    const isPassword = passwordField.type === "password";
    passwordField.type = isPassword ? "text" : "password";
    eyeOpen.style.display = isPassword ? "flex" : "none";
    eyeClosed.style.display = isPassword ? "none" : "flex";
});

document.getElementById("loginForm").addEventListener("submit", function(e) {
    const usuario = document.getElementById("nombre").value.trim();
    const contrasena = passwordField.value.trim();
    const mensaje = document.getElementById("mensaje-error");

    if (!usuario || !contrasena) {
        e.preventDefault();
        mensaje.textContent = "Por favor completa todos los campos.";

        // Aplica vibración al formulario
        const form = document.querySelector(".box");
        form.classList.add("shake");

        // Elimina la clase después de la animación
        setTimeout(() => {
            form.classList.remove("shake");
        }, 400);
    } else {
        mensaje.textContent = "";
    }
});

document.addEventListener('DOMContentLoaded', function() {
    const formulario = document.getElementById('loginForm');
    const mensajeDiv = document.getElementById('mensaje-error');

    formulario.addEventListener('submit', function(e) {
        e.preventDefault();

        // Obtener valores del formulario y mostrarlos en consola para depuración
        const nombre = document.getElementById('nombre').value;
        const contrasena = document.getElementById('contrasena').value;

        console.log("Datos a enviar:", { nombre, contrasena }); // Para depuración

        // Validar campos en el cliente (opcional pero útil)
        if (!nombre || !contrasena) {
            mostrarMensaje('Por favor, completa todos los campos', 'error');
            return;
        }

        // Crear objeto FormData
        const formData = new FormData();
        formData.append('nombre', nombre);
        formData.append('contrasena', contrasena);
        formData.append('action', 'login');

        // Mostrar mensaje de carga
        mostrarMensaje('Procesando...', 'info');

        // Enviar petición fetch con datos JSON
        fetch('http://127.0.0.1/finoso/login/php/login.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: new URLSearchParams({
                'nombre': nombre,
                'contrasena': contrasena,
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