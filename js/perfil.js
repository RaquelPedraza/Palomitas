/* TABS */
function showTab(tab) {
    document.querySelectorAll('.tab-content').forEach(e => e.classList.remove('active'));
    document.querySelectorAll('.tab').forEach(e => e.classList.remove('active'));
    document.getElementById(tab).classList.add('active');
    event.target.classList.add('active');
}


/* EDITAR RESEÑA */
function abrirModalEditarResena(id, puntuacion, titulo, contenido) {
    document.getElementById('editar_id_resena').value = id;
    document.getElementById('editar_titulo_resena').value = titulo;
    document.getElementById('editar_contenido_resena').value = contenido;

    const radio = document.querySelector(
        `#formEditarResena input[name="puntuacion"][value="${puntuacion}"]`
    );

    if (radio) {
        radio.checked = true;
    }

    abrirModal('modalEditarResena');
}


/* ELIMINAR RESEÑA */
let idResenaEliminar = null;

function abrirModalEliminar(id) {
    idResenaEliminar = id;
    abrirModal('modalEliminarResena');
}

/* MODAL ELIMINAR LISTA PERFIL */
let listaPerfilAEliminar = null;

function abrirModalEliminarListaPerfil(id) {
    listaPerfilAEliminar = id;
    abrirModal('modalEliminarListaPerfil');
}

/* MODAL EDITAR LISTA PERFIL */
function abrirModalEditarListaPerfil(id, nombre, descripcion, visibilidad) {

    document.getElementById('editar_id_lista').value = id;
    document.getElementById('editar_nombre').value = nombre;
    document.getElementById('editar_descripcion').value = descripcion;

    if (visibilidad === 'publica') {
        document.getElementById('edit_publica').checked = true;
    } else {
        document.getElementById('edit_privada').checked = true;
    }

    abrirModal('modalEditarLista');
}

document.addEventListener('DOMContentLoaded', () => {
    /* EDITAR RESEÑA */
    const formEditarResena = document.getElementById('formEditarResena');

    if (formEditarResena) {
        formEditarResena.addEventListener('submit', async (e) => {

            e.preventDefault();

            const formData = new FormData(formEditarResena);

            try {
                const res = await fetch('guardar_resena.php', {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: formData
                });

                const data = await res.json();

                if (!data.ok) {
                    alert('Error al editar reseña');
                    return;
                }

                location.reload();

            } catch (err) {

                console.error(err);
                alert('Error inesperado');
            }
        });
    }

    /* ELIMINAR RESEÑA */
    const btnEliminar = document.getElementById('confirmarEliminarResena');

    if (btnEliminar) {
        btnEliminar.addEventListener('click', async () => {

            try {

                const formData = new FormData();

                formData.append('action', 'eliminar');
                formData.append('id_resena', idResenaEliminar);

                const res = await fetch('guardar_resena.php', {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: formData
                });

                const data = await res.json();

                if (!data.ok) {
                    alert('Error al eliminar');
                    return;
                }

                location.reload();

            } catch (err) {

                console.error(err);
            }
        });
    }

    /* ELIMINAR LISTA */
    const btn = document.getElementById('confirmarEliminarListaPerfil');

    if (btn) {
        btn.addEventListener('click', async () => {

            const formData = new FormData();
            formData.append('action', 'eliminar');
            formData.append('id_lista', listaPerfilAEliminar);

            const res = await fetch('listas.php', {
                method: 'POST',
                body: formData
            });

            const data = await res.json();

            if (!data.ok) return;

            document
                .querySelector(`[data-lista-id="${listaPerfilAEliminar}"]`)
                ?.remove();

            cerrarModal('modalEliminarListaPerfil');
        });
    }

    /* EDITAR LISTA */
    const formEditar = document.getElementById('formEditarListaPerfil');

    if (formEditar) {

        formEditar.addEventListener('submit', async (e) => {

            e.preventDefault();

            const formData = new FormData(formEditar);
            formData.append('action', 'editar');

            try {
                const res = await fetch('listas.php', {
                    method: 'POST',
                    body: formData
                });

                const data = await res.json();

                if (!data.ok) return;

                const id = document.getElementById('editar_id_lista').value;
                const nombre = document.getElementById('editar_nombre').value;
                const descripcion = document.getElementById('editar_descripcion').value;

                const card = document.querySelector(
                    `[data-lista-id="${id}"]`
                );

                if (card) {
                    const title = card.querySelector('h3');
                    if (title) title.textContent = nombre;

                    const desc = card.querySelector('p');
                    if (desc) desc.textContent = descripcion;
                }

                cerrarModal('modalEditarLista');

            } catch (err) {
                console.error(err);
            }
        });
    }
});

