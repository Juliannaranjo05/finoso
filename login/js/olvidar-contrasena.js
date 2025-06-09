document.getElementById("olvidarForm").addEventListener("submit", async function (e) {
    e.preventDefault();
    const email = document.getElementById("email").value;

    const response = await fetch("http://127.0.0.1/finoso/login/php/enviar_reset_password.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ email }),
    });

    const result = await response.text();
    const mensaje = document.getElementById("mensaje-olvidar");

    mensaje.innerText = result;

    // Estilo según el tipo de mensaje
    if (result.includes("Correo de recuperación enviado")) {
        mensaje.style.color = "green";
    } else {
        mensaje.style.color = "red";
    }
});