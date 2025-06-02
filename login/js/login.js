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
    const usuario = document.getElementById("usuario").value.trim();
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