document.getElementById("loginForm").addEventListener("submit", async function (e) {
    e.preventDefault();
    const email = document.getElementById("email").value;

    const response = await fetch("php/enviar_reset_password.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ email }),
    });

    const result = await response.text();
    document.getElementById("mensaje").innerText = result;
});