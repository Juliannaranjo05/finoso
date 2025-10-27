document.addEventListener("DOMContentLoaded", function () {
    const metodoPagoInput = document.getElementById('metodo-pago');
    const errorMetodo = document.getElementById('error-metodo-pago');
    const form = document.getElementById("form-envio");
    const departamentoSelect = document.getElementById("departamento");
    const ciudadSelect = document.getElementById("ciudad");
    const checkbox = document.getElementById('guardar-info');

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
        // Limpiar precio de envío (usar el ID correcto: precio-envio)
        const precioEnvio = document.getElementById("precio-envio");
        if (precioEnvio) precioEnvio.textContent = '0';
        // Guardar formulario
        saveFormData();
    });

    // Inicializar departamentos al cargar la página
    cargarDepartamentos();

    async function calcularEnvio(ciudad, departamento) {
        try {
            const res = await fetch('http://127.0.0.1/finoso/informacion/php/consultar_precio_envio.php?ciudad=' + ciudad + '&departamento=' + departamento);
            const data = await res.json();

            if (data.status === "ok") {
                // El ID correcto es "precio-envio" (con guión)
                const precioEnvio = document.getElementById("precio-envio");
                
                if (precioEnvio) {
                    // Mostrar precio formateado (viene como 8000, mostrar como 8.000)
                    const precio = parseFloat(data.precio);
                    precioEnvio.textContent = precio.toLocaleString('es-CO', {
                        minimumFractionDigits: 0,
                        maximumFractionDigits: 0
                    });
                    console.log('✅ Precio de envío actualizado:', precio, 'COP');
                } else {
                    console.error('❌ No se encontró el elemento #precio-envio');
                }
            } else {
                console.warn('⚠️ No hay cobertura para esta ciudad');
                alert("No tenemos cobertura aún para esta ciudad.");
            }
        } catch (error) {
            console.error('Error al calcular envío:', error);
        }
    }

    // Listener para cambio de ciudad
    if (ciudadSelect) {
        ciudadSelect.addEventListener("change", () => {
            const ciudad = ciudadSelect.value;
            const departamento = departamentoSelect.value;
            if (ciudad && departamento) {
                calcularEnvio(ciudad, departamento);
            }
        });
    }

    // === SISTEMA DE GUARDADO SIMPLE ===
    const STORAGE_KEY = 'finoso_form_data';

    function saveFormData() {
        if (!checkbox.checked) return;

        const data = {
            nombre: document.getElementById('nombre').value,
            cedula: document.getElementById('cedula').value,
            celular: document.getElementById('celular').value,
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
        
        const data = JSON.parse(saved);
        
        // Llenar campos de texto
        Object.keys(data).forEach(key => {
            if (key !== 'departamento' && key !== 'ciudad' && key !== 'metodo_pago') {
                const el = document.getElementById(key);
                if (el && data[key]) el.value = data[key];
            }
        });
        
        // Departamento y ciudad
        if (data.departamento) {
            departamentoSelect.value = data.departamento;
            departamentoSelect.dispatchEvent(new Event('change'));
            
            setTimeout(() => {
                if (data.ciudad) ciudadSelect.value = data.ciudad;
            }, 50);
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

    // === EVENTOS ===
    
    // Checkbox de guardar
    checkbox.addEventListener('change', (e) => {
        if (e.target.checked) {
            saveFormData();
        } else {
            localStorage.removeItem(STORAGE_KEY);
        }
    });

    // Guardar automáticamente cuando cambie un campo
    const fieldsToWatch = ['nombre', 'cedula', 'celular', 'direccion', 'barrio', 'referencias'];
    fieldsToWatch.forEach(fieldId => {
        const field = document.getElementById(fieldId);
        if (field) {
            field.addEventListener('blur', saveFormData);
        }
    });

    // Los listeners ya están definidos arriba con la carga dinámica
    ciudadSelect.addEventListener('change', saveFormData);

    // Event listeners para métodos de pago ya están definidos en loadFormData()


    const btnCancelar = document.getElementById("cancelar-confirmacion");
        if (btnCancelar) {
        btnCancelar.onclick = () => {
            document.getElementById("modal-confirmacion").style.display = "none";
        };
    }


    // Submit del formulario
    form.addEventListener("submit", function (e) {
        e.preventDefault();

        // Validaciones
        const validations = [
            validateField("nombre", /^[A-Za-zÁÉÍÓÚáéíóúñÑ\s]+$/, "Por favor, ingresa un nombre válido."),
            validateField("cedula", /^\d{6,10}$/, "Por favor, ingresa una cédula válida."),
            validateField("celular", /^\d{10}$/, "Por favor, ingresa un número de celular válido."),
            validateField("ciudad", /^[A-Za-zÁÉÍÓÚáéíóúñÑ\s]+$/, "Por favor, ingresa una ciudad válida."),
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

        const campoCorreo = document.getElementById("campo-correo");
        if (metodoPagoInput.value === "nequi" && campoCorreo.style.display !== "none") {
            const correoValido = validateField("correo", /^[^\s@]+@[^\s@]+\.[^\s@]+$/, "Por favor, ingresa un correo electrónico válido.");
            validations.push(correoValido);
        }

        const formData = new FormData(form);
        const data = Object.fromEntries(formData.entries());

        console.log(new URLSearchParams(window.location.search))
        data.id_reloj = new URLSearchParams(window.location.search).get("id_reloj");
        console.log("Datos del formulario:", data);

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

        // AGREGAR el costo de envío a la data existente (NO sobrescribir)
        data.costo_envio = costoEnvio;
        if (!data.precio) {
            const precioElement = document.querySelector(".precio-normal, .precio-descuentos");
            if (precioElement) {
                const precioLimpiado = precioElement.textContent.trim().replace(/[^\d]/g, '');
                data.precio = parseInt(precioLimpiado);
            } else {
                data.precio = 0; // fallback
            }
        }

        const total = parseInt(data.precio) + costoEnvio;

        // === ACTUALIZAR CAMPOS DEL MODAL VISUAL ===
        const modalNombre = document.getElementById("modal-nombre");
        const modalCiudad = document.getElementById("modal-ciudad");
        const modalDireccion = document.getElementById("modal-direccion");
        const modalMetodo = document.getElementById("modal-metodo-pago");
        const modalCostoTotal = document.getElementById("modal-costo-total");

        if (modalNombre) modalNombre.textContent = data.nombre;
        if (modalCiudad) modalCiudad.textContent = data.ciudad;
        if (modalDireccion) modalDireccion.textContent = `${data.direccion}, ${data.barrio}`;
        if (modalMetodo) {
            const metodo = metodoPagoInput.value === "nequi" ? "Nequi" : 
                          metodoPagoInput.value === "wompi" ? "Wompi" : "Método no válido";
            modalMetodo.textContent = metodo;
        }
        if (modalCostoTotal) modalCostoTotal.textContent = `$${total.toLocaleString('es-CO')}`;

        console.log("🎯 modal-nombre:", modalNombre);
        console.log("🎯 modal-ciudad:", modalCiudad);

        const resumen = `
            <strong>Cédula:</strong> ${data.cedula}<br>
            <strong>Celular:</strong> ${data.celular}<br>
            <strong>Método de pago:</strong> ${metodoPagoInput.value === "nequi" ? "Nequi" : 
                                             metodoPagoInput.value === "wompi" ? "Wompi" : "No válido"}<br>
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

    } else if (metodoPagoInput.value === "nequi") {
            console.log("🟣 Iniciando flujo de pago con Nequi...");

            // 🔥 DECLARAR VARIABLES FUERA DEL SCOPE
            let precioOriginal = 0;
            let precioFinal = 0;
            let descuentoAplicado = 0;
            let porcentajeDescuento = 0;

            // Verificamos sesión para saber si ya tenemos el correo del usuario
            fetch("http://127.0.0.1/finoso/informacion/php/verificar_sesion.php")
                .then(res => {
                    if (!res.ok) throw new Error("Fallo en la verificación de sesión");
                    return res.json();
                })
                .then(sesion => {
                    console.log("🔍 Resultado verificación de sesión:", sesion);

                    if (sesion.logged_in && sesion.correo) {
                        console.log("✅ Sesión activa. Usando correo de sesión:", sesion.correo);
                        data.correo = sesion.correo;
                    } else {
                        const inputCorreo = document.getElementById("correo").value.trim();
                        console.log("⚠️ No hay sesión activa. Usando correo ingresado:", inputCorreo);
                        data.correo = inputCorreo;
                    }

                    // 🔥 OBTENER PRECIOS DEL DOM
                    const precioNormalElement = document.querySelector(".precio-normal");
                    const precioDescuentoElement = document.querySelector(".precio-descuentos");

                    // Verificar si hay código de descuento aplicado desde localStorage
                    const descuentoGuardado = localStorage.getItem("descuento_aplicado");
                    const codigoDescuentoData = descuentoGuardado ? JSON.parse(descuentoGuardado) : null;

                    // Leer precio original (siempre está en .precio-normal cuando hay descuento automático)
                    if (precioNormalElement) {
                        const precioTexto = precioNormalElement.textContent.trim().replace(/[^\d]/g, '');
                        precioOriginal = parseFloat(precioTexto); // $125.000 → 125000 (YA está correcto)
                    }

                    // Leer precio final actual del DOM (ya con todos los descuentos aplicados)
                    if (precioDescuentoElement) {
                        // Hay descuento automático, leer de precio-descuentos
                        const precioTexto = precioDescuentoElement.textContent.trim().replace(/[^\d]/g, '');
                        precioFinal = parseFloat(precioTexto); // $97.000 → 97000 (YA está correcto)
                        
                        // Si no teníamos el precio original, usar el precio con descuento como base
                        if (!precioOriginal) {
                            precioOriginal = precioFinal;
                        }
                    } else if (precioNormalElement) {
                        // No hay descuento automático, precio normal es el precio final
                        const precioTexto = precioNormalElement.textContent.trim().replace(/[^\d]/g, '');
                        precioFinal = parseFloat(precioTexto); // $125.000 → 125000 (YA está correcto)
                        precioOriginal = precioFinal;
                    }

                    // Si hay código de descuento aplicado, usar los datos guardados
                    if (codigoDescuentoData) {
                        descuentoAplicado = parseFloat(codigoDescuentoData.valor);
                        porcentajeDescuento = parseFloat(codigoDescuentoData.porcentaje);
                        
                        console.log(`🎉 Código de descuento aplicado: $${descuentoAplicado} (${porcentajeDescuento}%)`);
                    } else {
                        console.log("ℹ️ No hay código de descuento aplicado");
                    }

                    // Asignar valores a data
                    data.precio_original = precioOriginal;
                    data.descuento_valor = descuentoAplicado;
                    data.descuento_porcentaje = porcentajeDescuento;

                    console.log("📦 Datos calculados:", {
                        precioOriginal: data.precio_original,
                        precioFinal: precioFinal,
                        descuentoValor: data.descuento_valor,
                        descuentoPorcentaje: data.descuento_porcentaje
                    });

                    console.log("📦 Datos a enviar a crear_pago_nequi.php:", data);

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

                    // 🔥 USAR EL PRECIO FINAL QUE YA CALCULAMOS (no recalcular)
                    // El precioFinal ya tiene todos los descuentos aplicados correctamente
                    const total = precioFinal + respuestaBD.costo_envio;

                    // Guardamos datos limpios en localStorage
                    const datosAGuardar = {
                        id_reloj: data.id_reloj,
                        precio_original: data.precio_original, // Precio original real
                        precio_final: precioFinal, // Precio final ya calculado correctamente
                        costo_envio: respuestaBD.costo_envio,
                        total: total, // Total correcto
                        nombre: respuestaBD.nombre_reloj,
                        marca: respuestaBD.marca,
                        img: respuestaBD.img,
                        datos_cliente: {
                            ...respuestaBD.datos_cliente,
                            correo: data.correo
                        },
                        descuento_valor: data.descuento_valor || 0,
                        descuento_porcentaje: data.descuento_porcentaje || 0
                    };

                    console.log("💾 Guardando datos limpios en localStorage:", datosAGuardar);
                    localStorage.setItem("nequi_datos_pago", JSON.stringify(datosAGuardar));
                    localStorage.removeItem("descuento_aplicado");

                    console.log("➡️ Redirigiendo a /finoso/pago_nequi.html");
                    window.location.href = "/finoso/informacion/pago_nequi.html";
                })
                .catch(err => {
                    console.error("🛑 Error en el flujo de pago Nequi:", err);
                    alert("Ocurrió un error durante el proceso de pago con Nequi. Intenta nuevamente.");
                });

            return;
        }
    });
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

        } else {
            alert("Método de pago no implementado todavía.");
        }
    }

    // Cargar datos al iniciar
    loadFormData();
    aplicarMetodoPagoVisualmente();
});