/**
 * SISTEMA DE FILTROS EN MODAL PARA MÓVIL
 * Mejora UX/UI para dispositivos móviles
 */

document.addEventListener('DOMContentLoaded', () => {
    const btnAbrirFiltros = document.getElementById('btnAbrirFiltros');
    const btnCerrarFiltros = document.getElementById('btnCerrarFiltros');
    const btnAplicarFiltros = document.getElementById('btnAplicarFiltros');
    const btnLimpiarFiltrosModal = document.getElementById('btnLimpiarFiltrosModal');
    const modalFiltros = document.getElementById('modalFiltros');
    const modalFiltrosBody = document.querySelector('.modal-filtros-body');
    const filtrosRow = document.querySelector('.filtros-row');
    
    function clonarFiltrosAlModal() {
        if (!modalFiltrosBody || !filtrosRow) return;

        modalFiltrosBody.innerHTML = '';

        const filtros = filtrosRow.querySelectorAll('.filtro-group');
        filtros.forEach(filtro => {
            const clon = filtro.cloneNode(true);
            const selectOriginal = filtro.querySelector('select');
            const selectClon = clon.querySelector('select');

            if (selectClon) {
                if (selectClon.hasAttribute('id')) {
                    selectClon.setAttribute('data-original-id', selectClon.id);
                    selectClon.removeAttribute('id');
                }

                if (selectOriginal) {
                    selectClon.innerHTML = selectOriginal.innerHTML;
                    selectClon.value = selectOriginal.value;
                }
            }

            modalFiltrosBody.appendChild(clon);
        });
    }

    // Detectar si es móvil
    function esMobil() {
        return window.innerWidth <= 768;
    }
    
    // Mostrar/ocultar botón según dispositivo
    function actualizarVistaMobil() {
        if (esMobil()) {
            // MÓVIL: Mostrar botón, ocultar filtros inline
            if (btnAbrirFiltros) {
                btnAbrirFiltros.style.display = 'flex';
            }
            
            // Clonar filtros al modal si no están ya
            clonarFiltrosAlModal();
            sincronizarFiltros();
        } else {
            // DESKTOP: Ocultar botón y modal, mostrar filtros inline
            if (btnAbrirFiltros) {
                btnAbrirFiltros.style.display = 'none';
            }
            
            if (modalFiltros) {
                modalFiltros.classList.remove('activo');
                modalFiltros.style.display = 'none';
            }
            
            // Restaurar scroll del body por si acaso
            document.body.style.overflow = '';
        }
    }
    
    // Sincronizar valores entre filtros originales y modal
    function sincronizarFiltros() {
        const filtrosOriginales = filtrosRow.querySelectorAll('select');
        const filtrosModal = modalFiltrosBody.querySelectorAll('select[data-original-id]');

        filtrosModal.forEach((selectModal) => {
            const originalId = selectModal.getAttribute('data-original-id');
            if (!originalId) return;
            const selectOriginal = Array.from(filtrosOriginales).find(sel => sel.id === originalId);
            if (selectOriginal) {
                selectModal.innerHTML = selectOriginal.innerHTML;
                selectModal.value = selectOriginal.value;
            }
        });
    }
    
    // Abrir modal
    if (btnAbrirFiltros) {
        btnAbrirFiltros.addEventListener('click', () => {
            if (modalFiltros) {
                clonarFiltrosAlModal();
                sincronizarFiltros();
                modalFiltros.classList.add('activo');
                document.body.style.overflow = 'hidden'; // Evitar scroll del body
            }
        });
    }
    
    // Cerrar modal
    function cerrarModal() {
        if (modalFiltros) {
            modalFiltros.classList.remove('activo');
            document.body.style.overflow = ''; // Restaurar scroll
        }
    }
    
    if (btnCerrarFiltros) {
        btnCerrarFiltros.addEventListener('click', cerrarModal);
    }
    
    // Cerrar al hacer clic fuera del contenido
    if (modalFiltros) {
        modalFiltros.addEventListener('click', (e) => {
            if (e.target === modalFiltros) {
                cerrarModal();
            }
        });
    }
    
    // Aplicar filtros
    if (btnAplicarFiltros) {
        btnAplicarFiltros.addEventListener('click', () => {
            // Copiar valores del modal a los filtros originales
            const filtrosModal = modalFiltrosBody.querySelectorAll('select[data-original-id]');
            
            filtrosModal.forEach((selectModal) => {
                const originalId = selectModal.getAttribute('data-original-id');
                if (!originalId) return;
                const selectOriginal = filtrosRow.querySelector(`#${originalId}`);
                if (selectOriginal) {
                    selectOriginal.value = selectModal.value;
                    // Disparar evento change para que se apliquen los filtros
                    const event = new Event('change', { bubbles: true });
                    selectOriginal.dispatchEvent(event);
                }
            });
            
            cerrarModal();
        });
    }
    
    // Limpiar filtros en modal
    if (btnLimpiarFiltrosModal) {
        btnLimpiarFiltrosModal.addEventListener('click', () => {
            const filtrosModal = modalFiltrosBody.querySelectorAll('select[data-original-id]');
            filtrosModal.forEach(select => {
                select.value = '';
            });
        });
    }
    
    // Ejecutar al cargar y al redimensionar
    actualizarVistaMobil();
    window.addEventListener('resize', actualizarVistaMobil);
    
    console.log('✅ Sistema de filtros modal para móvil cargado');
});

