document.addEventListener("DOMContentLoaded", () => {

    // ABRIR MODAL
    window.abrirModal = function(id) {
        const modal = document.getElementById(id);
        if (!modal) return;

        modal.style.display = "flex";
        document.body.style.overflow = "hidden"; 
    };

    // CERRAR MODAL
    window.cerrarModal = function(id) {
        const modal = document.getElementById(id);
        if (!modal) return;

        modal.style.display = "none";
        document.body.style.overflow = "auto";
    };

    // CERRAR AL HACER CLICK FUERA
    document.addEventListener("click", function(e) {
        document.querySelectorAll(".modal").forEach(modal => {
            if (e.target === modal) {
                modal.style.display = "none";
                document.body.style.overflow = "auto";
            }
        });
    });

    // CERRAR CON ESC
    document.addEventListener("keydown", function(e) {
        if (e.key === "Escape") {
            document.querySelectorAll(".modal").forEach(modal => {
                modal.style.display = "none";
            });
            document.body.style.overflow = "auto";
        }
    });

});