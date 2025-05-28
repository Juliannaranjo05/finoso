// Seleccionar todas las cards
document.querySelectorAll('.contenedor-card').forEach(card => {
    card.addEventListener('click', () => {
      // Obtener datos de la card
      const imgSrc = card.querySelector('img').src;
      const modelo = card.querySelector('h3').textContent.trim();
      const precio = card.querySelector('p').textContent.trim();
  
      // Mostrar el modal
      const modal = document.querySelector('.contenedor-modal');
      modal.style.display = 'flex';
  
      // Colocar datos en el modal
      modal.querySelector('.contenedor-img img').src = imgSrc;
      modal.querySelector('.titulo-modal h1').textContent = modelo;
      modal.querySelector('.precio-modal h3').textContent = precio;
  
      // Condiciones para marca y detalles según el modelo
      let marca = "";
      let detalle = "";
  
      switch(modelo) {
        case "Rchrd Mll Calavera Blanco Circones Negros-Dorados":
          marca = "Richard Mille";
          detalle = "Diseño de calavera esqueleto con incrustaciones de circones negros y bisel dorado ionizado. Correa de acero pulido.";
          break;
          
        case "Rchrd Mll Calavera Circones Dorado-Negro":
          marca = "Richard Mille";
          detalle = "Calavera central con incrustación de circones facetados. Bisel de acero pavonado y corona mecanizada con acabado satinado.";
          break;
      
        case "Q&Q hombre Bazel Dorado Tablero Negro-Dorado":
          marca = "Q&Q";
          detalle = "Bazeleado dorado con tablero lacado negro. Detalles en índices aplicados y movimiento análogo de precisión.";
          break;
      
        case "Q&Q hombre Bazel Plateado Tablero Blanco-Plateado":
          marca = "Q&Q";
          detalle = "Bazeleado plateado en acero cromado, tablero blanco con subesferas decorativas y marcadores luminiscentes.";
          break;
      
        case "Tchmrn Mujer Circones Negro Tablero Plateado-Dorado":
          marca = "Technomarine";
          detalle = "Reloj de dama con circones austríacos y tablero galvanizado plateado. Bisel chapado en oro brillante.";
          break;
      
        case "Tchmrn Mujer Circones Negro Tablero Negro-Dorado":
          marca = "Technomarine";
          detalle = "Diseño elegante de tablero negro mate con bisel dorado ionizado e incrustaciones de circones facetados de alta pureza.";
          break;
      
        case "Tchmrn Negro Tablero Negro":
          marca = "Technomarine";
          detalle = "Minimalismo total en acabado negro mate. Tablero monocromático con detalles satinados y cristal mineral templado.";
          break;
      
        case "Patk Phlppe Bicolor Dorado - Negro":
          marca = "Patek Philippe";
          detalle = "Clásico bicolor con brazalete alternado en baño de oro y acero pulido. Tablero negro con marcadores luminiscentes y fechador a las 3.";
          break;
      
        default:
          marca = "Marca no registrada";
          detalle = "Detalles no disponibles";
      }      
  
      // Mostrar marca y detalles en el modal
      modal.querySelector('.marca h2').textContent = marca;
      modal.querySelector('.detalle h3').textContent = detalle;
    });
  });

  document.querySelector('.cerrar-modal').addEventListener('click', () => {
    document.querySelector('.contenedor-modal').style.display = 'none';
  });  
  
  // Para cerrar el modal (opcional)
  document.querySelector('.contenedor-modal').addEventListener('click', e => {
    if (e.target.classList.contains('contenedor-modal')) {
      e.target.style.display = 'none';
    }
  });  