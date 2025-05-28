document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('registroForm');

    form.addEventListener('submit', async (e) => {
        e.preventDefault();

        const formData = new FormData(form);
        const data = Object.fromEntries(formData.entries());

        try {
            const response = await fetch('php/registrarse.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(data)
            });

            const resultado = await response.text();

            // Mostrar mensaje en consola o en pantalla
            console.log(resultado);
            alert(resultado);

            if (resultado.includes("exitosamente")) {
                window.location.href = '../../../index.html';
            }

        } catch (error) {
            console.error('Error al registrar:', error);
            alert('Ocurrió un error al registrar el usuario.');
        }
    });
});
