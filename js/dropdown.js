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

    /* CREAR NUEVA LISTA */
    const crearItem = document.createElement('div');
    
    crearItem.classList.add(
        'lista-item',
        'crear-lista-dropdown'
    );

    crearItem.innerHTML = `
        <i class="fa-solid fa-plus"></i>
        Crear nueva lista
    `;

    crearItem.onclick = (e) => {
        e.stopPropagation();
        abrirModal('modalCrearListas');
        cerrarDropdown();
    };

    dropdown.appendChild(crearItem);

    /* RENDERIZAR LISTAS */ 
    listas.forEach(lista => {

        const item = document.createElement('div');
        item.classList.add('lista-item');

        item.innerHTML = `
            ${lista.nombre_lista}
            ${lista.contiene ? '<i class="fa-solid fa-check"></i>' : ''}
        `;

        if (lista.contiene) {
            item.classList.add('added')
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

/* FUNCIÓN PARA MOSTRAR TOASTS DE CONFIRMACION */
function mostrarToast(mensaje) {
    const toast = document.getElementById('toast');
    toast.textContent = mensaje;
    toast.classList.add('show');

    setTimeout(() => {
        toast.classList.remove('show');
    }, 2000);
}

/* FUNCION PARA SALIR DEL DROPDOWN */
function closeDropdownOnOutsideClick(e) {
    const dropdown = document.getElementById('listasDropdown');
    const contenedor = document.querySelector('.contenedor-listas');

    if (!contenedor.contains(e.target)) {
        dropdown.style.display = 'none';
        dropdownOpen = false;
        document.removeEventListener('click', closeDropdownOnOutsideClick);
    }
}