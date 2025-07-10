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
                img.classList.remove('seleccionado');
                if (img.dataset.metodo === data.metodo_pago) {
                    img.classList.add('seleccionado');
                }
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
            const correoValido = validateField("correo", /^[^\s@]+@[^\s@]+\.[^\s@]+$/, "Por favor, ingresa un correo electrónico válido.");
            validations.push(correoValido);
        }

        const campoCorreo = document.getElementById("campo-correo");
        if (metodoPagoInput.value === "nequi" && campoCorreo.style.display !== "none") {
            const correoValido = validateField("correo", /^[^\s@]+@[^\s@]+\.[^\s@]+$/, "Por favor, ingresa un correo electrónico válido.");
            validations.push(correoValido);
        }

        if (!validations.every(v => v)) return;

        if (!metodoPagoInput.value) {
            errorMetodo.textContent = 'Por favor, selecciona un método de pago.';
            return;
        } else {
            errorMetodo.textContent = '';
        }

        const formData = new FormData(form);
        const data = Object.fromEntries(formData.entries());

        const params = new URLSearchParams(window.location.search);
        console.log(new URLSearchParams(window.location.search))
        data.id_reloj = params.get("id_reloj");
        console.log("Datos del formulario:", data);

        if (!data.id_reloj) {
            alert("Error: no se encontró el reloj.");
            return;
        }

        // Obtener el valor del envío desde el DOM
        const precioEnvioElement = document.getElementById('precio-envio');
        const costoEnvio = precioEnvioElement ? parseInt(precioEnvioElement.textContent) : 0;

        // Validar que el costo de envío sea válido
        if (costoEnvio <= 0) {
            alert('Error: No se pudo obtener el costo de envío');
            return;
        }

        // AGREGAR el costo de envío a la data existente (NO sobrescribir)
        data.costo_envio = costoEnvio;

        if (metodoPagoInput.value === "mercadopago") {
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
                
                // Opcional: mostrar el desglose antes de redirigir
                console.log('Desglose del pago:');
                console.log(`Reloj: ${respuesta.precio_reloj.toLocaleString('es-CO', {style: 'currency', currency: 'COP'})}`);
                console.log(`Envío: ${respuesta.costo_envio.toLocaleString('es-CO', {style: 'currency', currency: 'COP'})}`);
                console.log(`Total: ${respuesta.total.toLocaleString('es-CO', {style: 'currency', currency: 'COP'})}`);

                // Nuevo paso antes de redirigir
                fetch("https://finoso.store/finoso-zip/finoso/catalogo/php/guardar_orden.php", {
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
                        // Ya se guardó la orden → redirige a MercadoPago
                        window.location.href = respuesta.init_point;
                    } else {
                        alert("No se pudo guardar la orden en la base de datos.");
                    }
                })
                .catch(err => {
                    alert("Error al guardar la orden antes del pago.");
                });
                
                if (respuesta.init_point) {
                    window.location.href = respuesta.init_point;
                }
            })
            .catch(err => {
                console.error("MP API error:", err);
                alert(`Error: ${err.message}\nDetalles:\n${JSON.stringify(err.response || err)}`);
            });
        }  else if (metodoPagoInput.value === "nequi") {
            console.log("🟣 Iniciando flujo de pago con Nequi...");

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

                    // Verificar si el reloj tiene descuento y tomar el precio correcto
                    const precioNormal = parseFloat(document.querySelector(".precio-normal").textContent.trim().replace(/[^\d]/g, ''));
                    let precioConDescuento = precioNormal; // Si no hay descuento, usar el precio normal

                    // Verificar si existe el descuento
                    const precioDescuentoElement = document.querySelector(".precio-descuentos");
                    if (precioDescuentoElement) {
                        precioConDescuento = parseFloat(precioDescuentoElement.textContent.trim().replace(/[^\d]/g, ''));
                        console.log("🎉 Precio con descuento encontrado: " + precioConDescuento);
                    } else {
                        console.log("⚠️ No hay precio con descuento, usando el precio normal.");
                    }

                    // Asignar el precio al objeto de datos
                    data.precio = precioConDescuento;

                    // Verificamos que todos los datos estén completos
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

                    const descuentoAplicado = JSON.parse(localStorage.getItem("descuento_aplicado") || "{}");

                    // Guardamos en localStorage
                    const datosAGuardar = {
                        id_reloj: data.id_reloj,
                        precio: data.precio,
                        costo_envio: respuestaBD.costo_envio,
                        total: respuestaBD.total,
                        nombre: respuestaBD.nombre_reloj,
                        marca: respuestaBD.marca,
                        img: respuestaBD.img,
                        datos_cliente: {
                            ...respuestaBD.datos_cliente,
                            correo: data.correo
                        },
                        descuento_valor: parseFloat(descuentoAplicado.valor || 0),
                        descuento_porcentaje: parseFloat(descuentoAplicado.porcentaje || 0)
                    };

                    console.log("💾 Guardando en localStorage (nequi_datos_pago):", datosAGuardar);
                    localStorage.setItem("nequi_datos_pago", JSON.stringify(datosAGuardar));

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
    // Cargar datos al iniciar
    loadFormData();
});