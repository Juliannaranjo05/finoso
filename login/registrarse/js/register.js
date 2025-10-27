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

    // UN SOLO event listener para submit
    registroForm.addEventListener('submit', async (e) => {
        e.preventDefault();

        // Limpiar mensaje anterior
        mensaje.textContent = "";
        mensaje.className = "";
        mensaje.style.display = "none";

        const formData = new FormData(registroForm);
        const data = Object.fromEntries(formData.entries());

        // Validación básica frontend
        if (!data.nombre || !data.contrasena || !data.correo) {
            mensaje.textContent = "Por favor completa todos los campos.";
            mensaje.className = "mensaje-error";
            mensaje.style.display = "block";
            
            registroForm.classList.add("shake");
            setTimeout(() => registroForm.classList.remove("shake"), 400);
            return;
        }

        try {
            const response = await fetch('http://127.0.0.1/finoso/login/registrarse/php/registrarse.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(data)
            });

            const resultado = await response.json();

            // Mostrar mensaje (permite HTML para el link)
            mensaje.innerHTML = resultado.message;
            mensaje.style.display = "block";
            
            if (resultado.success) {
                // Éxito - mensaje verde
                mensaje.className = "mensaje-exito";
                
                // Redirigir después de 2 segundos
                setTimeout(() => {
                    window.location.href = '../login.html';
                }, 2000);
            } else {
                // Error - mensaje rojo
                mensaje.className = "mensaje-error";
                
                // Vibración
                registroForm.classList.add("shake");
                setTimeout(() => registroForm.classList.remove("shake"), 400);
            }

        } catch (error) {
            console.error('Error al registrar:', error);
            mensaje.textContent = "Error de conexión. Por favor intenta de nuevo.";
            mensaje.className = "mensaje-error";
            mensaje.style.display = "block";
            
            registroForm.classList.add("shake");
            setTimeout(() => registroForm.classList.remove("shake"), 400);
        }
    });
});
                // Vibración
                registroForm.classList.add("shake");
                setTimeout(() => registroForm.classList.remove("shake"), 400);
            }

        } catch (error) {
            console.error('Error al registrar:', error);
            mensaje.textContent = "Error de conexión. Por favor intenta de nuevo.";
            mensaje.className = "mensaje-error";
            mensaje.style.display = "block";
            
            registroForm.classList.add("shake");
            setTimeout(() => registroForm.classList.remove("shake"), 400);
        }
    });
});