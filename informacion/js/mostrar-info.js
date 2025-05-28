// Script para recuperar la información de la tarjeta en la página de información
document.addEventListener('DOMContentLoaded', function() {
    // Recuperar la información guardada en localStorage
    const selectedCardInfo = JSON.parse(localStorage.getItem('selectedCardInfo'));
    
    // Función para obtener los elementos del DOM
    function getElements() {
        return {
            imgElement: document.querySelector('.contenedor-img img'),
            titleElement: document.querySelector('.titulo-modal h1'),
            precioElement: document.querySelector('.precio-modal h3')
        };
    }

    // Recuperar elementos
    const elements = getElements();

    // Si hay información guardada y los elementos existen
    if (selectedCardInfo && elements.imgElement && elements.titleElement && elements.precioElement) {
        // Actualizar los elementos con la información de la tarjeta
        elements.imgElement.src = selectedCardInfo.imgSrc;
        elements.titleElement.textContent = selectedCardInfo.title;
        elements.precioElement.textContent = selectedCardInfo.price;
        
        // Prevenir el contenido por defecto de reaparecer en refresco
        sessionStorage.setItem('productInfoLoaded', 'true');
    } else {
        // Verificar si la información ya se cargó anteriormente en esta sesión
        const infoLoaded = sessionStorage.getItem('productInfoLoaded');
        
        if (infoLoaded === 'true') {
            // Si no hay información en localStorage pero ya se cargó antes, 
            // significa que se refrescó la página
            localStorage.removeItem('selectedCardInfo');
            window.location.href = '../catalogo/catalogo.html'; // Redirigir al catálogo
        }
    }
});