    const mainElement = document.getElementById('mainElement');
    const progressBar = document.getElementById('progressBar');
    const dots = document.querySelectorAll('.dot');

    // Función que devuelve keyframes según el tamaño de pantalla
    function getKeyframes() {
        const width = window.innerWidth;

        if (width >= 1200) {
            // Escritorio grande
            return [
                { x: 60, y: 55, rotation: 30, scale: 1.5 },
                { x: 20, y: 12, rotation: 5, scale: 0.8 },
            ];
        } else if (width < 1199) {
            // Tablet
            return [
                { x: 70, y: 50, rotation: 20, scale: 1.4 },
                { x: 15, y: 23, rotation: 5, scale: 0.7 },
            ];
        } else if (width >= 820) {
            // Tablet
            return [
                { x: 70, y: 50, rotation: 20, scale: 1.2 },
                { x: 15, y: 23, rotation: 5, scale: 0.5 },
            ];
        } else if (width >= 768) {
            // Tablet
            return [
                { x: 50, y: 50, rotation: 20, scale: 1.2 },
                { x: 15, y: 20, rotation: 5, scale: 0.7 },
            ];
        } else {
            // Móvil
            return [
                { x: 40, y: 50, rotation: 10, scale: 1 },
                { x: 10, y: 25, rotation: 2, scale: 0.7 },
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

        // Segmento actual
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
    }

    window.addEventListener('scroll', updateOnScroll);

    // Navegación con dots
    dots.forEach((dot, index) => {
        dot.addEventListener('click', () => {
            const targetScroll = (index / (dots.length - 1)) * (document.documentElement.scrollHeight - window.innerHeight);
            window.scrollTo({
                top: targetScroll,
                behavior: 'smooth'
            });
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
