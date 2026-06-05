// Usuario con el que estamos hablando actualmente
let usuarioSeleccionado = null;

// Ref HTML
const listaUsuarios = document.getElementById("listaUsuarios");
const contenedorMensajes = document.getElementById("mensajes");
const formulario = document.getElementById("formMensaje");
const textarea = document.getElementById("mensaje");

cargarUsuarios();

/*Cargar usuarios funcion*/
async function cargarUsuarios() {

    try {

        const respuesta = await fetch("obtener_usuarios.php");
        const usuarios = await respuesta.json();

        listaUsuarios.innerHTML = "";

        usuarios.forEach(usuario => {

            const div = document.createElement("div");

            div.classList.add("usuario");

            div.innerHTML = `
                <div class="usuario-info">

                    <span>
                        ${usuario.nombre}
                    </span>

                    ${usuario.pendientes > 0
                    ?
                    `<span class="badge">
                            ${usuario.pendientes}
                        </span>`
                    :
                    ''
                }

                </div>
            `;

            div.addEventListener("click", () => {

                usuarioSeleccionado = usuario.id_usuario;

                cargarMensajes();

                document.getElementById("cabeceraChat")
                    .innerText = usuario.nombre;

            });

            listaUsuarios.appendChild(div);

        });

    } catch (error) {

        console.error("Error cargando usuarios:", error);

    }

}

/*Cargar mensajes funcion*/
async function cargarMensajes() {

    if (!usuarioSeleccionado) return;

    try {

        const respuesta = await fetch(
            `cargar_mensajes.php?usuario=${usuarioSeleccionado}`
        );

        const mensajes = await respuesta.json();

        contenedorMensajes.innerHTML = "";

        mensajes.forEach(mensaje => {

            const div = document.createElement("div");

            // Diferenciar mensajes propios y ajenos
            if (
                parseInt(mensaje.emisor_id)
                === parseInt(window.usuarioActual)
            ) {

                div.classList.add(
                    "mensaje",
                    "propio"
                );

            } else {

                div.classList.add(
                    "mensaje",
                    "ajeno"
                );

            }

            div.innerHTML = `
                ${escapeHTML(mensaje.mensaje)}
                <small>
                    ${mensaje.fecha_envio}
                </small>
            `;

            contenedorMensajes.appendChild(div);

        });

        // Scroll automático abajo
        contenedorMensajes.scrollTop =
            contenedorMensajes.scrollHeight;

    } catch (error) {

        console.error("Error cargando mensajes:", error);

    }

}

/*Enviar mensaje*/
formulario.addEventListener("submit", async function (e) {

    e.preventDefault();

    if (!usuarioSeleccionado) {

        alert("Selecciona primero un usuario.");

        return;
    }

    const texto = textarea.value.trim();

    if (texto === "") return;

    const datos = new FormData();

    datos.append(
        "receptor",
        usuarioSeleccionado
    );

    datos.append(
        "mensaje",
        texto
    );

    try {

        await fetch(
            "enviar_mensaje.php",
            {
                method: "POST",
                body: datos
            }
        );

        textarea.value = "";

        cargarMensajes();

    } catch (error) {

        console.error("Error enviando mensaje:", error);

    }

});

/*Actualización automatica*/
setInterval(() => {

    if (usuarioSeleccionado) {

        cargarMensajes();

    }

}, 2000);

/*
|--------------------------------------------------------------------------
| SEGURIDAD BÁSICA XSS
|--------------------------------------------------------------------------
*/
function escapeHTML(texto) {

    const div = document.createElement("div");

    div.innerText = texto;

    return div.innerHTML;
}