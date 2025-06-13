document.addEventListener("DOMContentLoaded", function () {
    const metodoPagoInput = document.getElementById('metodo-pago');
    const imagenesPago = document.querySelectorAll('.boton-metodo img');
    const errorMetodo = document.getElementById('error-metodo-pago');
    const form = document.getElementById("form-envio");

    const ciudadesPorDepartamento = {
        "05": ["Medellín", "Bello", "Itagüí", "Envigado"],
        "08": ["Barranquilla", "Soledad", "Malambo", "Puerto Colombia"],
        "11": ["Cartagena", "Turbaco", "Arjona", "Santa Rosa"],
        "15": ["Tunja", "Duitama", "Sogamoso", "Chiquinquirá"],
        "18": ["Florencia", "Belén de los Andaquíes", "San Vicente del Caguán", "Puerto Rico"],
        "19": ["Popayán", "Santander de Quilichao", "Puerto Tejada", "El Tambo"],
        "20": ["Valledupar", "Aguachica", "Codazzi", "Curumaní"],
        "23": ["Montería", "Lorica", "Sahagún", "Cereté"],
        "25": ["Bogotá", "Soacha", "Facatativá", "Zipaquirá"],
        "27": ["Quibdó", "Istmina", "Tadó", "Condoto"],
        "41": ["Neiva", "Pitalito", "Garzón", "La Plata"],
        "44": ["Riohacha", "Maicao", "Uribia", "Fonseca"],
        "47": ["Santa Marta", "Ciénaga", "Fundación", "El Banco"],
        "50": ["Villavicencio", "Acacías", "Granada", "Puerto López"],
        "52": ["Pasto", "Tumaco", "Ipiales", "Túquerres"],
        "54": ["Cúcuta", "Ocaña", "Pamplona", "Villa del Rosario"],
        "63": ["Armenia", "Calarcá", "La Tebaida", "Montenegro"],
        "66": ["Pereira", "Dosquebradas", "Santa Rosa de Cabal", "La Virginia"],
        "68": ["Bucaramanga", "Floridablanca", "Girón", "Piedecuesta"],
        "70": ["Sincelejo", "Corozal", "Sampués", "Tolú"],
        "73": ["Ibagué", "Espinal", "Melgar", "Honda"],
        "76": ["Cali", "Palmira", "Buenaventura", "Tuluá"],
        "81": ["Arauca", "Saravena", "Tame", "Arauquita"],
        "85": ["Yopal", "Aguazul", "Villanueva", "Paz de Ariporo"],
        "86": ["Mocoa", "Villagarzón", "Puerto Asís", "Orito"],
        "88": ["San Andrés", "Providencia"],
        "91": ["Leticia", "Puerto Nariño"],
        "94": ["Inírida"],
        "95": ["San José del Guaviare"],
        "97": ["Mitú"],
        "99": ["Puerto Carreño"]
    };

    form.addEventListener("submit", function (e) {
        const isNombreValid = validateField("nombre", /^[A-Za-zÁÉÍÓÚáéíóúñÑ\s]+$/, "Por favor, ingresa un nombre válido.");
        const isCedulaValid = validateField("cedula", /^\d{6,10}$/, "Por favor, ingresa una cédula válida.");
        const isCelularValid = validateField("celular", /^\d{10}$/, "Por favor, ingresa un número de celular válido.");
        const isCiudadValid = validateField("ciudad", /^[A-Za-zÁÉÍÓÚáéíóúñÑ\s]+$/, "Por favor, ingresa una ciudad válida.");
        const isDireccionValid = validateField("direccion", /^[A-Za-z0-9ÁÉÍÓÚáéíóúñÑ#\-\s]+$/, "Por favor, ingresa una dirección válida.");
        const isBarrioValid = validateField("barrio", /^[A-Za-zÁÉÍÓÚáéíóúñÑ\s]+$/, "Por favor, ingresa un barrio válido.");
        const isReferenciasValid = validateOptionalField("referencias", /^[A-Za-z0-9ÁÉÍÓÚáéíóúñÑ#\-\s]*$/, "Por favor, ingresa solo letras, números y símbolos válidos (#, -).");

        if (!isNombreValid || !isCedulaValid || !isCelularValid || !isCiudadValid || !isDireccionValid || !isBarrioValid || !isReferenciasValid) {
            e.preventDefault();
        }

        if (!metodoPagoInput.value) {
            errorMetodo.textContent = 'Por favor, selecciona un método de pago.';
            e.preventDefault();
        } else {
            errorMetodo.textContent = '';
        }
    });

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

    const departamentoSelect = document.getElementById("departamento");
    const ciudadSelect = document.getElementById("ciudad");

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
    });

    imagenesPago.forEach(img => {
        img.addEventListener('click', () => {
            imagenesPago.forEach(i => i.classList.remove('seleccionado'));
            img.classList.add('seleccionado');
            metodoPagoInput.value = img.dataset.metodo;
            errorMetodo.textContent = '';
        });
    });
});