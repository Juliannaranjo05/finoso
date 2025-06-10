// Variables globales
const mainElement = document.getElementById('mainElement');
const progressBar = document.getElementById('progressBar');
const dots = document.querySelectorAll('.dot');
const insertarImg = document.getElementById('insertar-img');

let imagenReemplazada = false;
let imagenesDestacadasCargadas = false;

// Función que devuelve keyframes según el tamaño de pantalla
function getKeyframes() {
    const width = window.innerWidth;

    if (width <= 660) {
        // Móvil
        return [
            { x: 73, y: 32, rotation: 15, scale: 0.6 },
            { x: 20, y: 40, rotation: 5, scale: 0.3 }
        ];
    } else if (width <= 1200) {
        // Tablet
        return [
            { x: 65, y: 50, rotation: 20, scale: 1.2 },
            { x: 20, y: 27, rotation: 5, scale: 0.7 }
        ];
    } else {
        // Escritorio grande
        return [
            { x: 60, y: 55, rotation: -10, scale: 1.6 },
            { x: 20, y: 12, rotation: 5, scale: 0.9 }
        ];
    }
}

// Inicialización dinámica de keyframes
let keyframes = getKeyframes();

function lerp(start, end, factor) {
    return start + (end - start) * factor;
}

function interpolateKeyframes(keyframe1, keyframe2, factor) {
    return {
        x: lerp(keyframe1.x, keyframe2.x, factor),
        y: lerp(keyframe1.y, keyframe2.y, factor),
        rotation: lerp(keyframe1.rotation, keyframe2.rotation, factor),
        scale: lerp(keyframe1.scale, keyframe2.scale, factor)
    };
}

function applyTransforms(transforms) {
    mainElement.style.left = transforms.x + '%';
    mainElement.style.top = transforms.y + '%';
    mainElement.style.transform = `translate(-50%, -50%) rotate(${transforms.rotation}deg) scale(${transforms.scale})`;
}

// Función para cargar las imágenes destacadas
function cargarImagenesDestacadas() {
    return fetch('http://127.0.0.1/finoso/php/obtener_cuatro_destacados.php')
        .then(res => res.json())
        .then(imagenes => {
            if (!Array.isArray(imagenes) || imagenes.length === 0) {
                console.error("No se encontraron imágenes destacadas.");
                return;
            }

            const contenedor = document.querySelector('.contenedor-img-coleccion');
            if (!contenedor) {
                console.error("No se encontró el contenedor de imágenes.");
                return;
            }

            contenedor.innerHTML = ''; // Limpia el contenido existente

            // Crear el primer contenedor con el elemento insertar-img
            contenedor.innerHTML += `
                <div class="img-coleccion">
                    <a href="http://127.0.0.1/finoso/informacion/informacion.html?id_reloj=2">
                        <img src="img/Patk Phlppe Bicolor Dorado - Negro.png" alt="Colección" id="insertar-img" style="display: none;">
                    </a>
                </div>
            `;

            // Agregar las demás imágenes (excluyendo el id 2)
            imagenes.forEach(img => {
                if (img.id_reloj != 2) {
                    contenedor.innerHTML += `
                        <div class="img-coleccion">
                            <a href="http://127.0.0.1/finoso/informacion/informacion.html?id_reloj=${img.id_reloj}">
                                <img src="${img.img}" alt="Reloj colección">
                            </a>
                        </div>
                    `;
                }
            });

            imagenesDestacadasCargadas = true;
            console.log("Imágenes destacadas cargadas correctamente");
        })
        .catch(err => {
            console.error("Error al cargar las imágenes destacadas:", err);
        });
}

function updateOnScroll() {
    const scrollTop = window.pageYOffset || document.documentElement.scrollTop;
    const maxScroll = document.documentElement.scrollHeight - window.innerHeight;
    const scrollProgress = Math.min(scrollTop / maxScroll, 1);

    // Barra de progreso
    if (progressBar) {
        progressBar.style.width = (scrollProgress * 100) + '%';
    }

    // Interpolación de keyframes
    const totalSegments = keyframes.length - 1;
    const currentSegment = Math.floor(scrollProgress * totalSegments);
    const nextSegment = Math.min(currentSegment + 1, totalSegments);
    const segmentProgress = (scrollProgress * totalSegments) - currentSegment;

    const transforms = interpolateKeyframes(
        keyframes[currentSegment],
        keyframes[nextSegment],
        segmentProgress
    );

    if (mainElement) {
        applyTransforms(transforms);
    }

    // Dots activos
    dots.forEach((dot, index) => {
        const currentSectionIndex = Math.round(scrollProgress * (dots.length - 1));
        dot.classList.toggle('active', index === currentSectionIndex);
    });

    // Control de visibilidad de elementos
    const sectionSize = 1 / (dots.length - 1);
    const activationOffset = 0; // Aquí cambias el % que quieras (0.1 = 10%)

    const start = sectionSize * 1 + sectionSize * activationOffset; // sección 2 + offset
    const end = sectionSize * 2;

    // Obtener el elemento insertar-img actualizado (por si se recargó el contenedor)
    const insertarImgElement = document.getElementById('insertar-img');

    if (scrollProgress >= start && scrollProgress < end) {
        if (mainElement) mainElement.style.display = 'none';
        if (insertarImgElement) insertarImgElement.style.display = 'flex';
    } else {
        if (mainElement) mainElement.style.display = 'flex';
        if (insertarImgElement) insertarImgElement.style.display = 'none';
    }
}

// Inicialización cuando el DOM esté listo
document.addEventListener('DOMContentLoaded', function() {
    // Cargar las imágenes destacadas primero
    cargarImagenesDestacadas().then(() => {
        // Una vez cargadas las imágenes, inicializar el scroll
        updateOnScroll();
    });
});

// Event listeners
window.addEventListener('scroll', updateOnScroll);

// Navegación con dots
dots.forEach((dot, index) => {
    dot.addEventListener('click', () => {
        const targetScroll = (index / (dots.length - 1)) * (document.documentElement.scrollHeight - window.innerHeight);
        window.scrollTo({ top: targetScroll, behavior: 'smooth' });
    });
});

// Teclado
document.addEventListener('keydown', (e) => {
    const currentScroll = window.pageYOffset;
    const maxScroll = document.documentElement.scrollHeight - window.innerHeight;
    const step = maxScroll / (dots.length - 1);

    if (e.key === 'ArrowDown') {
        window.scrollTo({
            top: Math.min(currentScroll + step, maxScroll),
            behavior: 'smooth'
        });
    } else if (e.key === 'ArrowUp') {
        window.scrollTo({
            top: Math.max(currentScroll - step, 0),
            behavior: 'smooth'
        });
    }
});

// Adaptar keyframes cuando cambia el tamaño de pantalla
window.addEventListener('resize', () => {
    keyframes = getKeyframes();
    updateOnScroll();
});