document.addEventListener("DOMContentLoaded", function () {
    let firstQuestion = document.getElementById("q1");
    firstQuestion.classList.add("active");
});

let answers = {};

function saveAnswer(question, value) {
    answers[question] = value;
}

function toggleSelection(element, question, value) {
    // Si la opción ya está seleccionada, la deseleccionamos
    if (element.classList.contains("selected")) {
        element.classList.remove("selected");
        removeAnswer(question, value); // Eliminar la respuesta del objeto `answers`
    } else {
        // Si no está seleccionada, la seleccionamos
        element.classList.add("selected");
        addAnswer(question, value); // Añadir la respuesta al objeto `answers`

        // Si se selecciona "Aceptar todo", deseleccionar las demás opciones
        if (value === "aceptar_todo") {
            let legalOptions = document.querySelectorAll("#q5 .mosaico-opcion");
            legalOptions.forEach(option => {
                let optionValue = option.getAttribute("onclick").split("'")[5];
                if (optionValue !== "aceptar_todo" && option.classList.contains("selected")) {
                    option.classList.remove("selected");
                    removeAnswer(question, optionValue); // Eliminar las demás respuestas
                }
            });
        } else {
            // Si se selecciona cualquier otra opción, deseleccionar "Aceptar todo"
            let aceptarTodoOption = document.querySelector("#q5 .mosaico-opcion[onclick*='aceptar_todo']");
            if (aceptarTodoOption.classList.contains("selected")) {
                aceptarTodoOption.classList.remove("selected");
                removeAnswer(question, "aceptar_todo"); // Eliminar "Aceptar todo"
            }
        }
    }

    // Forzar la actualización visual de la opción "Aceptar todo"
    if (value === "aceptar_todo") {
        element.classList.add("selected"); // Asegurarnos de que la clase "selected" se aplique
    }

    console.log("Respuestas actuales después de toggleSelection:", answers[question]); // Depuración
}

function addAnswer(question, value) {
    if (!answers[question]) {
        answers[question] = []; // Inicializamos como un array si no existe
    }
    if (value && !answers[question].includes(value)) { // Asegurarse de que el valor no sea null
        answers[question].push(value); // Añadir la respuesta al array
    }
}

function removeAnswer(question, value) {
    if (answers[question]) {
        answers[question] = answers[question].filter(item => item !== value); // Filtramos y eliminamos la respuesta
    }
}

function validateAndNext(current) {
    let currentQuestion = document.getElementById(`q${current}`);
    let inputs = currentQuestion.querySelectorAll("input[required], select[required]");
    let valid = true;

    // Validar inputs requeridos
    inputs.forEach(input => {
        if (!input.value.trim()) {
            valid = false;
            input.style.border = "2px solid red";
        } else {
            input.style.border = "";
        }
    });

    // Validar preguntas de tipo mosaico
    if (currentQuestion.getAttribute("data-type") === "mosaico") {
        let mosaicoKey = currentQuestion.getAttribute("data-key"); // Obtener la clave del mosaico
        if (!answers[mosaicoKey] || answers[mosaicoKey].length === 0) {
            valid = false;
            currentQuestion.querySelector(".mosaico").style.border = "2px solid red";
        } else {
            currentQuestion.querySelector(".mosaico").style.border = "";
        }
    }

    // Mostrar alerta si la validación falla
    if (!valid) {
        alert("Por favor, responde esta pregunta antes de continuar.");
        return;
    }

    // Avanzar a la siguiente pregunta
    nextQuestion(current);
}

function nextQuestion(current) {
    let currentQuestion = document.getElementById(`q${current}`);
    let nextQuestion = document.getElementById(`q${current + 1}`);

    if (!nextQuestion) {
        submitSurvey();
        return;
    }

    currentQuestion.classList.remove("active");
    setTimeout(() => {
        currentQuestion.style.display = "none";
        nextQuestion.style.display = "block";
        setTimeout(() => {
            nextQuestion.classList.add("active");
        }, 10);
    }, 500);
}
function selectAllLegalOptions() {
    let legalOptions = document.querySelectorAll("#q5 .mosaico-opcion");
    legalOptions.forEach(option => {
        let value = option.getAttribute("onclick").split("'")[5]; // Extraer el valor de la opción
        console.log("Opción:", option.textContent, "Valor:", value); // Depuración

        // Excluir la opción "No deseo recibir información adicional" (opcionD)
        if (value !== "opcionD") {
            if (!option.classList.contains("selected")) {
                option.classList.add("selected");
                addAnswer('legal', value); // Añadir la respuesta al objeto `answers`
            }
        } else {
            // Deseleccionar "No deseo recibir información adicional" si estaba seleccionada
            if (option.classList.contains("selected")) {
                option.classList.remove("selected");
                removeAnswer('legal', value); // Eliminar la respuesta del objeto `answers`
            }
        }
    });
    console.log("Respuestas finales después de 'Acepto todo':", answers['legal']); // Depuración
}

function submitSurvey() {
    // Obtener el título de la encuesta
    let tituloEncuesta = document.getElementById("titulo_encuesta").value;

    // Crear el objeto de datos con las respuestas y el título de la encuesta
    let data = {
        titulo_encuesta: tituloEncuesta, // Añadir el título de la encuesta
        respuestas: Object.keys(answers).map(key => ({
            pregunta: key,
            respuesta: answers[key]
        }))
    };

    console.log("Datos enviados al servidor:", data);

    // Enviar los datos al servidor
    fetch('/wp-content/themes/astra_child/save_survey.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json; charset=UTF-8' },
        body: JSON.stringify(data)
    })
    .then(response => {
        if (!response.ok) throw new Error("Error en la respuesta del servidor");
        return response.json();
    })
    .then(data => {
        console.log("Respuesta del servidor:", data);
        document.getElementById("surveyForm").style.display = "none";
        document.getElementById("ending").style.display = "block";
        setTimeout(() => {
            document.getElementById("ending").classList.add("active");
        }, 10);
    })
    .catch(error => {
        console.error('Error:', error);
        alert("Hubo un error al enviar la encuesta. Por favor, inténtalo de nuevo.");
    });
}