// Ocultar mensaje de error al cargar la página
document.addEventListener('DOMContentLoaded', function() {
    const mensaje = document.getElementById("mensaje-olvidar");
    if (mensaje) {
        mensaje.style.display = "none";
        mensaje.innerText = "";
    }
});

document.getElementById("olvidarForm").addEventListener("submit", async function (e) {
    e.preventDefault();
    const email = document.getElementById("email").value;
    const mensaje = document.getElementById("mensaje-olvidar");

    // Limpiar mensaje anterior
    mensaje.innerText = "";
    mensaje.style.display = "none";

    const response = await fetch("https://finoso.store/login/php/enviar_reset_password.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ email }),
    });

    const result = await response.text();

    // Mostrar mensaje
    mensaje.innerText = result;
    mensaje.style.display = "block";

    // Estilo según el tipo de mensaje
    if (result.includes("Correo de recuperación enviado")) {
        mensaje.classList.remove('mensaje-error');
        mensaje.classList.add('mensaje-exito');
    } else {
        mensaje.classList.remove('mensaje-exito');
        mensaje.classList.add('mensaje-error');
        // Agregar animación de shake
        mensaje.classList.add('shake');
        setTimeout(() => mensaje.classList.remove('shake'), 500);
    }
});