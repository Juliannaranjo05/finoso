document.querySelector('.contenedor-modal .btn-whatsapp').addEventListener('click', () => {
  const modal = document.querySelector('.contenedor-modal');
  const modelo = modal.querySelector('.titulo-modal h1').textContent.trim();
  const precio = modal.querySelector('.precio-modal h3').textContent.trim();

  const numeroWhatsapp = '573180908930';
  const mensaje = `Hola, estoy interesado en el ${modelo} que vi en su catálogo, cuyo precio es ${precio}. ¿Me podría dar más información, por favor?`;

  const url = `https://wa.me/${numeroWhatsapp}?text=${encodeURIComponent(mensaje)}`;

  window.open(url, '_blank'); // abre en nueva pestaña
});