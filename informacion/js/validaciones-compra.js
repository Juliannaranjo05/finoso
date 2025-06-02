document.addEventListener("DOMContentLoaded", function () {
    const metodoPagoInput = document.getElementById('metodo-pago');
    const imagenesPago = document.querySelectorAll('.boton-metodo img');
    const errorMetodo = document.getElementById('error-metodo-pago');
    const form = document.getElementById("form-envio");
    const ciudadesPorDepartamento = {
        "Amazonas": ["Leticia", "Puerto Nariño"],
        "Antioquia": ["Medellín", "Bello", "Itagüí", "Envigado", "Rionegro", "Apartadó", "Turbo", "La Ceja", "Sabaneta", "Copacabana", "Marinilla", "Guarne", "Amagá", "Caucasia"],
        "Arauca": ["Arauca", "Arauquita", "Saravena", "Tame", "Cravo Norte", "Puerto Rondón", "Fortul"],
        "Atlántico": ["Barranquilla", "Soledad", "Malambo", "Sabanalarga", "Puerto Colombia", "Galapa", "Baranoa", "Polonuevo", "Sabanagrande", "Campo de la Cruz"],
        "Bolívar": ["Cartagena", "Magangué", "Turbaco", "Arjona", "El Carmen de Bolívar", "Mompox", "San Juan Nepomuceno", "María la Baja", "Santa Rosa", "Talaigua Nuevo"],
        "Boyacá": ["Tunja", "Duitama", "Sogamoso", "Chiquinquirá", "Paipa", "Moniquirá", "Villa de Leyva", "Puerto Boyacá", "Nobsa", "Tibasosa", "Samacá"],
        "Caldas": ["Manizales", "La Dorada", "Chinchiná", "Villamaría", "Riosucio", "Anserma", "Salamina", "Aguadas"],
        "Caquetá": ["Florencia", "San Vicente del Caguán", "Puerto Rico", "El Doncello", "La Montañita", "Belén de los Andaquíes"],
        "Casanare": ["Yopal", "Aguazul", "Villanueva", "Tauramena", "Monterrey", "Maní", "Paz de Ariporo", "Trinidad"],
        "Cauca": ["Popayán", "Santander de Quilichao", "Puerto Tejada", "Patía", "El Tambo", "Guapi", "Timbiquí", "Silvia", "Rosas"],
        "Cesar": ["Valledupar", "Aguachica", "La Jagua de Ibirico", "Codazzi", "Bosconia", "Chimichagua", "Curumaní", "El Copey"],
        "Chocó": ["Quibdó", "Istmina", "Tadó", "Condoto", "Bahía Solano", "Acandí", "Nuquí", "Lloró"],
        "Córdoba": ["Montería", "Lorica", "Sahagún", "Cereté", "Tierralta", "Planeta Rica", "Montelíbano", "Ayapel"],
        "Cundinamarca": ["Bogotá", "Soacha", "Chía", "Zipaquirá", "Facatativá", "Girardot", "Fusagasugá", "Mosquera", "Cajicá", "La Calera", "Madrid", "Funza", "Sibaté"],
        "Guainía": ["Inírida", "Barranco Minas", "Cacahual", "Pana Pana"],
        "Guaviare": ["San José del Guaviare", "Calamar", "Miraflores", "El Retorno"],
        "Huila": ["Neiva", "Pitalito", "Garzón", "La Plata", "Campoalegre", "Rivera", "Yaguará", "Aipe"],
        "La Guajira": ["Riohacha", "Maicao", "Uribia", "Fonseca", "San Juan del Cesar", "Manaure", "Dibulla"],
        "Magdalena": ["Santa Marta", "Ciénaga", "Fundación", "El Banco", "Plato", "Aracataca", "Pivijay", "Zona Bananera"],
        "Meta": ["Villavicencio", "Acacías", "Granada", "Puerto López", "San Martín", "Cumaral", "Guamal", "Cabuyaro"],
        "Nariño": ["Pasto", "Tumaco", "Ipiales", "Túquerres", "La Unión", "Sandoná", "El Tambo", "Barbacoas"],
        "Norte de Santander": ["Cúcuta", "Ocaña", "Pamplona", "Villa del Rosario", "Los Patios", "Chinácota", "Tibú"],
        "Putumayo": ["Mocoa", "Puerto Asís", "Orito", "Sibundoy", "Valle del Guamuez", "Puerto Caicedo"],
        "Quindío": ["Armenia", "Calarcá", "Montenegro", "La Tebaida", "Quimbaya", "Circasia", "Filandia"],
        "Risaralda": ["Pereira", "Dosquebradas", "Santa Rosa de Cabal", "La Virginia", "Belén de Umbría", "Marsella"],
        "San Andrés y Providencia": ["San Andrés", "Providencia"],
        "Santander": ["Bucaramanga", "Floridablanca", "Girón", "Piedecuesta", "Barrancabermeja", "San Gil", "Socorro", "Lebrija"],
        "Sucre": ["Sincelejo", "Corozal", "Sampués", "San Marcos", "Tolú", "San Onofre"],
        "Tolima": ["Ibagué", "Espinal", "Melgar", "Honda", "Líbano", "Chaparral", "Mariquita", "Guamo"],
        "Valle del Cauca": ["Cali", "Palmira", "Buenaventura", "Tuluá", "Buga", "Yumbo", "Cartago", "Jamundí", "Candelaria"],
        "Vaupés": ["Mitú", "Carurú", "Taraira", "Pacoa"],
        "Vichada": ["Puerto Carreño", "La Primavera", "Santa Rosalía", "Cumaribo"]
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
});