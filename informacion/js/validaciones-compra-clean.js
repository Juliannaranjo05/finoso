document.addEventListener("DOMContentLoaded", function () {
    const metodoPagoInput = document.getElementById('metodo-pago');
    const errorMetodo = document.getElementById('error-metodo-pago');
    const form = document.getElementById("form-envio");
    const departamentoSelect = document.getElementById("departamento");
    const ciudadSelect = document.getElementById("ciudad");
    const checkbox = document.getElementById('guardar-info');

    const STORAGE_KEY = 'form_data_finoso';

    // Cargar departamentos desde la base de datos al iniciar
    async function cargarDepartamentos() {
        try {
            const response = await fetch('http://127.0.0.1/finoso/informacion/php/obtener_ciudades.php?action=departamentos');
            const data = await response.json();
            
            if (data.success && data.departamentos) {
                departamentoSelect.innerHTML = '<option value="">Seleccionar departamento</option>';
                data.departamentos.forEach(depto => {
                    const option = document.createElement('option');
                    option.value = depto;
                    option.textContent = depto;
                    departamentoSelect.appendChild(option);
                });
            }
        } catch (error) {
            console.error('Error al cargar departamentos:', error);
        }
    }

    // Cargar ciudades cuando se selecciona un departamento
    async function cargarCiudades(departamento) {
        if (!departamento) {
            ciudadSelect.innerHTML = '<option value="">Seleccionar ciudad</option>';
            ciudadSelect.disabled = true;
            return;
        }

        try {
            const response = await fetch(`http://127.0.0.1/finoso/informacion/php/obtener_ciudades.php?action=ciudades&departamento=${encodeURIComponent(departamento)}`);
            const data = await response.json();
            
            if (data.success && data.ciudades) {
                ciudadSelect.innerHTML = '<option value="">Seleccionar ciudad</option>';
                data.ciudades.forEach(item => {
                    const option = document.createElement('option');
                    option.value = item.ciudad;
                    option.textContent = item.ciudad;
                    option.dataset.precio = item.precio;
                    option.dataset.dias = item.dias_estimados;
                    ciudadSelect.appendChild(option);
                });
                ciudadSelect.disabled = false;
            } else {
                ciudadSelect.innerHTML = '<option value="">No hay ciudades disponibles</option>';
                ciudadSelect.disabled = true;
            }
        } catch (error) {
            console.error('Error al cargar ciudades:', error);
            ciudadSelect.innerHTML = '<option value="">Error al cargar ciudades</option>';
            ciudadSelect.disabled = true;
        }
    }

    // Listener para cambio de departamento
    departamentoSelect.addEventListener('change', () => {
        const departamento = departamentoSelect.value;
        cargarCiudades(departamento);
        // Limpiar precio de envío
        const precioEnvio = document.getElementById("precio-envio");
        if (precioEnvio) precioEnvio.textContent = '0';
        // Guardar formulario
        saveFormData();
    });

    // Listener para cambio de ciudad
    ciudadSelect.addEventListener('change', () => {
        const ciudad = ciudadSelect.value;
        const departamento = departamentoSelect.value;
        if (ciudad && departamento) {
            calcularEnvio(ciudad, departamento);
        }
        saveFormData();
    });

    // Función para calcular envío
    async function calcularEnvio(ciudad, departamento) {
        try {
            const res = await fetch('http://127.0.0.1/finoso/informacion/php/consultar_precio_envio.php?ciudad=' + ciudad + '&departamento=' + departamento);
            const data = await res.json();

            if (data.status === "ok") {
                const precioEnvio = document.getElementById("precio-envio");
                
                if (precioEnvio) {
                    const precio = parseFloat(data.precio);
                    precioEnvio.textContent = precio.toLocaleString('es-CO', {
                        minimumFractionDigits: 0,
                        maximumFractionDigits: 0
                    });
                    console.log('✅ Precio de envío actualizado:', precio, 'COP');
                }
            } else {
                console.warn('⚠️ No hay cobertura para esta ciudad');
                alert("No tenemos cobertura aún para esta ciudad.");
            }
        } catch (error) {
            console.error('Error al calcular envío:', error);
        }
    }

    // Inicializar departamentos al cargar la página
    cargarDepartamentos();

    // Guardar datos del formulario
    function saveFormData() {
        const data = {
            nombre: document.getElementById('nombre').value,
            cedula: document.getElementById('cedula').value,
            celular: document.getElementById('celular').value,
            correo: document.getElementById('correo').value,
            departamento: departamentoSelect.value,
            ciudad: ciudadSelect.value,
            direccion: document.getElementById('direccion').value,
            barrio: document.getElementById('barrio').value,
            referencias: document.getElementById('referencias').value,
            metodo_pago: metodoPagoInput.value
        };

        // Si el método de pago es Nequi o Wompi, también guardar el correo
        if (metodoPagoInput.value === "nequi" || metodoPagoInput.value === "wompi") {
            data.correo = document.getElementById('correo').value;
        }

        localStorage.setItem(STORAGE_KEY, JSON.stringify(data));
    }

    // Aplicar visualmente el método de pago guardado
    function aplicarMetodoPagoVisualmente() {
        const metodoGuardado = metodoPagoInput.value;

        // Limpiar selección previa
        document.querySelectorAll('.boton-metodo').forEach(btn => {
            btn.classList.remove('activo');
        });

        // Aplicar clase activa al botón correspondiente
        const botonActivo = document.querySelector(`.boton-metodo[data-metodo="${metodoGuardado}"]`);
        if (botonActivo) {
            botonActivo.classList.add('activo');
        }
    }

    function loadFormData() {
        const saved = localStorage.getItem(STORAGE_KEY);
        if (!saved) return;

        try {
            const data = JSON.parse(saved);
            
            // Llenar campos del formulario
            if (data.nombre) document.getElementById('nombre').value = data.nombre;
            if (data.cedula) document.getElementById('cedula').value = data.cedula;
            if (data.celular) document.getElementById('celular').value = data.celular;
            if (data.correo) document.getElementById('correo').value = data.correo;
            if (data.direccion) document.getElementById('direccion').value = data.direccion;
            if (data.barrio) document.getElementById('barrio').value = data.barrio;
            if (data.referencias) document.getElementById('referencias').value = data.referencias;
            
            // Departamento y ciudad
            if (data.departamento) {
                departamentoSelect.value = data.departamento;
                cargarCiudades(data.departamento).then(() => {
                    if (data.ciudad) {
                        ciudadSelect.value = data.ciudad;
                        calcularEnvio(data.ciudad, data.departamento);
                    }
                });
            }
            
            // Método de pago
            if (data.metodo_pago) {
                metodoPagoInput.value = data.metodo_pago;
                document.querySelectorAll('.boton-metodo img').forEach(img => {
                    img.addEventListener('click', function () {
                        const metodo = this.dataset.metodo;
                        metodoPagoInput.value = metodo;
                        console.log("✅ Método de pago seleccionado:", metodo);

                        // Quitar selección previa
                        document.querySelectorAll('.boton-metodo').forEach(btn => {
                            btn.classList.remove('activo');
                        });

                        // Aplicar clase activa al seleccionado
                        const contenedor = this.closest('.boton-metodo');
                        if (contenedor) contenedor.classList.add('activo');

                        const campoCorreo = document.getElementById("campo-correo");
                        const correoInput = document.getElementById("correo");

                        // Si el método es Nequi, verificar si hay sesión
                        if (metodo === "nequi") {
                            fetch("http://127.0.0.1/finoso/informacion/php/verificar_sesion.php")
                                .then(res => res.json())
                                .then(respuesta => {
                                    if (respuesta.logged_in) {
                                        // Hay sesión, no mostrar campo correo
                                        campoCorreo.style.display = "none";
                                        correoInput.required = false;
                                        correoInput.value = respuesta.correo;
                                    } else {
                                        // No hay sesión, sí mostrar
                                        campoCorreo.style.display = "block";
                                        correoInput.required = true;
                                        correoInput.value = "";
                                    }
                                });
                        } else {
                            // Para Wompi, siempre mostrar campo correo
                            campoCorreo.style.display = "block";
                            correoInput.required = true;
                        }

                        // Guardar si el checkbox está activo
                        if (checkbox.checked) saveFormData();
                    });
                });
            }
            
            checkbox.checked = true;
        } catch (error) {
            console.error('Error al cargar datos guardados:', error);
        }
    }

    // === VALIDACIONES ===
    function validateField(id, regex, message) {
        const input = document.getElementById(id);
        const error = document.getElementById("error-" + id);
        if (!regex.test(input.value.trim())) {
            error.textContent = message;
            return false;
        } else {
            error.textContent = "";
            return true;
        }
    }

    function validateOptionalField(id, regex, message) {
        const input = document.getElementById(id);
        const error = document.getElementById("error-" + id);
        if (input.value.trim() !== "" && !regex.test(input.value.trim())) {
            error.textContent = message;
            return false;
        } else {
            error.textContent = "";
            return true;
        }
    }

    // Event listener para el formulario
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        console.log("🔍 DEBUG validaciones-compra.js - CLICK en botón comprar");

        const validations = [
            validateField("nombre", /^[A-Za-zÁÉÍÓÚáéíóúñÑ\s]+$/, "Por favor, ingresa un nombre válido."),
            validateField("cedula", /^[0-9]{6,12}$/, "Por favor, ingresa una cédula válida."),
            validateField("celular", /^[0-9]{10}$/, "Por favor, ingresa un celular válido."),
            validateField("direccion", /^[A-Za-z0-9ÁÉÍÓÚáéíóúñÑ#\-\s]+$/, "Por favor, ingresa una dirección válida."),
            validateField("barrio", /^[A-Za-zÁÉÍÓÚáéíóúñÑ\s]+$/, "Por favor, ingresa un barrio válido."),
            validateOptionalField("referencias", /^[A-Za-z0-9ÁÉÍÓÚáéíóúñÑ#\-\s]*$/, "Por favor, ingresa solo letras, números y símbolos válidos (#, -).")
        ];

        if (metodoPagoInput.value === "nequi" || metodoPagoInput.value === "wompi") {
            validations.push(validateField("correo", /^[^\s@]+@[^\s@]+\.[^\s@]+$/, "Por favor, ingresa un correo válido."));
        }

        if (!validations.every(v => v)) return;

        if (!metodoPagoInput.value) {
            errorMetodo.textContent = 'Por favor, selecciona un método de pago.';
            return;
        }

        const formData = new FormData(form);
        const data = {
            nombre: formData.get('nombre'),
            cedula: formData.get('cedula'),
            celular: formData.get('celular'),
            correo: formData.get('correo'),
            departamento: formData.get('departamento'),
            ciudad: formData.get('ciudad'),
            direccion: formData.get('direccion'),
            barrio: formData.get('barrio'),
            referencias: formData.get('referencias'),
            metodo_pago: formData.get('metodo_pago')
        };

        // Obtener ID del reloj desde la URL
        data.id_reloj = new URLSearchParams(window.location.search).get("id_reloj");

        if (!data.id_reloj) {
            alert("Error: no se encontró el reloj.");
            return;
        }

        // Obtener el valor del envío desde el DOM
        const costoEnvio = parseInt(document.getElementById('precio-envio')?.textContent || 0);

        // Validar que el costo de envío sea válido
        if (costoEnvio <= 0) {
            alert('Error: No se pudo obtener el costo de envío');
            return;
        }

        // AGREGAR el costo de envío a la data existente
        data.costo_envio = costoEnvio;
        
        // Obtener precio del reloj
        if (!data.precio) {
            const precioElement = document.querySelector(".precio-normal, .precio-descuentos");
            if (precioElement) {
                const precioLimpiado = precioElement.textContent.trim().replace(/[^\d]/g, '');
                data.precio = parseInt(precioLimpiado);
            } else {
                data.precio = 0;
            }
        }

        console.log("🔍 DEBUG validaciones-compra.js - Datos preparados:", data);

        // Mostrar modal de confirmación
        mostrarModalConfirmacion(data, costoEnvio);

        // Guardar datos
        saveFormData();
    });

    // Función para mostrar modal de confirmación
    function mostrarModalConfirmacion(data, costoEnvio) {
        const total = data.precio + costoEnvio;
        
        const modalNombre = document.getElementById("modal-nombre");
        const modalCiudad = document.getElementById("modal-ciudad");
        const modalDireccion = document.getElementById("modal-direccion");
        const modalMetodo = document.getElementById("modal-metodo");
        const modalCostoTotal = document.getElementById("modal-costo-total");

        if (modalNombre) modalNombre.textContent = data.nombre;
        if (modalCiudad) modalCiudad.textContent = data.ciudad;
        if (modalDireccion) modalDireccion.textContent = `${data.direccion}, ${data.barrio}`;
        if (modalMetodo) {
            const metodo = data.metodo_pago === "nequi" ? "Nequi" : 
                          data.metodo_pago === "wompi" ? "Wompi" : "Método no válido";
            modalMetodo.textContent = metodo;
        }
        if (modalCostoTotal) modalCostoTotal.textContent = `$${total.toLocaleString('es-CO')}`;

        const resumen = `
            <strong>Cédula:</strong> ${data.cedula}<br>
            <strong>Celular:</strong> ${data.celular}<br>
            <strong>Método de pago:</strong> ${data.metodo_pago === "nequi" ? "Nequi" : 
                                             data.metodo_pago === "wompi" ? "Wompi" : "No válido"}<br>
            <strong>Precio reloj:</strong> $${parseInt(data.precio).toLocaleString()}<br>
            <strong>Envío:</strong> $${costoEnvio.toLocaleString()}
        `;

        // Mostrar modal de confirmación para todos los métodos de pago
        document.getElementById("resumen-compra").innerHTML = resumen;
        const modal = document.getElementById("modal-confirmacion");
        modal.style.display = "flex";
        let segundos = 5;
        const mensaje = document.getElementById("auto-redir-msg");
        if (mensaje) {
            mensaje.textContent = `Procesando pago en ${segundos} segundos...`;

            const countdown = setInterval(() => {
                segundos--;
                mensaje.textContent = `Procesando pago en ${segundos} segundos...`;
                if (segundos <= 0) clearInterval(countdown);
            }, 1000);
        }

        setTimeout(() => {
            cerrarModal();
            enviarFormulario(data);
        }, 5000);

        return;
    }

    // Función para enviar formulario
    function enviarFormulario(data) {
        if (data.metodo_pago === "wompi") {
            // Crear transacción con Wompi
            fetch("http://127.0.0.1/finoso/informacion/php/crear_transaccion_wompi.php", {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify(data)
            })
            .then(res => {
                if (!res.ok) {
                    return res.json().then(err => { throw err; });
                }
                return res.json();
            })
            .then(respuesta => {
                if (respuesta.error) {
                    alert(`Error: ${respuesta.error}\n${respuesta.message || ''}`);
                    return;
                }

                console.log('🧾 Desglose del pago Wompi:');
                console.log(`Reloj: ${(respuesta.amount - data.costo_envio).toLocaleString('es-CO', {style: 'currency', currency: 'COP'})}`);
                console.log(`Envío: ${data.costo_envio.toLocaleString('es-CO', {style: 'currency', currency: 'COP'})}`);
                console.log(`Total: ${respuesta.amount.toLocaleString('es-CO', {style: 'currency', currency: 'COP'})}`);

                // Abrir widget de Wompi
                const wompiWidget = new WompiWidget({
                    publicKey: respuesta.public_key,
                    amountInCents: respuesta.amount * 100, // Convertir a centavos
                    currency: 'COP',
                    reference: respuesta.reference,
                    customerEmail: data.correo || 'cliente@finoso.com',
                    customerData: {
                        fullName: data.nombre,
                        phoneNumber: data.celular
                    },
                    shippingAddress: {
                        addressLine1: data.direccion,
                        city: data.ciudad,
                        region: data.departamento,
                        country: 'CO'
                    },
                    onApproved: function(result) {
                        console.log('✅ Pago aprobado:', result);
                        // Redirigir a página de éxito
                        window.location.href = `pago_exitoso_wompi.html?orden=${result.transaction_id}&token=${result.reference}`;
                    },
                    onRejected: function(result) {
                        console.log('❌ Pago rechazado:', result);
                        // Redirigir a página de error
                        window.location.href = `pago_error_wompi.html?status=${result.status}&reason=${result.reason || 'Pago rechazado'}`;
                    },
                    onError: function(error) {
                        console.error('❌ Error en Wompi:', error);
                        alert('Error al procesar el pago. Intenta nuevamente.');
                    }
                });

                wompiWidget.open();

            })
            .catch(err => {
                console.error("❌ Error al crear transacción Wompi:", err);
                alert(`Error al crear transacción: ${err.message || err.error || 'Error desconocido'}`);
            });

        } else if (data.metodo_pago === "nequi") {
            console.log("🟣 Iniciando flujo de pago con Nequi...");

            // Verificar sesión para obtener correo
            fetch("http://127.0.0.1/finoso/informacion/php/verificar_sesion.php")
                .then(res => {
                    if (!res.ok) throw new Error("Fallo en la verificación de sesión");
                    return res.json();
                })
                .then(sesion => {
                    if (sesion.logged_in && sesion.correo) {
                        console.log("✅ Sesión activa. Usando correo de sesión:", sesion.correo);
                        data.correo = sesion.correo;
                    } else {
                        console.log("⚠️ No hay sesión activa. Usando correo ingresado:", data.correo);
                    }

                    return fetch("http://127.0.0.1/finoso/informacion/php/crear_pago_nequi.php", {
                        method: "POST",
                        headers: { "Content-Type": "application/json" },
                        body: JSON.stringify(data)
                    });
                })
                .then(res => {
                    if (!res.ok) throw new Error("Error en la respuesta de crear_pago_nequi.php");
                    return res.json();
                })
                .then(respuestaBD => {
                    console.log("📥 Respuesta del backend (crear_pago_nequi):", respuestaBD);

                    const total = data.precio + respuestaBD.costo_envio;

                    // Guardar datos en localStorage
                    const datosAGuardar = {
                        id_reloj: data.id_reloj,
                        precio_original: data.precio,
                        precio_final: data.precio,
                        costo_envio: respuestaBD.costo_envio,
                        total: total,
                        nombre: respuestaBD.nombre_reloj,
                        marca: respuestaBD.marca,
                        img: respuestaBD.img,
                        datos_cliente: {
                            ...respuestaBD.datos_cliente,
                            correo: data.correo
                        },
                        descuento_valor: 0,
                        descuento_porcentaje: 0
                    };

                    console.log("💾 Guardando datos limpios en localStorage:", datosAGuardar);
                    localStorage.setItem("nequi_datos_pago", JSON.stringify(datosAGuardar));

                    console.log("➡️ Redirigiendo a /finoso/pago_nequi.html");
                    window.location.href = "/finoso/informacion/pago_nequi.html";
                })
                .catch(err => {
                    console.error("🛑 Error en el flujo de pago Nequi:", err);
                    alert("Ocurrió un error durante el proceso de pago con Nequi. Intenta nuevamente.");
                });

        } else {
            alert("Método de pago no implementado todavía.");
        }
    }

    // Función para cerrar modal
    function cerrarModal() {
        const modal = document.getElementById("modal-confirmacion");
        if (modal) modal.style.display = "none";
    }

    // Event listeners adicionales
    document.getElementById("cancelar-confirmacion").onclick = () => {
        cerrarModal();
    };

    // Cargar datos al iniciar
    loadFormData();
    aplicarMetodoPagoVisualmente();
});


