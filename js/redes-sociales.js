// Para el botón general de WhatsApp
document.getElementById('whatsaap').addEventListener('click', function() {
    const numeroWhatsapp = '573180908930';
    const mensaje = 'Hola, quiero más información!';
  
    const url = `https://wa.me/${numeroWhatsapp}?text=${encodeURIComponent(mensaje)}`;
  
    window.open(url, '_blank');
  });

// Instagram con utm para tracking
document.getElementById("instagram").addEventListener("click", function() {
    const instagramUser = "finoso.club"; // Reemplaza con tu usuario
    if (/Android|iPhone|iPad|iPod/i.test(navigator.userAgent)) {
        // Si es móvil, intenta abrir el chat en la app
        window.open(`instagram://user?username=${instagramUser}`, "_blank");
    } else {
        // Si es escritorio, redirige al perfil
        window.open(`https://instagram.com/${instagramUser}`, "_blank");
    }
});