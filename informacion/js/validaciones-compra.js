document.addEventListener("DOMContentLoaded", function () {
    const metodoPagoInput = document.getElementById('metodo-pago');
    const imagenesPago = document.querySelectorAll('.boton-metodo img');
    const errorMetodo = document.getElementById('error-metodo-pago');
    const form = document.getElementById("form-envio");

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

    const departamentoSelect = document.getElementById("departamento");
    const ciudadSelect = document.getElementById("ciudad");

    form.addEventListener("submit", function (e) {
        let hasError = false;

        hasError |= !validateField("nombre", /^[\s\S]{1,30}$/, "Máximo 30 caracteres.");
        hasError |= !validateField("cedula", /^\d{6,10}$/, "Solo números (6 a 10 dígitos).");
        hasError |= !validateField("celular", /^\d{10}$/, "Debe tener 10 dígitos numéricos.");
        hasError |= !validateField("ciudad", /^.{1,50}$/, "Máximo 50 caracteres.");
        hasError |= !validateField("direccion", /^.{1,50}$/, "Máximo 50 caracteres.");
        hasError |= !validateField("barrio", /^.{1,50}$/, "Máximo 50 caracteres.");
        hasError |= validateOptionalField("referencias", /^.{0,100}$/, "Máximo 100 caracteres.");

        if (metodoPagoInput.value === '') {
            errorMetodo.textContent = 'Por favor selecciona un método de pago.';
            hasError = true;
        }

        if (hasError) {
            e.preventDefault();
            errorMetodo.scrollIntoView({ behavior: "smooth", block: "center" });
        }
    });

    function validateField(id, regex, message) {
        const field = document.getElementById(id);
        const errorSpan = document.getElementById("error-" + id);
        field.style.border = "";
        errorSpan.textContent = "";

        if (!regex.test(field.value.trim())) {
            field.style.border = "2px solid red";
            errorSpan.textContent = message;
            return false;
        }
        return true;
    }

    function validateOptionalField(id, regex, message) {
        const field = document.getElementById(id);
        const errorSpan = document.getElementById("error-" + id);
        field.style.border = "";
        errorSpan.textContent = "";

        if (field.value.trim() !== "" && !regex.test(field.value.trim())) {
            field.style.border = "2px solid red";
            errorSpan.textContent = message;
            return true;
        }
        return false;
    }

    departamentoSelect.addEventListener("change", function () {
        const selectedDepto = this.value;
        ciudadSelect.innerHTML = `<option value="">Selecciona una ciudad</option>`;

        if (ciudadesPorDepartamento[selectedDepto]) {
            ciudadesPorDepartamento[selectedDepto].forEach(ciudad => {
                const option = document.createElement("option");
                option.value = ciudad;
                option.textContent = ciudad;
                ciudadSelect.appendChild(option);
            });
        }
    });

    imagenesPago.forEach(img => {
        img.addEventListener('click', () => {
            // Quitar selección anterior
            imagenesPago.forEach(i => i.classList.remove('seleccionado'));

            // Marcar seleccionado actual
            img.classList.add('seleccionado');

            // Guardar el método de pago seleccionado
            metodoPagoInput.value = img.dataset.metodo || '';

            console.log("Método seleccionado:", metodoPagoInput.value);

            // Limpiar error si existía
            errorMetodo.textContent = '';
        });
    });

    ciudadSelect.addEventListener("change", function () {
        const destinoDepto = departamentoSelect.value;
        const destinoCiudad = ciudadSelect.value;

        if (destinoDepto && destinoCiudad) {
            obtenerCotizacion(destinoDepto, destinoCiudad);
            obtenerProvincias();
        }
    });


    function obtenerCotizacion(departamento, ciudad) {
        const token = "451a74674f2b372e19edffe9d124c92135be3116e3b39670f36126b4f52652a1";

        const payload = {
            origin: {
                name: "Tienda Web",
                company: "finoso",
                email: "davidpascuas708@gmail.com",
                phone: "3173897119",
                street: "Calle 42A",
                number: "2W - 74",
                district: "other",
                city: "NEIVA",
                state: "HU",
                country: "CO",
                postalCode: "410001",
                reference: "",
                coordinates: {
                    latitude: "2.9386",
                    longitude: "-75.2899"
                }
            },
            destination: {
                name: "Cliente Final",
                company: "",
                email: "cliente@correo.com",
                phone: "3000000000",
                street: "Cra 7",
                number: "12",
                district: "other",
                city: ciudad.toUpperCase(),
                state: departamento.toUpperCase(),
                country: "CO",
                postalCode: "050001",
                reference: "",
                coordinates: {
                    latitude: "4.7110",  // Coordenadas genéricas de Colombia
                    longitude: "-74.0721"
                }
            },
            packages: [
                {
                    content: "reloj",
                    amount: 1,
                    type: "box",
                    weight: 1,
                    insurance: 0,
                    declaredValue: 50000,
                    weightUnit: "KG",
                    lengthUnit: "CM",
                    dimensions: {
                        length: 30,
                        width: 20,
                        height: 10
                    }
                }
            ],
            shipment: {
                carrier: "servientrega",  // Puedes cambiarlo dinámicamente
                type: 1
            },
            settings: {
                currency: "COP"
            }
        };

        console.log("📤 Payload Enviado:", JSON.stringify(payload, null, 2));

        fetch("https://api.envia.com/ship/rate/", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "Authorization": `Bearer ${token}`
            },
            body: JSON.stringify(payload)
        })
        .then(async response => {
            const text = await response.text();
            console.log("📥 Respuesta cruda:", text);

            if (text.startsWith("<!DOCTYPE") || text.includes("<html")) {
                document.getElementById("precio-envio").innerText = "❌ Error interno del servidor (HTML devuelto)";
                return;
            }

            let data;
            try {
                data = JSON.parse(text);
            } catch (err) {
                console.error("❌ Error al parsear JSON:", err);
                document.getElementById("precio-envio").innerText = "❌ Respuesta no válida del servidor.";
                return;
            }

            const precioEnvioDiv = document.getElementById("precio-envio");

            if (Array.isArray(data)) {
                let html = "<strong>Opciones de envío:</strong><ul>";
                data.forEach(opcion => {
                    html += `<li>${opcion.courier.name}: $${opcion.total.toLocaleString('es-CO')}</li>`;
                });
                html += "</ul>";
                precioEnvioDiv.innerHTML = html;
            } else {
                console.warn("⚠️ Respuesta inesperada:", data);
                precioEnvioDiv.innerText = "❌ No se encontraron opciones de envío.";
            }
        })
        .catch(error => {
            console.error("❌ Error en la solicitud:", error);
            document.getElementById("precio-envio").innerText = "❌ Error al obtener precio de envío.";
        });
    }
});