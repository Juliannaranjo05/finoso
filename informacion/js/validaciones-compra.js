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

    // Métodos de pago
    document.querySelectorAll(".boton-metodo img").forEach(img => {
        img.addEventListener("click", () => {
            document.querySelectorAll(".boton-metodo img").forEach(i => i.classList.remove("seleccionado"));
            img.classList.add("seleccionado");
            metodoPagoInput.value = img.dataset.metodo;
            errorMetodo.textContent = "";
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

        if (!validations.every(v => v)) return;

        if (!metodoPagoInput.value) {
            errorMetodo.textContent = 'Por favor, selecciona un método de pago.';
            return;
        } else {
            errorMetodo.textContent = '';
        }

        const formData = new FormData(form);
        const data = Object.fromEntries(formData.entries());

        if (data.metodo_pago !== "mercado_pago") {
            alert("Por ahora solo estamos conectando Mercado Pago. Elige ese método para continuar.");
            return;
        }

        const params = new URLSearchParams(window.location.search);
        data.id_reloj = params.get("id_reloj");

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
            
            if (respuesta.init_point) {
                window.location.href = respuesta.init_point;
            }
        })
        .catch(err => {
            alert(`Error: ${err.message || 'No se pudo conectar con Mercado Pago'}`);
        });
    });

    // Cargar datos al iniciar
    loadFormData();
});