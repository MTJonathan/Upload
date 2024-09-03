<?php
// Nombre de la carpeta a crear (obtenido del parámetro)
$carpetaNombre = $_GET['nombre'];

// Ruta donde deseas crear la carpeta (por ejemplo, en la carpeta 'descarga')
$carpetaRuta = "./descarga/" . $carpetaNombre;

// Verifica si la carpeta ya existe antes de crearla
if (!file_exists($carpetaRuta)) {
    // Crea la carpeta con permisos adecuados (por ejemplo, 0755)
    mkdir($carpetaRuta, 0755, true);
    $mensaje = "Carpeta '$carpetaNombre' creada con éxito.";
} else {
    $mensaje = "La carpeta '$carpetaNombre' ya existe.";
}

// Procesa el archivo subido
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_FILES['archivo'])) {
        $archivo = $_FILES['archivo'];

        if (move_uploaded_file($archivo['tmp_name'], $carpetaRuta . '/' . $archivo['name'])) {
            echo json_encode(['success' => true, 'message' => "Archivo subido con éxito."]);
        } else {
            echo json_encode(['success' => false, 'message' => "Error al subir el archivo."]);
        }
    } else {
        echo json_encode(['success' => false, 'message' => "No se recibió ningún archivo."]);
    }
} else {
    echo json_encode(['success' => false, 'message' => "Método de solicitud no válido."]);
}
