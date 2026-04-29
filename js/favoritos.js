async function toggleFavorito(btn, id) {
    const formData = new FormData();
    formData.append('id', id);

    try {
        const res = await fetch('favoritos.php', {
            method: 'POST',
            body: formData
        });

        const data = await res.json();

        if (!data.ok) {
            console.log('Error:', data.error);
            return;
        }

        const icon = btn.querySelector('i');

        //ANIMACIÓN
        icon.classList.remove('pop');
        void icon.offsetWidth;
        icon.classList.add('pop');

        // ESTADO VISUAL
        if (data.estado === 'añadido') {
            icon.classList.remove('fa-regular');
            icon.classList.add('fa-solid');
        } else {
            icon.classList.remove('fa-solid');
            icon.classList.add('fa-regular');
        }

    } catch (err) {
        console.error('Error en favoritos AJAX:', err);
    }
}