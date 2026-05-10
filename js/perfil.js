/* TABS */
function showTab(tab) {
    document.querySelectorAll('.tab-content').forEach(e => e.classList.remove('active'));
    document.querySelectorAll('.tab').forEach(e => e.classList.remove('active'));
    document.getElementById(tab).classList.add('active');
    event.target.classList.add('active');
}


/* EDITAR RESEÑA */
async function abrirModalEditar(id_resena) {

    const contenido = document.getElementById('contenidoEditarResena');

    abrirModal('modalEditarResena');

    try {
        const res = await fetch(`modificar_resena.php?id=${id_resena}`);
        const html = await res.text();

        contenido.innerHTML = html;
    } catch (e) {
        contenido.innerHTML = "<p>Error cargando reseña</p>";
        console.error(e);
    }
}


/* ELIMINAR RESEÑA */
function abrirModalEliminar(id_resena) {
    document.getElementById('inputEliminarResena').value = id_resena;
    abrirModal('modalEliminarResena');
}

/* ELIMINAR LISTA PERFIL */
let listaPerfilAEliminar = null;

function abrirModalEliminarListaPerfil(id) {
    listaPerfilAEliminar = id;
    abrirModal('modalEliminarListaPerfil');
}

document.addEventListener('DOMContentLoaded', () => {

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
});

/* EDITAR LISTA PERFIL */
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