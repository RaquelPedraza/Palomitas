document.addEventListener('DOMContentLoaded', () => {
    //CREAR LISTA
    const formCrearLista = document.getElementById('formCrearLista');
    if (formCrearLista) {

        formCrearLista.addEventListener('submit', async (e) => {
            e.preventDefault();
            const formData = new FormData(formCrearLista);
            formData.append('action', 'crear');

            /* Crear listas DROPDOWN */
            const dropdown = document.getElementById('listasDropdown');
            const idProduccion = dropdown?.dataset?.produccion || null;

            if (idProduccion) {
                formData.append('id_produccion', idProduccion);
            }

            try {
                const res = await fetch('listas.php', {
                    method: 'POST',
                    body: formData
                });

                const data = await res.json();

                if (data.ok) {
                    const card = document.querySelector(
                        `[data-lista-id="${listaAEliminar}"]`
                    );

                    if (card) {
                        card.style.transition = "opacity 0.2s ease";
                        card.style.opacity = "0";

                        setTimeout(() => card.remove(), 200);
                    }

                    cerrarModal('modalEliminarLista');
                }

            } catch (err) {
                console.error('Error creando lista:', err);
            }
        });
    }

    //EDITAR LISTA
    const formEditar = document.getElementById('formEditarLista');

    if (formEditar) {
        formEditar.addEventListener('submit', async (e) => {

            e.preventDefault();

            const formData = new FormData(formEditar);
            formData.append('action', 'editar');

            const res = await fetch('listas.php', {
                method: 'POST',
                body: formData
            });

            const data = await res.json();

            if (!data.ok) return;

            location.reload();
        });
    }

    //ELIMINAR LISTA
    const btnEliminar = document.getElementById('confirmarEliminarLista');

    if (btnEliminar) {
        btnEliminar.addEventListener('click', async () => {
            const formData = new FormData();
            formData.append('action', 'eliminar');
            formData.append('id_lista', listaAEliminar);

            try {
                const res = await fetch('listas.php', {
                    method: 'POST',
                    body: formData
                });

                const data = await res.json();

                if (!data.ok) return;

                location.href = 'mis_listas.php';

            } catch (err) {
                console.error(err);
            }
        });
    }
});

let listaAEliminar = null;

/* MODAL EDITAR */
function abrirModalEditarLista(id, nombre, descripcion, visibilidad) {

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

/* MODAL ELIMINAR */
function abrirModalEliminarLista(id) {
    listaAEliminar = id;
    abrirModal('modalEliminarLista');
}

//AÑADIR PELÍCULA A LISTA
async function addToList(id_produccion, id_lista) {

    // evitar opción vacía
    if (!id_lista) return;

    const formData = new FormData();

    formData.append('action', 'add');
    formData.append('id_produccion', id_produccion);
    formData.append('id_lista', id_lista);


    try {
        const res = await fetch('listas.php', {
            method: 'POST',
            body: formData
        });

        const data = await res.json();

        if (!data.ok) {
            console.log(data.error);
            return;
        }

        mostrarToast('Añadido a la lista correctamente');

    } catch (err) {
        console.error('Error añadiendo película:', err);

    }
}

/* ELIMINAR PELÍCULA DE LISTA */
async function eliminarPeliculaLista(id_produccion) {

    const id_lista = new URLSearchParams(window.location.search).get("id");

    const confirmar = confirm("¿Eliminar película?");
    if (!confirmar) return;

    const formData = new FormData();
    formData.append('action', 'remove');
    formData.append('id_lista', id_lista);
    formData.append('id_produccion', id_produccion);

    try {

        const res = await fetch('listas.php', {
            method: 'POST',
            body: formData
        });

        const data = await res.json();

        if (!data.ok) return;

         document.querySelector(`[data-id="${id_produccion}"]`)?.remove();

    } catch (err) {
        console.error(err);
    }
}