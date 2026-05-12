async function toggleSeguimiento(id_usuario) {
  const formData = new FormData();
  formData.append("id", id_usuario);

  try {
    const res = await fetch("seguir.php", {
      method: "POST",
      body: formData,
    });

    const data = await res.json();

    if (data.ok) {
      // Si ha ido bien, recargamos la página para actualizar los números
      location.reload();
    } else {
      console.error("Error al seguir:", data.error);
    }
  } catch (err) {
    console.error("Error de conexión AJAX:", err);
  }
}
