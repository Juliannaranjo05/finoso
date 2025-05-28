const contenedor = document.querySelector('.contenedor-img-coleccion');

function moverVitrina() {
  // Mueve todas las imágenes a la izquierda
  contenedor.childNodes.forEach(item => {
    if (item.nodeType === 1) {
      item.style.transition = "transform 1s linear";
      item.style.transform = "translateX(-110%)";
    }
  });

  setTimeout(() => {
    // Resetea la posición de la primera imagen y la manda al final
    const primera = contenedor.firstElementChild;
    primera.style.transition = "none";
    primera.style.transform = "none";
    contenedor.appendChild(primera);

    // Resetea las demás imágenes
    contenedor.childNodes.forEach(item => {
      if (item.nodeType === 1) {
        item.style.transition = "none";
        item.style.transform = "none";
      }
    });
  }, 1000);
}

// Ejecuta cada 2 segundos
setInterval(moverVitrina, 2000);