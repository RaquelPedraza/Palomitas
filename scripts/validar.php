<?php
// Archivo de Procesamiento: VALIDACIÓN EN  SERVIDOR
// Indicamos que la resultado será JSON 
header('Content-Type: application/json');

// Recibimos los datos por POST 
$nombre = $_POST['nombre'] ?? '';
$email = $_POST['email'] ?? '';

// Estructura de resultado inicial 
$resultado = [
    "ok" => false,
    "mensaje" => ""
];

// Validación en servidor (PHP) 
if ($nombre == "" || $email == "") {
    $resultado["mensaje"] = "Error: Faltan datos en el servidor.";
} 
// 5. Comprobar si 'email' contiene '@' 
elseif (!str_contains($email, '@')) { 
    $resultado["mensaje"] = "Error: El email debe contener una @.";
} 
else { // 4. Devolver JSON con: ok (true/false) y mensaje
    $resultado["ok"] = true;
    $resultado["mensaje"] = "¡Éxito! Datos validados correctamente por el servidor.";
}

// Convertimos el array PHP a texto JSON y lo enviamos 
echo json_encode($resultado);
?>