document.addEventListener("DOMContentLoaded", function () {
    // Mostrar la primera pregunta
    let firstQuestion = document.getElementById("q1");
    firstQuestion.classList.add("active");
});

function nextQuestion(current) {
    let currentQuestion = document.getElementById(`q${current}`);
    let nextQuestion = document.getElementById(`q${current + 1}`);

    if (!nextQuestion) {
        submitSurvey();
        return;
    }

    // Oculta la pregunta actual
    currentQuestion.classList.remove("active");

    setTimeout(() => {
        currentQuestion.style.display = "none";

        // Muestra la siguiente pregunta
        nextQuestion.style.display = "block";
        setTimeout(() => {
            nextQuestion.classList.add("active");
        }, 10); // Pequeño retraso para asegurar la transición
    }, 500); // Espera la transición antes de ocultar completamente
}

function submitSurvey() {
    alert("Encuesta enviada. ¡Gracias por participar!");
}