// Sistema de Comentarios para Finoso
class SistemaComentarios {
    constructor() {
        this.idReloj = this.obtenerIdReloj();
        this.comentarios = [];
        this.estadisticas = null;
        this.init();
    }

    init() {
        if (!this.idReloj) {
            console.error('No se pudo obtener el ID del reloj');
            return;
        }

        // Verificar sesión y configurar formulario (con delay para asegurar DOM)
        setTimeout(async () => {
            await this.verificarSesion();
        }, 100);
        
        // Verificación adicional después de un tiempo más largo
        setTimeout(async () => {
            await this.verificarSesion();
        }, 500);
        
        // Esperar a que el contenedor de comentarios existentes esté disponible
        this.esperarContenedorComentarios();
        this.configurarEventos();
        this.configurarContadorCaracteres();
    }

    esperarContenedorComentarios() {
        let intentos = 0;
        const maxIntentos = 20; // 10 segundos máximo
        
        const verificarContenedor = () => {
            const contenedor = document.getElementById('comentarios-existente');
            if (contenedor) {
                console.log('Contenedor de comentarios encontrado, cargando comentarios...');
                // Pequeño delay adicional para asegurar que el DOM esté completamente renderizado
                setTimeout(() => {
                    this.cargarComentarios();
                }, 100);
            } else {
                intentos++;
                if (intentos >= maxIntentos) {
                    console.error('No se pudo encontrar el contenedor de comentarios después de', maxIntentos, 'intentos');
                    return;
                }
                console.log(`Esperando contenedor de comentarios... (intento ${intentos}/${maxIntentos})`);
                setTimeout(verificarContenedor, 500);
            }
        };
        
        verificarContenedor();
    }

    obtenerIdReloj() {
        const urlParams = new URLSearchParams(window.location.search);
        return urlParams.get('id_reloj');
    }

    async verificarSesion() {
        const campoNombre = document.getElementById('campo-nombre-usuario');
        console.log('Campo de nombre encontrado:', campoNombre);
        
        try {
            // Verificar sesión usando el endpoint PHP
            const response = await fetch('https://finoso.store/login/php/verificar_sesion.php');
            const data = await response.json();
            
            console.log('Respuesta de verificación de sesión:', data);
            
            if (data.logged_in && data.nombre && campoNombre) {
                // Usuario logueado - ocultar campo de nombre
                campoNombre.classList.add('oculto');
                console.log('✅ Usuario logueado:', data.nombre, '- Campo de nombre oculto');
                
                // Guardar datos en localStorage para uso posterior
                localStorage.setItem('usuario_data', JSON.stringify({
                    id_usuario: data.id_usuario,
                    nombre: data.nombre,
                    correo: data.correo
                }));
            } else {
                // No hay sesión - mostrar campo de nombre
                if (campoNombre) {
                    campoNombre.classList.remove('oculto');
                }
                console.log('❌ No hay sesión activa - Campo de nombre visible');
            }
        } catch (error) {
            console.error('Error al verificar sesión:', error);
            // En caso de error, mostrar el campo
            if (campoNombre) {
                campoNombre.classList.remove('oculto');
            }
        }
    }

    async cargarComentarios() {
        try {
            const response = await fetch(`php/obtener_comentarios.php?id_reloj=${this.idReloj}`);
            const data = await response.json();

            if (data.success) {
                this.comentarios = data.comentarios;
                this.estadisticas = data.estadisticas;
                this.mostrarComentarios();
                this.actualizarCalificacionPromedio();
            } else {
                this.mostrarError('Error al cargar comentarios: ' + data.error);
            }
        } catch (error) {
            console.error('Error:', error);
            this.mostrarError('Error de conexión al cargar comentarios');
        }
    }

    mostrarComentarios() {
        const listaComentarios = document.getElementById('comentarios-existente');
        
        if (!listaComentarios) {
            console.error('No se encontró el contenedor de comentarios existentes');
            return;
        }
        
        console.log('Mostrando comentarios:', this.comentarios.length, 'comentarios encontrados');
        
        if (this.comentarios.length === 0) {
            listaComentarios.innerHTML = `
                <div class="sin-comentarios-existente">
                    <i class="fas fa-comments"></i>
                    <h3>No hay comentarios aún</h3>
                    <p>¡Sé el primero en compartir tu experiencia con este reloj!</p>
                </div>
            `;
            return;
        }

        const htmlComentarios = this.comentarios.map(comentario => this.crearHTMLComentario(comentario)).join('');
        listaComentarios.innerHTML = `
            <div class="comentarios-header-existente">
                <h2>Comentarios de Clientes</h2>
                <div class="calificacion-promedio-existente">
                    <div class="estrellas-promedio-existente">
                        <span class="estrella">★</span>
                        <span class="estrella">★</span>
                        <span class="estrella">★</span>
                        <span class="estrella">★</span>
                        <span class="estrella">★</span>
                    </div>
                    <span class="promedio-texto-existente">${this.estadisticas.calificacion_promedio} (${this.estadisticas.total_comentarios} comentarios)</span>
                </div>
            </div>
            <div class="lista-comentarios-existente">
                ${htmlComentarios}
            </div>
        `;
        
        // Actualizar las estrellas del promedio
        this.actualizarEstrellasExistentes();
        console.log('Comentarios mostrados correctamente');
    }

    crearHTMLComentario(comentario) {
        const fecha = new Date(comentario.fecha).toLocaleDateString('es-ES', {
            year: 'numeric',
            month: 'long',
            day: 'numeric'
        });

        const estrellas = this.crearEstrellasHTML(comentario.calificacion);

        return `
            <div class="comentario-item-existente">
                <div class="comentario-header-existente-item">
                    <div class="comentario-usuario-existente">
                        <div class="avatar-usuario-existente">
                            <i class="fas fa-user"></i>
                        </div>
                        <div class="info-usuario-existente">
                            <h4>${this.escapeHtml(comentario.nombre)}</h4>
                            <span class="fecha-comentario-existente">${fecha}</span>
                        </div>
                    </div>
                    <div class="calificacion-comentario-existente">
                        ${estrellas}
                    </div>
                </div>
                <div class="comentario-texto-existente">
                    <p>${this.escapeHtml(comentario.comentario)}</p>
                </div>
            </div>
        `;
    }

    crearEstrellasHTML(calificacion) {
        let estrellas = '';
        for (let i = 1; i <= 5; i++) {
            const clase = i <= calificacion ? 'estrella-llena' : 'estrella-vacia';
            estrellas += `<span class="estrella ${clase}">★</span>`;
        }
        return estrellas;
    }

    actualizarCalificacionPromedio() {
        // Ya no actualizamos la calificación promedio en el formulario
        // Solo se muestra en los comentarios existentes
        return;
    }

    actualizarEstrellasExistentes() {
        if (!this.estadisticas) return;

        const { calificacion_promedio } = this.estadisticas;
        const estrellasPromedioExistente = document.querySelector('.estrellas-promedio-existente');
        
        if (estrellasPromedioExistente) {
            const estrellas = estrellasPromedioExistente.querySelectorAll('.estrella');
            estrellas.forEach((estrella, index) => {
                if (index < Math.floor(calificacion_promedio)) {
                    estrella.classList.add('estrella-llena');
                    estrella.classList.remove('estrella-vacia');
                } else {
                    estrella.classList.add('estrella-vacia');
                    estrella.classList.remove('estrella-llena');
                }
            });
        }
    }

    configurarEventos() {
        const formComentario = document.getElementById('form-comentario');
        const botonEnviar = document.querySelector('.btn-enviar-comentario');
        console.log('🔍 DEBUG comentarios.js - Formulario encontrado:', formComentario);
        console.log('🔍 DEBUG comentarios.js - Botón encontrado:', botonEnviar);
        
        if (botonEnviar) {
            botonEnviar.addEventListener('click', (e) => {
                console.log('🔍 DEBUG comentarios.js - Click en botón detectado');
                this.enviarComentario(e);
            });
            console.log('🔍 DEBUG comentarios.js - Event listener agregado al botón');
        } else {
            console.error('🔍 DEBUG comentarios.js - No se encontró el botón .btn-enviar-comentario');
        }

        // Configurar estrellas interactivas
        const estrellasInput = document.querySelectorAll('.estrella-input');
        estrellasInput.forEach((estrella, index) => {
            estrella.addEventListener('click', () => this.marcarEstrellas(index + 1));
            estrella.addEventListener('mouseenter', () => this.resaltarEstrellas(index + 1));
        });

        const contenedorEstrellas = document.querySelector('.estrellas-input');
        contenedorEstrellas.addEventListener('mouseleave', () => this.resetearEstrellas());
    }

    configurarContadorCaracteres() {
        const textarea = document.getElementById('texto-comentario');
        const contador = document.getElementById('contador');

        textarea.addEventListener('input', () => {
            const longitud = textarea.value.length;
            contador.textContent = longitud;
            
            if (longitud > 450) {
                contador.style.color = '#ff6b6b';
            } else if (longitud > 400) {
                contador.style.color = '#ffa726';
            } else {
                contador.style.color = '#666';
            }
        });
    }

    marcarEstrellas(cantidad) {
        const estrellas = document.querySelectorAll('.estrella-input');
        estrellas.forEach((estrella, index) => {
            if (index < cantidad) {
                estrella.classList.add('estrella-seleccionada');
            } else {
                estrella.classList.remove('estrella-seleccionada');
            }
        });
    }

    resaltarEstrellas(cantidad) {
        const estrellas = document.querySelectorAll('.estrella-input');
        estrellas.forEach((estrella, index) => {
            if (index < cantidad) {
                estrella.classList.add('estrella-hover');
            } else {
                estrella.classList.remove('estrella-hover');
            }
        });
    }

    resetearEstrellas() {
        const estrellas = document.querySelectorAll('.estrella-input');
        estrellas.forEach(estrella => {
            estrella.classList.remove('estrella-hover');
        });
    }

    async enviarComentario(event) {
        console.log('🔍 DEBUG comentarios.js - Método enviarComentario ejecutado');
        console.log('🔍 DEBUG comentarios.js - Evento original:', event);
        event.preventDefault();
        event.stopPropagation();
        console.log('🔍 DEBUG comentarios.js - Evento prevenido y propagación detenida');

        const formComentario = document.getElementById('form-comentario');
        const formData = new FormData(formComentario);
        const calificacion = formData.get('calificacion');
        const comentario = formData.get('comentario');
        
        console.log('🔍 DEBUG comentarios.js - Datos del formulario:');
        console.log('- calificacion:', calificacion);
        console.log('- comentario:', comentario);

        // Validaciones
        if (!calificacion) {
            this.mostrarMensaje('Por favor selecciona una calificación', 'error');
            return;
        }

        if (!comentario.trim() || comentario.length < 10) {
            this.mostrarMensaje('El comentario debe tener al menos 10 caracteres', 'error');
            return;
        }

        // Obtener datos del usuario
        let idUsuario, nombreUsuario;
        try {
            const datosUsuario = await this.obtenerDatosUsuario();
            idUsuario = datosUsuario.idUsuario;
            nombreUsuario = datosUsuario.nombreUsuario;
            
            console.log('🔍 DEBUG comentarios.js - Datos del usuario obtenidos:');
            console.log('- idUsuario:', idUsuario);
            console.log('- nombreUsuario:', nombreUsuario);
        } catch (error) {
            console.error('🔍 DEBUG comentarios.js - Error al obtener datos del usuario:', error);
            this.mostrarMensaje(error.message, 'error');
            return;
        }

        const datosComentario = {
            id_reloj: this.idReloj,
            id_usuario: idUsuario,
            nombre_usuario: nombreUsuario,
            calificacion: parseInt(calificacion),
            comentario: comentario.trim()
        };
        
        console.log('🔍 DEBUG comentarios.js - Datos a enviar:', datosComentario);

        try {
            const response = await fetch('php/agregar_comentario.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(datosComentario)
            });

            const data = await response.json();
            
            console.log('🔍 DEBUG comentarios.js - Respuesta del servidor:', data);

            if (data.success) {
                this.mostrarMensaje(data.message, 'success');
                formComentario.reset();
                this.resetearEstrellas();
                document.getElementById('contador').textContent = '0';
                // Recargar comentarios después de un breve delay
                setTimeout(() => this.cargarComentarios(), 1000);
            } else {
                console.error('🔍 DEBUG comentarios.js - Error del servidor:', data.error);
                this.mostrarMensaje(data.error, 'error');
            }
        } catch (error) {
            console.error('Error:', error);
            this.mostrarMensaje('Error de conexión al enviar comentario', 'error');
        }
    }

    async obtenerDatosUsuario() {
        // Primero intentar desde localStorage
        const usuarioData = localStorage.getItem('usuario_data');
        const campoNombre = document.getElementById('nombre-comentario');
        
        if (usuarioData) {
            try {
                const usuario = JSON.parse(usuarioData);
                if (usuario.nombre) {
                    // Usuario logueado - usar datos de la sesión
                    return {
                        idUsuario: usuario.id_usuario || null,
                        nombreUsuario: usuario.nombre
                    };
                }
            } catch (e) {
                console.error('Error al parsear datos de usuario:', e);
            }
        }
        
        // Si no hay datos en localStorage, verificar sesión PHP
        try {
            const response = await fetch('https://finoso.store/login/php/verificar_sesion.php');
            const data = await response.json();
            
            if (data.logged_in && data.nombre) {
                // Usuario logueado - usar datos de la sesión PHP
                return {
                    idUsuario: data.id_usuario || null,
                    nombreUsuario: data.nombre
                };
            }
        } catch (error) {
            console.error('Error al verificar sesión PHP:', error);
        }
        
        // Si no hay sesión, usar el campo de nombre
        const nombreDelCampo = campoNombre ? campoNombre.value.trim() : '';
        if (!nombreDelCampo) {
            throw new Error('Por favor ingresa tu nombre');
        }
        
        return {
            idUsuario: null,
            nombreUsuario: nombreDelCampo
        };
    }

    async obtenerIdUsuario() {
        // Método legacy - mantener para compatibilidad
        const datosUsuario = await this.obtenerDatosUsuario();
        return datosUsuario.idUsuario;
    }

    mostrarMensaje(mensaje, tipo) {
        console.log('🔍 DEBUG comentarios.js - mostrarMensaje ejecutado:', mensaje, tipo);
        // Crear o actualizar mensaje
        let mensajeElement = document.querySelector('.mensaje-comentario');
        
        if (!mensajeElement) {
            console.log('🔍 DEBUG comentarios.js - Creando nuevo elemento de mensaje');
            mensajeElement = document.createElement('div');
            mensajeElement.className = 'mensaje-comentario';
            const formulario = document.querySelector('.formulario-comentario');
            console.log('🔍 DEBUG comentarios.js - Formulario encontrado:', formulario);
            if (formulario) {
                formulario.appendChild(mensajeElement);
            } else {
                console.error('🔍 DEBUG comentarios.js - No se encontró .formulario-comentario');
            }
        }

        mensajeElement.textContent = mensaje;
        mensajeElement.className = `mensaje-comentario ${tipo}`;
        mensajeElement.style.display = 'block';

        // Ocultar después de 5 segundos
        setTimeout(() => {
            mensajeElement.style.display = 'none';
        }, 5000);
    }

    mostrarError(mensaje) {
        const listaComentarios = document.getElementById('comentarios-existente');
        if (listaComentarios) {
            listaComentarios.innerHTML = `
                <div class="error-comentarios">
                    <i class="fas fa-exclamation-triangle"></i>
                    <h3>Error al cargar comentarios</h3>
                    <p>${mensaje}</p>
                    <button onclick="sistemaComentarios.cargarComentarios()" class="btn-reintentar">
                        <i class="fas fa-redo"></i>
                        Reintentar
                    </button>
                </div>
            `;
        }
    }

    escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    // Método público para forzar verificación de sesión
    async forzarVerificacionSesion() {
        console.log('🔄 Forzando verificación de sesión...');
        await this.verificarSesion();
    }
}

// Inicializar el sistema cuando se carga la página
let sistemaComentarios;

// Exponer globalmente para debugging
window.sistemaComentarios = null;

// Función para inicializar cuando el DOM esté listo
function inicializarSistemaComentarios() {
    if (!sistemaComentarios) {
        sistemaComentarios = new SistemaComentarios();
        window.sistemaComentarios = sistemaComentarios; // Exponer para debugging
    }
}

// Intentar inicializar inmediatamente
document.addEventListener('DOMContentLoaded', inicializarSistemaComentarios);

// También intentar después de un delay para asegurar que todo esté cargado
setTimeout(() => {
    if (!sistemaComentarios) {
        console.log('Inicializando sistema de comentarios con delay...');
        inicializarSistemaComentarios();
    }
}, 2000);

// Observador de mutaciones para detectar cuando se crea el contenedor
const observer = new MutationObserver((mutations) => {
    mutations.forEach((mutation) => {
        if (mutation.type === 'childList') {
            mutation.addedNodes.forEach((node) => {
                if (node.nodeType === 1 && node.id === 'comentarios-existente') {
                    console.log('Contenedor de comentarios detectado por MutationObserver');
                    if (sistemaComentarios && sistemaComentarios.comentarios.length > 0) {
                        sistemaComentarios.mostrarComentarios();
                    }
                }
            });
        }
    });
});

// Iniciar observación cuando el DOM esté listo
document.addEventListener('DOMContentLoaded', () => {
    observer.observe(document.body, {
        childList: true,
        subtree: true
    });
});
