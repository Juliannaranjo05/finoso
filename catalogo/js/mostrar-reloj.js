document.addEventListener('DOMContentLoaded', () => {
  fetch('http://127.0.0.1/finoso/catalogo/php/mostrar_relojes.php') // Ajusta la ruta si es necesario
    .then(response => {
      if (!response.ok) throw new Error('Error al cargar los relojes');
      return response.text();
    })
      .then(data => {
      document.getElementById('contenedor-relojes').innerHTML = data;
    })
    .catch(error => {
      console.error('Hubo un problema al cargar los relojes:', error);
    });
});