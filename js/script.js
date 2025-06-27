// pantalla de carga
document.addEventListener("DOMContentLoaded", function() {
    // Espera a que todos los recursos de la página estén completamente cargados
    window.addEventListener("load", function() {
        // Añade la clase 'loaded' para activar las transiciones de CSS
        document.body.classList.add("loaded");
        document.querySelector(".overlay2").classList.add("loaded");

        // Oculta el overlay después de la transición (2 segundos en este caso)
        setTimeout(function() {
            document.querySelector(".overlay2").style.display = "none";
        }, 2000);
    });

    // Opción de respaldo: remueve el overlay después de 1 minuto si no se carga correctamente
    setTimeout(function() {
        document.body.classList.add("loaded");
        document.querySelector(".overlay2").style.display = "none";
    }, 60000);
});

