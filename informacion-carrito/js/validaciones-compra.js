console.log("🔍 DEBUG validaciones-compra.js - Archivo cargado correctamente");
console.log("🔍 DEBUG validaciones-compra.js - Timestamp:", new Date().toISOString());
console.log("🔍 DEBUG validaciones-compra.js - URL actual:", window.location.href);
console.log("🔍 DEBUG validaciones-compra.js - User Agent:", navigator.userAgent);

// Capturar errores de JavaScript
window.addEventListener('error', function(e) {
    console.error("🔍 DEBUG validaciones-compra.js - Error de JavaScript:", e.error);
    console.error("🔍 DEBUG validaciones-compra.js - Mensaje:", e.message);
    console.error("🔍 DEBUG validaciones-compra.js - Archivo:", e.filename);
    console.error("🔍 DEBUG validaciones-compra.js - Línea:", e.lineno);
});

// Capturar promesas rechazadas
window.addEventListener('unhandledrejection', function(e) {
    console.error("🔍 DEBUG validaciones-compra.js - Promesa rechazada:", e.reason);
});

document.addEventListener("DOMContentLoaded", function () {
    console.log("🔍 DEBUG validaciones-compra.js - DOM cargado, iniciando configuración");
    console.log("🔍 DEBUG validaciones-compra.js - Timestamp DOM:", new Date().toISOString());
    console.log("🔍 DEBUG validaciones-compra.js - Página actual:", document.title);
    console.log("🔍 DEBUG validaciones-compra.js - Elementos del DOM cargados:", document.querySelectorAll('*').length);
    
    // Verificar sesión antes de continuar
    verificarSesion();
    
    const metodoPagoInput = document.getElementById('metodo-pago');
    const errorMetodo = document.getElementById('error-metodo-pago');
    const form = document.getElementById("form-envio");
    const departamentoSelect = document.getElementById("departamento");
    const ciudadSelect = document.getElementById("ciudad");
    const checkbox = document.getElementById('guardar-info');

    console.log("🔍 DEBUG validaciones-compra.js - Elementos encontrados:");
    console.log("🔍 DEBUG validaciones-compra.js - metodoPagoInput:", metodoPagoInput);
    console.log("🔍 DEBUG validaciones-compra.js - form:", form);
    console.log("🔍 DEBUG validaciones-compra.js - departamentoSelect:", departamentoSelect);
    console.log("🔍 DEBUG validaciones-compra.js - ciudadSelect:", ciudadSelect);

    // Cargar departamentos desde la base de datos al iniciar
    async function cargarDepartamentos() {
        console.log("🔍 DEBUG validaciones-compra.js - Iniciando carga de departamentos");
        try {
            const url = 'http://127.0.0.1/finoso/informacion/php/obtener_ciudades.php?action=departamentos';
            console.log("🔍 DEBUG validaciones-compra.js - URL:", url);
            
            const response = await fetch(url);
            console.log("🔍 DEBUG validaciones-compra.js - Response status:", response.status);
            console.log("🔍 DEBUG validaciones-compra.js - Response ok:", response.ok);
            
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            const data = await response.json();
            console.log("🔍 DEBUG validaciones-compra.js - Data recibida:", data);
            
            if (data.success && data.departamentos) {
                console.log("🔍 DEBUG validaciones-compra.js - Departamentos encontrados:", data.departamentos.length);
                departamentoSelect.innerHTML = '<option value="">Seleccionar departamento</option>';
                data.departamentos.forEach(depto => {
                    console.log("🔍 DEBUG validaciones-compra.js - Agregando departamento:", depto);
                    const option = document.createElement('option');
                    option.value = depto;
                    option.textContent = depto;
                    departamentoSelect.appendChild(option);
                });
                console.log("🔍 DEBUG validaciones-compra.js - Departamentos cargados exitosamente");
            } else {
                console.error("🔍 DEBUG validaciones-compra.js - No se encontraron departamentos:", data);
            }
        } catch (error) {
            console.error('🔍 DEBUG validaciones-compra.js - Error al cargar departamentos:', error);
            console.error('🔍 DEBUG validaciones-compra.js - Error message:', error.message);
            console.error('🔍 DEBUG validaciones-compra.js - Error stack:', error.stack);
        }
    }

    // Cargar ciudades cuando se selecciona un departamento
    async function cargarCiudades(departamento) {
        console.log("🔍 DEBUG validaciones-compra.js - Iniciando carga de ciudades para:", departamento);
        if (!departamento) {
            console.log("🔍 DEBUG validaciones-compra.js - No hay departamento seleccionado");
            ciudadSelect.innerHTML = '<option value="">Seleccionar ciudad</option>';
            ciudadSelect.disabled = true;
            return;
        }

        try {
            const url = `http://127.0.0.1/finoso/informacion/php/obtener_ciudades.php?action=ciudades&departamento=${encodeURIComponent(departamento)}`;
            console.log("🔍 DEBUG validaciones-compra.js - URL ciudades:", url);
            
            const response = await fetch(url);
            console.log("🔍 DEBUG validaciones-compra.js - Response ciudades status:", response.status);
            console.log("🔍 DEBUG validaciones-compra.js - Response ciudades ok:", response.ok);
            
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            const data = await response.json();
            console.log("🔍 DEBUG validaciones-compra.js - Data ciudades recibida:", data);
            
            if (data.success && data.ciudades) {
                console.log("🔍 DEBUG validaciones-compra.js - Ciudades encontradas:", data.ciudades.length);
                ciudadSelect.innerHTML = '<option value="">Seleccionar ciudad</option>';
                data.ciudades.forEach(item => {
                    console.log("🔍 DEBUG validaciones-compra.js - Agregando ciudad:", item.ciudad, "Precio:", item.precio);
                    const option = document.createElement('option');
                    option.value = item.ciudad;
                    option.textContent = item.ciudad;
                    option.dataset.precio = item.precio;
                    option.dataset.dias = item.dias_estimados;
                    ciudadSelect.appendChild(option);
                });
                ciudadSelect.disabled = false;
                console.log("🔍 DEBUG validaciones-compra.js - Ciudades cargadas exitosamente");
            } else {
                console.error("🔍 DEBUG validaciones-compra.js - No se encontraron ciudades:", data);
                ciudadSelect.innerHTML = '<option value="">No hay ciudades disponibles</option>';
                ciudadSelect.disabled = true;
            }
        } catch (error) {
            console.error('🔍 DEBUG validaciones-compra.js - Error al cargar ciudades:', error);
            console.error('🔍 DEBUG validaciones-compra.js - Error message:', error.message);
            console.error('🔍 DEBUG validaciones-compra.js - Error stack:', error.stack);
            ciudadSelect.innerHTML = '<option value="">Error al cargar ciudades</option>';
            ciudadSelect.disabled = true;
        }
    }

    // Listener para cambio de departamento
    departamentoSelect.addEventListener('change', () => {
        console.log("🔍 DEBUG validaciones-compra.js - Cambio de departamento detectado");
        const departamento = departamentoSelect.value;
        console.log("🔍 DEBUG validaciones-compra.js - Departamento seleccionado:", departamento);
        cargarCiudades(departamento);
        // Limpiar precio de envío
        const precioEnvio = document.getElementById("precio-envio");
        if (precioEnvio) {
            precioEnvio.textContent = '0';
            console.log("🔍 DEBUG validaciones-compra.js - Precio de envío limpiado");
        }
    });

    // Listener para cambio de ciudad
    ciudadSelect.addEventListener('change', () => {
        console.log("🔍 DEBUG validaciones-compra.js - Cambio de ciudad detectado");
        const selectedOption = ciudadSelect.options[ciudadSelect.selectedIndex];
        console.log("🔍 DEBUG validaciones-compra.js - Opción seleccionada:", selectedOption);
        
        if (selectedOption && selectedOption.dataset.precio) {
            const precio = parseFloat(selectedOption.dataset.precio);
            const dias = selectedOption.dataset.dias;
            console.log("🔍 DEBUG validaciones-compra.js - Precio de envío:", precio, "Días:", dias);
            
            // Actualizar precio de envío
            const precioEnvio = document.getElementById("precio-envio");
            if (precioEnvio) {
                // Guardar valor real en data-attribute
                precioEnvio.dataset.precio = precio;
                // Mostrar valor formateado
                precioEnvio.textContent = precio.toLocaleString('es-CO', {
                    minimumFractionDigits: 0,
                    maximumFractionDigits: 0
                });
                console.log("🔍 DEBUG validaciones-compra.js - Precio de envío actualizado a:", precio);
            }
            
            // Actualizar días estimados si existe el elemento
            const diasElement = document.getElementById("dias-envio");
            if (diasElement) {
                diasElement.textContent = `${dias} días`;
                console.log("🔍 DEBUG validaciones-compra.js - Días de envío actualizados a:", `${dias} días`);
            }
        }
    });

    // === MANEJO DE MÉTODOS DE PAGO ===
    console.log("🔍 DEBUG validaciones-compra.js - Configurando listeners de métodos de pago");
    
    // Listener para cambio de método de pago
    const botonesMetodo = document.querySelectorAll(".boton-metodo img");
    console.log("🔍 DEBUG validaciones-compra.js - Botones encontrados:", botonesMetodo.length);
    
    botonesMetodo.forEach((img, index) => {
        console.log("🔍 DEBUG validaciones-compra.js - Configurando botón", index, "con método:", img.dataset.metodo);
        
        img.addEventListener("click", () => {
            console.log("🔍 DEBUG validaciones-compra.js - CLICK en método:", img.dataset.metodo);
            
            // Quitar selección previa de todas las imágenes
            document.querySelectorAll(".boton-metodo img").forEach(img => img.classList.remove("seleccionado"));
            
            // Aplicar clase seleccionado directamente a la imagen
            img.classList.add("seleccionado");
            
            metodoPagoInput.value = img.dataset.metodo;
            errorMetodo.textContent = "";

            const campoCorreo = document.getElementById("campo-correo");
            const correoInput = document.getElementById("correo");
            
            console.log("🔍 DEBUG validaciones-compra.js - Campo correo encontrado:", campoCorreo);
            console.log("🔍 DEBUG validaciones-compra.js - Input correo encontrado:", correoInput);

            // Si el método es Nequi, verificar si hay sesión
            if (img.dataset.metodo === "nequi") {
                console.log("🔍 DEBUG validaciones-compra.js - Verificando sesión para Nequi");
                fetch("http://127.0.0.1/finoso/informacion/php/verificar_sesion.php")
                    .then(res => res.json())
                    .then(respuesta => {
                        console.log("🔍 DEBUG validaciones-compra.js - Respuesta sesión:", respuesta);
                        if (respuesta.logged_in) {
                            // Hay sesión, no mostrar campo correo
                            console.log("🔍 DEBUG validaciones-compra.js - Sesión activa, ocultando campo correo");
                            campoCorreo.style.display = "none";
                            correoInput.required = false;
                            correoInput.value = "";
                        } else {
                            // No hay sesión, sí mostrar
                            console.log("🔍 DEBUG validaciones-compra.js - Sin sesión, mostrando campo correo");
                            campoCorreo.style.display = "block";
                            correoInput.required = true;
                        }
                    })
                    .catch(error => {
                        console.error("🔍 DEBUG validaciones-compra.js - Error verificando sesión:", error);
                        // En caso de error, mostrar el campo por seguridad
                        campoCorreo.style.display = "block";
                        correoInput.required = true;
                    });
            } else {
                // No es Nequi
                console.log("🔍 DEBUG validaciones-compra.js - Método no es Nequi, ocultando campo correo");
                campoCorreo.style.display = "none";
                correoInput.required = false;
                correoInput.value = "";
            }
        });
    });

    // === MANEJO DEL FORMULARIO ===
    const formEnvio = document.getElementById("form-envio");
    console.log("🔍 DEBUG validaciones-compra.js - Formulario encontrado:", formEnvio);
    
    // También agregar listener al botón directamente
    const botonComprar = document.querySelector('button[type="submit"]');
    console.log("🔍 DEBUG validaciones-compra.js - Botón comprar encontrado:", botonComprar);
    console.log("🔍 DEBUG validaciones-compra.js - Todos los botones:", document.querySelectorAll('button'));
    console.log("🔍 DEBUG validaciones-compra.js - Botones submit:", document.querySelectorAll('button[type="submit"]'));
    
    // Función para manejar el click del botón
    function manejarClickComprar(e) {
        console.log("🔍 DEBUG validaciones-compra.js - CLICK en botón comprar");
        console.log("🔍 DEBUG validaciones-compra.js - Timestamp:", new Date().toISOString());
        e.preventDefault();
        
        // Validar campos requeridos
        const nombre = document.getElementById("nombre");
        const cedula = document.getElementById("cedula");
        const celular = document.getElementById("celular");
        const direccion = document.getElementById("direccion");
        const barrio = document.getElementById("barrio");
        const departamento = document.getElementById("departamento");
        const ciudad = document.getElementById("ciudad");
        const metodoPago = document.getElementById("metodo-pago");
        const correo = document.getElementById("correo");
        
        console.log("🔍 DEBUG validaciones-compra.js - Elementos encontrados:");
        console.log("- Nombre:", nombre, "Valor:", nombre ? nombre.value : "NO ENCONTRADO");
        console.log("- Cédula:", cedula, "Valor:", cedula ? cedula.value : "NO ENCONTRADO");
        console.log("- Celular:", celular, "Valor:", celular ? celular.value : "NO ENCONTRADO");
        console.log("- Dirección:", direccion, "Valor:", direccion ? direccion.value : "NO ENCONTRADO");
        console.log("- Barrio:", barrio, "Valor:", barrio ? barrio.value : "NO ENCONTRADO");
        console.log("- Departamento:", departamento, "Valor:", departamento ? departamento.value : "NO ENCONTRADO");
        console.log("- Ciudad:", ciudad, "Valor:", ciudad ? ciudad.value : "NO ENCONTRADO");
        console.log("- Método de pago:", metodoPago, "Valor:", metodoPago ? metodoPago.value : "NO ENCONTRADO");
        console.log("- Correo:", correo, "Valor:", correo ? correo.value : "NO ENCONTRADO");
        
        // Verificar si los elementos existen
        if (!nombre || !cedula || !celular || !direccion || !barrio || !departamento || !ciudad || !metodoPago) {
            console.log("🔍 DEBUG validaciones-compra.js - ERROR: Algunos elementos del formulario no existen");
            alert("Error: Algunos campos del formulario no se encontraron");
            return;
        }
        
        // Obtener valores
        const nombreVal = nombre.value.trim();
        const cedulaVal = cedula.value.trim();
        const celularVal = celular.value.trim();
        const direccionVal = direccion.value.trim();
        const barrioVal = barrio.value.trim();
        const departamentoVal = departamento.value;
        const ciudadVal = ciudad.value;
        const metodoPagoVal = metodoPago.value;
        const correoVal = correo ? correo.value.trim() : "";
        
        console.log("🔍 DEBUG validaciones-compra.js - Valores del formulario:");
        console.log("- Nombre:", nombreVal);
        console.log("- Cédula:", cedulaVal);
        console.log("- Celular:", celularVal);
        console.log("- Dirección:", direccionVal);
        console.log("- Barrio:", barrioVal);
        console.log("- Departamento:", departamentoVal);
        console.log("- Ciudad:", ciudadVal);
        console.log("- Método de pago:", metodoPagoVal);
        console.log("- Correo:", correoVal);
        
        // Validaciones básicas
        if (!nombreVal || !cedulaVal || !celularVal || !direccionVal || !barrioVal || !departamentoVal || !ciudadVal || !metodoPagoVal) {
            console.log("🔍 DEBUG validaciones-compra.js - Faltan campos requeridos");
            console.log("🔍 DEBUG validaciones-compra.js - Campos faltantes:");
            if (!nombreVal) console.log("  - Nombre");
            if (!cedulaVal) console.log("  - Cédula");
            if (!celularVal) console.log("  - Celular");
            if (!direccionVal) console.log("  - Dirección");
            if (!barrioVal) console.log("  - Barrio");
            if (!departamentoVal) console.log("  - Departamento");
            if (!ciudadVal) console.log("  - Ciudad");
            if (!metodoPagoVal) console.log("  - Método de pago");
            alert("Por favor completa todos los campos requeridos");
            return;
        }
        
        // Verificar campo de correo
        const campoCorreo = document.getElementById("campo-correo");
        console.log("🔍 DEBUG validaciones-compra.js - Campo correo elemento:", campoCorreo);
        console.log("🔍 DEBUG validaciones-compra.js - Campo correo display:", campoCorreo ? campoCorreo.style.display : "NO ENCONTRADO");
        
        // Si es Nequi y el campo de correo está visible, validar correo
        if (metodoPagoVal === "nequi" && campoCorreo && campoCorreo.style.display !== "none") {
            console.log("🔍 DEBUG validaciones-compra.js - Validando correo para Nequi");
            if (!correoVal) {
                console.log("🔍 DEBUG validaciones-compra.js - Falta correo para Nequi");
                alert("Por favor ingresa tu correo electrónico");
                return;
            }
        }
        
        console.log("🔍 DEBUG validaciones-compra.js - Formulario válido, preparando datos para pago");
        console.log("🔍 DEBUG validaciones-compra.js - Método de pago seleccionado:", metodoPagoVal);
        console.log("🔍 DEBUG validaciones-compra.js - Ciudad seleccionada:", ciudadVal);
        console.log("🔍 DEBUG validaciones-compra.js - Departamento seleccionado:", departamentoVal);
        
        // Obtener datos del carrito
        console.log("🔍 DEBUG validaciones-compra.js - Obteniendo datos del carrito...");
        fetch('http://127.0.0.1/finoso/php/mostrar_carrito.php')
            .then(response => response.json())
            .then(data => {
                console.log("🔍 DEBUG validaciones-compra.js - Datos del carrito recibidos:", data);
                console.log("🔍 DEBUG validaciones-compra.js - data.success:", data.success);
                console.log("🔍 DEBUG validaciones-compra.js - data.relojes:", data.relojes);
                console.log("🔍 DEBUG validaciones-compra.js - data.relojes.length:", data.relojes ? data.relojes.length : "undefined");
                
                if (data.success && data.relojes && data.relojes.length > 0) {
                    console.log("🔍 DEBUG validaciones-compra.js - Entrando al bloque de procesamiento");
                    // Obtener costo de envío basado en la ciudad seleccionada
                    console.log("🔍 DEBUG validaciones-compra.js - Obteniendo costo de envío para ciudad:", ciudadVal);
                    console.log("🔍 DEBUG validaciones-compra.js - Departamento:", departamentoVal);
                    console.log("🔍 DEBUG validaciones-compra.js - URL de consulta:", `http://127.0.0.1/finoso/informacion/php/obtener_ciudades.php?action=ciudades&departamento=${departamentoVal}`);
                    
                    fetch(`http://127.0.0.1/finoso/informacion/php/obtener_ciudades.php?action=ciudades&departamento=${departamentoVal}`)
                        .then(response => {
                            console.log("🔍 DEBUG validaciones-compra.js - Respuesta de ciudades recibida:", response);
                            return response.json();
                        })
                        .then(ciudadesData => {
                            console.log("🔍 DEBUG validaciones-compra.js - Datos de ciudades:", ciudadesData);
                            let costoEnvio = 0;
                            if (ciudadesData.success && ciudadesData.ciudades) {
                                console.log("🔍 DEBUG validaciones-compra.js - Buscando ciudad:", ciudadVal, "en:", ciudadesData.ciudades);
                                console.log("🔍 DEBUG validaciones-compra.js - Tipo de ciudadVal:", typeof ciudadVal);
                                console.log("🔍 DEBUG validaciones-compra.js - Valor exacto de ciudadVal:", JSON.stringify(ciudadVal));
                                
                                const ciudadSeleccionada = ciudadesData.ciudades.find(ciudad => {
                                    console.log("🔍 DEBUG validaciones-compra.js - Comparando:", JSON.stringify(ciudad.ciudad), "con", JSON.stringify(ciudadVal));
                                    return ciudad.ciudad === ciudadVal;
                                });
                                
                                console.log("🔍 DEBUG validaciones-compra.js - Ciudad seleccionada encontrada:", ciudadSeleccionada);
                                if (ciudadSeleccionada) {
                                    costoEnvio = parseFloat(ciudadSeleccionada.precio);
                                    console.log("🔍 DEBUG validaciones-compra.js - Costo de envío encontrado:", costoEnvio);
                                    console.log("🔍 DEBUG validaciones-compra.js - Precio original:", ciudadSeleccionada.precio);
                                } else {
                                    console.log("🔍 DEBUG validaciones-compra.js - Ciudad no encontrada, usando costo por defecto");
                                    console.log("🔍 DEBUG validaciones-compra.js - Ciudades disponibles:", ciudadesData.ciudades.map(c => c.ciudad));
                                }
                            } else {
                                console.log("🔍 DEBUG validaciones-compra.js - Error en datos de ciudades o no hay ciudades");
                            }
                            
                            // Preparar datos para el pago
                            const datosPago = {
                                productos: data.relojes.map(reloj => ({
                                    id: reloj.id_reloj,
                                    nombre: reloj.nombre,
                                    precio: reloj.precio_final,
                                    precio_original: reloj.precio,
                                    imagen: reloj.img,
                                    cantidad: 1
                                })),
                                total: data.total,
                                costo_envio: costoEnvio,
                                datos_cliente: {
                                    nombre: nombreVal,
                                    cedula: cedulaVal,
                                    celular: celularVal,
                                    direccion: direccionVal,
                                    barrio: barrioVal,
                                    departamento: departamentoVal,
                                    ciudad: ciudadVal,
                                    correo: correoVal
                                }
                            };
                            
                            console.log("🔍 DEBUG validaciones-compra.js - Datos preparados:", datosPago);
                            console.log("🔍 DEBUG validaciones-compra.js - Costo de envío final:", costoEnvio);
                            
                            // Guardar en localStorage
                            localStorage.setItem("nequi_datos_pago", JSON.stringify(datosPago));
                            console.log("🔍 DEBUG validaciones-compra.js - Datos guardados en localStorage");
                            console.log("🔍 DEBUG validaciones-compra.js - Verificación localStorage:", JSON.parse(localStorage.getItem("nequi_datos_pago")));
                            console.log("🔍 DEBUG validaciones-compra.js - Costo de envío en localStorage:", JSON.parse(localStorage.getItem("nequi_datos_pago")).costo_envio);
                            
                            // Redirigir según el método de pago
                            if (metodoPagoVal === "nequi") {
                                console.log("🔍 DEBUG validaciones-compra.js - Redirigiendo a pago_nequi-carrito.html");
                                window.location.href = "pago_nequi-carrito.html";
                            } else if (metodoPagoVal === "wompi") {
                                console.log("🔍 DEBUG validaciones-compra.js - Procesando pago con Wompi");
                                procesarPagoWompi(data);
                            } else {
                                console.log("🔍 DEBUG validaciones-compra.js - Método de pago no reconocido:", metodoPagoVal);
                                alert("Método de pago no válido");
                            }
                        })
                        .catch(error => {
                            console.error("🔍 DEBUG validaciones-compra.js - Error obteniendo costo de envío:", error);
                            // En caso de error, usar costo 0
                            const datosPago = {
                                productos: data.relojes.map(reloj => ({
                                    id: reloj.id_reloj,
                                    nombre: reloj.nombre,
                                    precio: reloj.precio_final,
                                    precio_original: reloj.precio,
                                    imagen: reloj.img,
                                    cantidad: 1
                                })),
                                total: data.total,
                                costo_envio: 0,
                                datos_cliente: {
                                    nombre: nombreVal,
                                    cedula: cedulaVal,
                                    celular: celularVal,
                                    direccion: direccionVal,
                                    barrio: barrioVal,
                                    departamento: departamentoVal,
                                    ciudad: ciudadVal,
                                    correo: correoVal
                                }
                            };
                            
                            localStorage.setItem("nequi_datos_pago", JSON.stringify(datosPago));
                            
                            if (metodoPagoVal === "nequi") {
                                window.location.href = "pago_nequi-carrito.html";
                            }
                        });
                } else {
                    console.log("🔍 DEBUG validaciones-compra.js - Error: No hay productos en el carrito");
                    console.log("🔍 DEBUG validaciones-compra.js - Condiciones fallidas:");
                    console.log("  - data.success:", data.success);
                    console.log("  - data.relojes existe:", !!data.relojes);
                    console.log("  - data.relojes.length:", data.relojes ? data.relojes.length : "undefined");
                    alert("No hay productos en el carrito");
                }
            })
            .catch(error => {
                console.error("🔍 DEBUG validaciones-compra.js - Error obteniendo datos del carrito:", error);
                alert("Error al obtener los datos del carrito");
            });
    }
    
    if (botonComprar) {
        console.log("🔍 DEBUG validaciones-compra.js - Agregando listener al botón encontrado");
        console.log("🔍 DEBUG validaciones-compra.js - Botón encontrado:", botonComprar);
        console.log("🔍 DEBUG validaciones-compra.js - Texto del botón:", botonComprar.textContent);
        botonComprar.addEventListener("click", manejarClickComprar);
        console.log("🔍 DEBUG validaciones-compra.js - Listener agregado exitosamente");
    } else {
        console.log("🔍 DEBUG validaciones-compra.js - Botón no encontrado, agregando listener a todos los botones");
        document.querySelectorAll('button').forEach((btn, index) => {
            console.log("🔍 DEBUG validaciones-compra.js - Botón", index, ":", btn.textContent, "Tipo:", btn.type);
            btn.addEventListener("click", function(e) {
                console.log("🔍 DEBUG validaciones-compra.js - Click en botón:", btn.textContent);
                if (btn.textContent.includes("Finalizar") || btn.textContent.includes("Comprar")) {
                    console.log("🔍 DEBUG validaciones-compra.js - Click en botón de compra alternativo");
                    manejarClickComprar(e);
                }
            });
        });
    }
    
    if (formEnvio) {
        formEnvio.addEventListener("submit", function(e) {
            console.log("🔍 DEBUG validaciones-compra.js - Formulario enviado (evento submit)");
            e.preventDefault();
            // La lógica ya está en el click del botón
        });
    }

    // Inicializar departamentos al cargar la página
    console.log("🔍 DEBUG validaciones-compra.js - DOM cargado, iniciando carga de departamentos");
    cargarDepartamentos();

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
    const STORAGE_KEY = 'finoso_form_data_carrito';

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
        
        console.log("🔍 DEBUG saveFormData - Guardando datos:", data);
        console.log("🔍 DEBUG saveFormData - Departamento seleccionado:", departamentoSelect.value);

        // Si el método de pago es Nequi o Wompi, también guardar el correo
        if (metodoPagoInput.value === "nequi" || metodoPagoInput.value === "wompi") {
            data.correo = document.getElementById('correo').value;
        }

        localStorage.setItem(STORAGE_KEY, JSON.stringify(data));
    }

    function loadFormData() {
        const saved = localStorage.getItem(STORAGE_KEY);
        if (!saved) {
            console.log("🔍 DEBUG loadFormData - No hay datos guardados en localStorage");
            return;
        }
        
        const data = JSON.parse(saved);
        console.log("🔍 DEBUG loadFormData - Datos cargados desde localStorage:", data);
        
        // Llenar campos de texto
        Object.keys(data).forEach(key => {
            if (key !== 'departamento' && key !== 'ciudad' && key !== 'metodo_pago') {
                const el = document.getElementById(key);
                if (el && data[key]) el.value = data[key];
            }
        });
        
        // Departamento y ciudad
        if (data.departamento) {
            console.log("🔍 DEBUG loadFormData - Cargando departamento:", data.departamento);
            departamentoSelect.value = data.departamento;
            
            // Forzar el evento change para cargar las ciudades
            const changeEvent = new Event('change', { bubbles: true });
            departamentoSelect.dispatchEvent(changeEvent);
            
            // Esperar un poco más para que se carguen las ciudades
            setTimeout(() => {
                if (data.ciudad) {
                    console.log("🔍 DEBUG loadFormData - Cargando ciudad:", data.ciudad);
                    ciudadSelect.value = data.ciudad;
                }
            }, 200);
        }
        
        // Método de pago
        if (data.metodo_pago) {
            metodoPagoInput.value = data.metodo_pago;
            document.querySelectorAll('.boton-metodo img').forEach(img => {
                img.addEventListener('click', function () {
                    const metodo = this.dataset.metodo;
                    document.getElementById('metodo-pago').value = metodo;
                    console.log("✅ Método de pago seleccionado:", metodo);
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
            // Quitar selección previa de todas las imágenes
            document.querySelectorAll(".boton-metodo img").forEach(img => img.classList.remove("seleccionado"));
            
            // Aplicar clase seleccionado directamente a la imagen
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
                            correoInput.value = respuesta.correo;
                        } else {
                            // No hay sesión, sí mostrar
                            campoCorreo.style.display = "block";
                            correoInput.required = true;
                            correoInput.value = "";
                        }
                    });
            } else if (img.dataset.metodo === "wompi") {
                // Para Wompi, siempre mostrar campo correo
                campoCorreo.style.display = "block";
                correoInput.required = true;
                correoInput.value = "";
            } else {
                // Otros métodos
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

        if (metodoPagoInput.value === "nequi" || metodoPagoInput.value === "wompi") {
            const correoValido = validateField("correo", /^[^\s@]+@[^\s@]+\.[^\s@]+$/, "Por favor, ingresa un correo electrónico válido.");
            validations.push(correoValido);
        }

        const campoCorreo = document.getElementById("campo-correo");
        if ((metodoPagoInput.value === "nequi" || metodoPagoInput.value === "wompi") && campoCorreo.style.display !== "none") {
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

        const carrito = JSON.parse(localStorage.getItem('carrito') || '[]');

        if (carrito.length === 0) {
            alert("El carrito está vacío.");
            return;
        }

        data.productos = carrito;
        console.log("Datos del formulario:", data);
        console.log("Productos en el carrito:", carrito);

        if (!data.productos || data.productos.length === 0) {
            alert("Error: no se encontró el reloj.");
            return;
        }

        // Obtener el valor del envío desde el data-attribute (valor real sin formato)
        const precioEnvioElement = document.getElementById('precio-envio');
        const costoEnvio = precioEnvioElement ? parseInt(precioEnvioElement.dataset.precio || 0) : 0;

        console.log('💰 Costo de envío capturado:', costoEnvio, 'COP');

        // Validar que el costo de envío sea válido
        if (costoEnvio <= 0) {
            alert('Error: No se pudo obtener el costo de envío');
            return;
        }

        // AGREGAR el costo de envío a la data existente (NO sobrescribir)
        data.costo_envio = costoEnvio;

        if (metodoPagoInput.value === "wompi") {
            procesarPagoWompi(data);
        } else if (metodoPagoInput.value === "mercado_pago") {
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

                   function redondearMiles(valor) {
                        return Math.round(valor) * 1000;
                    }

                    const subtotal = carrito.reduce((total, producto) => {
                    const precio = parseFloat(producto.currentPrice); // ya viene en miles
                    const cantidad = producto.cantidad || 1;

                    return total + (precio * cantidad);
                }, 0);

                // 🔁 Redondear el subtotal al múltiplo de mil
                data.subtotal = redondearMiles(subtotal);
                data.productos = carrito;

                console.log("✅ Subtotal redondeado:", data.subtotal);


                    return fetch("http://127.0.0.1/finoso/informacion-carrito/php/crear_pago_nequi.php", {
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

                    // Guardamos en localStorage
                    const datosAGuardar = {
                        productos: respuestaBD.productos,           // ✅ Del backend
                        subtotal: respuestaBD.subtotal,            // ✅ Del backend
                        costo_envio: respuestaBD.costo_envio,      // ✅ Del backend  
                        total: respuestaBD.total,                  // ✅ Del backend
                        datos_cliente: {
                            ...respuestaBD.datos_cliente,
                            correo: data.correo
                        }
                    };
    
                    // 🔍 DEBUG: Verificar valores específicos
                    console.log("🔢 Valores individuales:");
                    console.log("   Subtotal backend:", respuestaBD.subtotal);
                    console.log("   Costo envío:", respuestaBD.costo_envio);
                    console.log("   Total backend:", respuestaBD.total);
                    console.log("   ¿Total = Subtotal + Envío?", respuestaBD.total === (respuestaBD.subtotal + respuestaBD.costo_envio));

                    console.log("💾 Guardando en localStorage (nequi_datos_pago):", datosAGuardar);
                    localStorage.setItem("nequi_datos_pago", JSON.stringify(datosAGuardar));

                    console.log("➡️ Redirigiendo a /finoso/pago_nequi.html");
                    window.location.href = "/finoso/informacion-carrito/pago_nequi-carrito.html";
                })
                .catch(err => {
                    console.error("🛑 Error en el flujo de pago Nequi:", err);
                    alert("Ocurrió un error durante el proceso de pago con Nequi. Intenta nuevamente.");
                });

            return;
        }
    });
    // Función para procesar pago con Wompi
    function procesarPagoWompi(data) {
        console.log("🔍 DEBUG validaciones-compra.js - Iniciando pago con Wompi para carrito");
        
        // Crear transacción con Wompi
        fetch("http://127.0.0.1/finoso/informacion-carrito/php/crear_transaccion_wompi_carrito.php", {
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

            console.log('🧾 Desglose del pago Wompi carrito:');
            console.log(`Productos: ${(respuesta.amount - data.costo_envio).toLocaleString('es-CO', {style: 'currency', currency: 'COP'})}`);
            console.log(`Envío: ${data.costo_envio.toLocaleString('es-CO', {style: 'currency', currency: 'COP'})}`);
            console.log(`Total: ${respuesta.amount.toLocaleString('es-CO', {style: 'currency', currency: 'COP'})}`);

            // Redirigir a VPOS con monto predefinido
            console.log('🔄 Redirigiendo a VPOS de Wompi...');
            
            if (respuesta.vpos_url) {
                console.log('✅ VPOS URL:', respuesta.vpos_url);
                console.log('💰 Monto:', respuesta.amount.toLocaleString('es-CO', {style: 'currency', currency: 'COP'}));
                alert('Redirigiendo a Wompi para completar el pago de ' + respuesta.amount.toLocaleString('es-CO', {style: 'currency', currency: 'COP'}));
                window.location.href = respuesta.vpos_url;
            } else {
                console.error('❌ No se recibió vpos_url');
                alert('Error: No se pudo generar el enlace de pago');
            }

        })
        .catch(err => {
            console.error("❌ Error al crear transacción Wompi:", err);
            alert(`Error al crear transacción: ${err.message || err.error || 'Error desconocido'}`);
        });
    }

    // Cargar datos al iniciar
    loadFormData();
});

// Función para verificar sesión
function verificarSesion() {
    fetch("http://127.0.0.1/finoso/login/php/verificar_sesion.php")
        .then(res => res.json())
        .then(data => {
            if (!data.logged_in) {
                // Si no hay sesión, redirigir al login
                alert('Tu sesión ha expirado. Por favor, inicia sesión nuevamente.');
                window.location.href = 'http://127.0.0.1/finoso/login/login.html';
                return;
            }
            console.log("🔍 DEBUG validaciones-compra.js - Sesión verificada correctamente");
        })
        .catch(err => {
            console.error('Error al verificar sesión:', err);
            // En caso de error, redirigir al login por seguridad
            window.location.href = 'http://127.0.0.1/finoso/login/login.html';
        });
}