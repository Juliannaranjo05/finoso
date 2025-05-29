    const mainElement = document.getElementById('mainElement');
    const progressBar = document.getElementById('progressBar');
    const dots = document.querySelectorAll('.dot');
    const insertarImg = document.getElementById('insertar-img');

    let imagenReemplazada = false;

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

    function updateOnScroll() {
        const scrollTop = window.pageYOffset || document.documentElement.scrollTop;
        const maxScroll = document.documentElement.scrollHeight - window.innerHeight;
        const scrollProgress = Math.min(scrollTop / maxScroll, 1);

        // Barra de progreso
        progressBar.style.width = (scrollProgress * 100) + '%';

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

        applyTransforms(transforms);

        // Dots activos
        const currentSectionIndex = Math.round(scrollProgress * (dots.length - 1));
        dots.forEach((dot, index) => {
            dot.classList.toggle('active', index === currentSectionIndex);
        });

        const sectionSize = 1 / (dots.length - 1);
        const activationOffset = 0; // ✅ Aquí cambias el % que quieras (0.1 = 10%)

        const start = sectionSize * 1 + sectionSize * activationOffset; // sección 2 + 10%
        const end = sectionSize * 2;

        if (scrollProgress >= start && scrollProgress < end) {
            if (mainElement) mainElement.style.display = 'none';
            if (insertarImg) insertarImg.style.display = 'flex';
        } else {
            if (mainElement) mainElement.style.display = 'flex';
            if (insertarImg) insertarImg.style.display = 'none';
        }
    }

    window.addEventListener('scroll', updateOnScroll);

    // Navegación con dots
    dots.forEach((dot, index) => {
        dot.addEventListener('click', () => {
            const targetScroll = (index / (dots.length - 1)) * (document.documentElement.scrollHeight - window.innerHeight);
            window.scrollTo({ top: targetScroll, behavior: 'smooth' });
        });
    });

    // Botón de catálogo
    document.getElementById('ver-catalogo').addEventListener('click', () => {
        alert('Redirigiendo al catálogo...');
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

    // Inicial
    updateOnScroll();
