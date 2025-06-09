document.addEventListener("DOMContentLoaded", function () {
    const registroForm = document.getElementById("registroForm");
    const passwordField = document.getElementById("contrasena");
    const togglePassword = document.getElementById("togglePassword");
    const eyeOpen = document.getElementById("eye-open");
    const eyeClosed = document.getElementById("eye-closed");
    const mensaje = document.getElementById("mensaje-error");

    // Mostrar / Ocultar contraseña
    if (togglePassword && passwordField) {
        togglePassword.addEventListener("click", () => {
            const isPassword = passwordField.type === "password";
            passwordField.type = isPassword ? "text" : "password";
            eyeOpen.style.display = isPassword ? "flex" : "none";
            eyeClosed.style.display = isPassword ? "none" : "flex";
        });
    }

    // Validación de campos
    registroForm.addEventListener("submit", function (e) {
        const nombre = registroForm.querySelector('input[name="nombre"]').value.trim();
        const contrasena = passwordField.value.trim();
        const email = registroForm.querySelector('input[name="correo"]').value.trim();

        if (!nombre || !contrasena || !email) {
            e.preventDefault();
            mensaje.textContent = "Por favor completa todos los campos.";

            // Agrega efecto de vibración
            registroForm.classList.add("shake");
            setTimeout(() => {
                registroForm.classList.remove("shake");
            }, 400);
        } else {
            mensaje.textContent = "";
        }
    });

    const form = document.getElementById('registroForm');

    form.addEventListener('submit', async (e) => {
        e.preventDefault();

        const formData = new FormData(form);
        const data = Object.fromEntries(formData.entries());

        try {
            const response = await fetch('http://127.0.0.1/finoso/login/registrarse/php/registrarse.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(data)
            });

            const resultado = await response.text();
            alert(resultado);

            if (resultado.includes("exitosamente")) {
                window.location.href = '../login.html';
            }

        } catch (error) {
            console.error('Error al registrar:', error);
            alert('Error en el registro.');
        }
    });
});