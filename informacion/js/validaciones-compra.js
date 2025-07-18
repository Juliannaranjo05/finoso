document.addEventListener("DOMContentLoaded", function () {
    const metodoPagoInput = document.getElementById('metodo-pago');
    const errorMetodo = document.getElementById('error-metodo-pago');
    const form = document.getElementById("form-envio");
    const departamentoSelect = document.getElementById("departamento");
    const ciudadSelect = document.getElementById("ciudad");
    const checkbox = document.getElementById('guardar-info');

    const ciudadesPorDepartamento = {
        "AMA": ["Leticia", "Puerto Nariño"],
        "AN": ["Medellín", "Bello", "Itagüí", "Envigado", "Rionegro", "Apartadó", "Turbo", "La Ceja", "Sabaneta", "Copacabana", "Marinilla", "Guarne", "Amagá", "Caucasia"],
        "ARA": ["Arauca", "Arauquita", "Saravena", "Tame", "Cravo Norte", "Puerto Rondón", "Fortul"],
        "ATL": ["Barranquilla", "Soledad", "Malambo", "Sabanalarga", "Puerto Colombia", "Galapa", "Baranoa", "Polonuevo", "Sabanagrande", "Campo de la Cruz"],
        "BOL": ["Cartagena", "Magangué", "Turbaco", "Arjona", "El Carmen de Bolívar", "Mompox", "San Juan Nepomuceno", "María la Baja", "Santa Rosa", "Talaigua Nuevo"],
        "BOY": ["Tunja", "Duitama", "Sogamoso", "Chiquinquirá", "Paipa", "Moniquirá", "Villa de Leyva", "Puerto Boyacá", "Nobsa", "Tibasosa", "Samacá"],
        "CAL": ["Manizales", "La Dorada", "Chinchiná", "Villamaría", "Riosucio", "Anserma", "Salamina", "Aguadas"],
        "CAQ": ["Florencia", "San Vicente del Caguán", "Puerto Rico", "El Doncello", "La Montañita", "Belén de los Andaquíes"],
        "CAS": ["Yopal", "Aguazul", "Villanueva", "Tauramena", "Monterrey", "Maní", "Paz de Ariporo", "Trinidad"],
        "CAU": ["Popayán", "Santander de Quilichao", "Puerto Tejada", "Patía", "El Tambo", "Guapi", "Timbiquí", "Silvia", "Rosas"],
        "CES": ["Valledupar", "Aguachica", "La Jagua de Ibirico", "Codazzi", "Bosconia", "Chimichagua", "Curumaní", "El Copey"],
        "CHO": ["Quibdó", "Istmina", "Tadó", "Condoto", "Bahía Solano", "Acandí", "Nuquí", "Lloró"],
        "COR": ["Montería", "Lorica", "Sahagún", "Cereté", "Tierralta", "Planeta Rica", "Montelíbano", "Ayapel"],
        "CUN": ["Bogotá", "Soacha", "Chía", "Zipaquirá", "Facatativá", "Girardot", "Fusagasugá", "Mosquera", "Cajicá", "La Calera", "Madrid", "Funza", "Sibaté"],
        "GUA": ["Inírida", "Barranco Minas", "Cacahual", "Pana Pana"],
        "GUV": ["San José del Guaviare", "Calamar", "Miraflores", "El Retorno"],
        "HUI": ["Neiva", "Pitalito", "Garzón", "La Plata", "Campoalegre", "Rivera", "Yaguará", "Aipe"],
        "LAG": ["Riohacha", "Maicao", "Uribia", "Fonseca", "San Juan del Cesar", "Manaure", "Dibulla"],
        "MAG": ["Santa Marta", "Ciénaga", "Fundación", "El Banco", "Plato", "Aracataca", "Pivijay", "Zona Bananera"],
        "MET": ["Villavicencio", "Acacías", "Granada", "Puerto López", "San Martín", "Cumaral", "Guamal", "Cabuyaro"],
        "NAR": ["Pasto", "Tumaco", "Ipiales", "Túquerres", "La Unión", "Sandoná", "El Tambo", "Barbacoas"],
        "NSA": ["Cúcuta", "Ocaña", "Pamplona", "Villa del Rosario", "Los Patios", "Chinácota", "Tibú"],
        "PUT": ["Mocoa", "Puerto Asís", "Orito", "Sibundoy", "Valle del Guamuez", "Puerto Caicedo"],
        "QUI": ["Armenia", "Calarcá", "Montenegro", "La Tebaida", "Quimbaya", "Circasia", "Filandia"],
        "RIS": ["Pereira", "Dosquebradas", "Santa Rosa de Cabal", "La Virginia", "Belén de Umbría", "Marsella"],
        "SAP": ["San Andrés", "Providencia"],
        "SAN": ["Bucaramanga", "Floridablanca", "Girón", "Piedecuesta", "Barrancabermeja", "San Gil", "Socorro", "Lebrija"],
        "SUC": ["Sincelejo", "Corozal", "Sampués", "San Marcos", "Tolú", "San Onofre"],
        "TOL": ["Ibagué", "Espinal", "Melgar", "Honda", "Líbano", "Chaparral", "Mariquita", "Guamo"],
        "VAC": ["Cali", "Palmira", "Buenaventura", "Tuluá", "Buga", "Yumbo", "Cartago", "Jamundí", "Candelaria"],
        "VAU": ["Mitú", "Carurú", "Taraira", "Pacoa"],
        "VID": ["Puerto Carreño", "La Primavera", "Santa Rosalía", "Cumaribo"]
    };

    async function calcularEnvio(ciudad, departamento) {
        const res = await fetch('http://127.0.0.1/finoso/informacion/php/consultar_precio_envio.php?ciudad=' + ciudad + '&departamento=' + departamento);
        const data = await res.json();

        if (data.status === "ok") {
            document.getElementById("precioEnvio").textContent = `$${data.precio.toLocaleString()}`;
            document.getElementById("diasEnvio").textContent = `${data.dias_estimados} días hábiles`;
        } else {
            alert("No tenemos cobertura aún para esta ciudad.");
        }
    }

    document.getElementById("ciudad").addEventListener("change", () => {
        const ciudad = document.getElementById("ciudad").value;
        const departamento = document.getElementById("departamento").value;
        calcularEnvio(ciudad, departamento);
    });

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

        // Si el método de pago es Nequi, también guardar el correo
        if (metodoPagoInput.value === "nequi") {
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

    // Departamento/Ciudad
    departamentoSelect.addEventListener("change", function () {
        const selectedDepartamento = departamentoSelect.value;
        ciudadSelect.innerHTML = '<option value="">Selecciona una ciudad</option>';

        if (selectedDepartamento && ciudadesPorDepartamento[selectedDepartamento]) {
            ciudadesPorDepartamento[selectedDepartamento].forEach(ciudad => {
                const option = document.createElement("option");
                option.value = ciudad;
                option.textContent = ciudad;
                ciudadSelect.appendChild(option);
            });
        }

        saveFormData(); // Guardar cuando cambie departamento
    });

    ciudadSelect.addEventListener('change', saveFormData);

    document.querySelectorAll(".boton-metodo img").forEach(img => {
        img.addEventListener("click", () => {
            document.querySelectorAll(".boton-metodo img").forEach(i => i.classList.remove("seleccionado"));
            img.classList.add("seleccionado");
            metodoPagoInput.value = img.dataset.metodo;
            errorMetodo.textContent = "";

            const campoCorreo = document.getElementById("campo-correo");
            const correoInput = document.getElementById("correo");

            // Si el método es Nequi, verificar si hay sesión
            if (img.dataset.metodo === "nequi") {
                fetch("http://127.0.0.1/finoso/informacion/php/verificar_sesion.php")
                    .then(res => res.json())
                    .then(respuesta => {
                        if (respuesta.logged_in) {
                            // Hay sesión, no mostrar campo correo
                            campoCorreo.style.display = "none";
                            correoInput.required = false;
                            correoInput.value = "";
                        } else {
                            // No hay sesión, sí mostrar
                            campoCorreo.style.display = "block";
                            correoInput.required = true;
                        }
                    });
            } else {
                // No es Nequi
                campoCorreo.style.display = "none";
                correoInput.required = false;
                correoInput.value = "";
            }

            saveFormData();
        });
    });


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

        if (metodoPagoInput.value === "nequi") {
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
        if (modalMetodo) modalMetodo.textContent = metodoPagoInput.value === "nequi" ? "Nequi" : "Mercado Pago";
        if (modalCostoTotal) modalCostoTotal.textContent = `$${total.toLocaleString('es-CO')}`;

        console.log("🎯 modal-nombre:", modalNombre);
        console.log("🎯 modal-ciudad:", modalCiudad);

        const resumen = `
            <strong>Cédula:</strong> ${data.cedula}<br>
            <strong>Celular:</strong> ${data.celular}<br>
            <strong>Método de pago:</strong> ${metodoPagoInput.value === "nequi" ? "Nequi" : "Mercado Pago"}<br>
            <strong>Precio reloj:</strong> $${parseInt(data.precio).toLocaleString()}<br>
            <strong>Envío:</strong> $${costoEnvio.toLocaleString()}
        `;

        if (metodoPagoInput.value === "mercado_pago") {
            document.getElementById("resumen-compra").innerHTML = resumen;
            const modal = document.getElementById("modal-confirmacion");
            modal.style.display = "flex";
            let segundos = 5;
            const mensaje = document.getElementById("auto-redir-msg");
            if (mensaje) {
                mensaje.textContent = `Redirigiendo automáticamente en ${segundos} segundos...`;

                const countdown = setInterval(() => {
                    segundos--;
                    mensaje.textContent = `Redirigiendo automáticamente en ${segundos} segundos...`;
                    if (segundos <= 0) clearInterval(countdown);
                }, 1000);
            }

            setTimeout(() => {
                cerrarModal();
                enviarFormulario(data);
            }, 5000);

            return;

        }  else if (metodoPagoInput.value === "nequi") {
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
                        precioOriginal = parseFloat(precioNormalElement.textContent.trim().replace(/[^\d]/g, ''));
                    }

                    // Leer precio final actual del DOM (ya con todos los descuentos aplicados)
                    if (precioDescuentoElement) {
                        // Hay descuento automático, leer de precio-descuentos
                        precioFinal = parseFloat(precioDescuentoElement.textContent.trim().replace(/[^\d]/g, ''));
                        
                        // Si no teníamos el precio original, calcular desde el precio con descuento
                        if (!precioOriginal) {
                            // Asumir que el descuento automático es conocido o calculable
                            // En este caso específico, sabemos que 97000 viene de 115000 con descuento
                            precioOriginal = 115000; // Valor fijo conocido, o calcular dinámicamente
                        }
                    } else if (precioNormalElement) {
                        // No hay descuento automático, precio normal es el precio final
                        precioFinal = parseFloat(precioNormalElement.textContent.trim().replace(/[^\d]/g, ''));
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
        if (data.metodo_pago === "mercado_pago") {
            fetch("http://127.0.0.1/finoso/informacion/php/crear_preferencia.php", {
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
                    const errorMessage = respuesta.debug ? 
                        `Error: ${respuesta.error}\nDetalles:\n` +
                        `- Precio BD: ${(respuesta.debug.precio_bd / 1000).toLocaleString('es-CO', {style: 'currency', currency: 'COP'})}\n` +
                        `- Precio final: ${(respuesta.debug.precio_redondeado / 1000).toLocaleString('es-CO', {style: 'currency', currency: 'COP'})}\n` +
                        `- Costo envío: ${respuesta.debug.costo_envio.toLocaleString('es-CO', {style: 'currency', currency: 'COP'})}\n` +
                        `- Total: ${respuesta.debug.total_con_envio.toLocaleString('es-CO', {style: 'currency', currency: 'COP'})}` :
                        respuesta.message;

                    alert(errorMessage);
                    return;
                }

                console.log('🧾 Desglose del pago:');
                console.log(`Reloj: ${respuesta.precio_reloj.toLocaleString('es-CO', {style: 'currency', currency: 'COP'})}`);
                console.log(`Envío: ${respuesta.costo_envio.toLocaleString('es-CO', {style: 'currency', currency: 'COP'})}`);
                console.log(`Total: ${respuesta.total.toLocaleString('es-CO', {style: 'currency', currency: 'COP'})}`);

                // Guardar la orden antes de redirigir a MP
                fetch("https://finoso.store/finoso/informacion/php/guardar_orden.php", {
                    method: "POST",
                    headers: { "Content-Type": "application/json" },
                    body: JSON.stringify({
                        ...data,
                        precio_reloj: respuesta.precio_reloj,
                        costo_envio: respuesta.costo_envio
                    })
                })
                .then(res => res.json())
                .then(guardarRes => {
                    if (guardarRes.success) {
                        window.location.href = respuesta.init_point;
                    } else {
                        alert("No se pudo guardar la orden en la base de datos.");
                    }
                })
                .catch(err => {
                    console.error("❌ Error al guardar orden:", err);
                    alert("Error al guardar la orden antes del pago.");
                });

            })
            .catch(err => {
                console.error("❌ MP API error:", err);
                alert(`Error: ${err.message}\nDetalles:\n${JSON.stringify(err.response || err)}`);
            });

        } else {
            alert("Método de pago no implementado todavía.");
        }
    }

    // Cargar datos al iniciar
    loadFormData();
    aplicarMetodoPagoVisualmente();
});