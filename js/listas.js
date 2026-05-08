//CREAR LISTA
document.addEventListener('DOMContentLoaded', () => {

    const formCrearLista = document.getElementById('formCrearLista');
    if (formCrearLista) {

        formCrearLista.addEventListener('submit', async (e) => {

            e.preventDefault();

            const formData = new FormData(formCrearLista);

            formData.append('action', 'crear');

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

                // refresca para mostrar nueva lista
                location.reload();

            } catch (err) {
                console.error('Error creando lista:', err);
            }
        });
    }
});

//EDITAR LISTA
async function editarLista(
    id_lista,
    nombreActual,
    descripcionActual,
    visibilidadActual
) {

    const nuevoNombre = prompt(
        'Nuevo nombre de la lista:',
        nombreActual
    );

    if (nuevoNombre === null) return;


    const nuevaDescripcion = prompt(
        'Nueva descripción:',
        descripcionActual
    );

    if (nuevaDescripcion === null) return;


    const nuevaVisibilidad = prompt(
        'Visibilidad: publica o privada',
        visibilidadActual
    );

    if (nuevaVisibilidad === null) return;


    const formData = new FormData();

    formData.append('action', 'editar');
    formData.append('id_lista', id_lista);

    formData.append('nombre_lista', nuevoNombre);
    formData.append('descripcion', nuevaDescripcion);
    formData.append('visibilidad', nuevaVisibilidad);


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

        // actualizar contenido
        location.reload();

    } catch (err) {
        console.error('Error editando lista:', err);
    }
}

//ELIMINAR LISTA
async function eliminarLista(id_lista) {

    const confirmar = confirm(
        '¿Seguro que quieres eliminar esta lista?'
    );

    if (!confirmar) return;


    const formData = new FormData();

    formData.append('action', 'eliminar');
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

        // refresca vista
        location.reload();

    } catch (err) {
        console.error('Error eliminando lista:', err);
    }
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

//TOGGLE DETALLES DE LISTA
let dropdownOpen = false;

async function toggleListas(id_produccion) {

    const dropdown = document.getElementById('listasDropdown');

    if (dropdownOpen) {
        dropdown.style.display = 'none';
        dropdownOpen = false;
        document.removeEventListener('click', closeDropdownOnOutsideClick);
        return;
    }

    const res = await fetch(`get_listas.php?id_produccion=${id_produccion}`);
    const listas = await res.json();

    dropdown.innerHTML = '';

    listas.forEach(lista => {

        const item = document.createElement('div');
        item.classList.add('lista-item');

        item.innerHTML = `
            ${lista.nombre_lista}
            ${lista.contiene ? '✔' : ''}
        `;

        if (lista.contiene) {
            item.style.opacity = '0.6';
        }

        item.onclick = (e) => {
            e.stopPropagation();
            addToList(id_produccion, lista.id_lista);
        };

        dropdown.appendChild(item);
    });

    dropdown.style.display = 'block';
    dropdownOpen = true;

    setTimeout(() => {
        document.addEventListener('click', closeDropdownOnOutsideClick);
    }, 0);

    dropdown.dataset.produccion = id_produccion;
}

function mostrarToast(mensaje) {
    const toast = document.getElementById('toast');
    toast.textContent = mensaje;
    toast.classList.add('show');

    setTimeout(() => {
        toast.classList.remove('show');
    }, 2000);
}