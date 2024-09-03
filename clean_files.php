<?php
$carpetaNombre = isset($_GET['nombre']) ? $_GET['nombre'] : '';
$carpetaRuta = "./descarga/" . $carpetaNombre;

// Verifica si la carpeta existe
if (!file_exists($carpetaRuta)) {
    echo json_encode(['success' => false, 'message' => "La carpeta no existe."]);
    exit;
}

// Función para eliminar archivos y carpetas recursivamente
function deleteFiles($dir) {
    if (is_dir($dir)) {
        $objects = scandir($dir);
        foreach ($objects as $object) {
            if ($object != "." && $object != "..") {
                if (is_dir($dir. DIRECTORY_SEPARATOR .$object) && !is_link($dir."/".$object))
                    deleteFiles($dir. DIRECTORY_SEPARATOR .$object);
                else
                    unlink($dir. DIRECTORY_SEPARATOR .$object);
            }
        }
        rmdir($dir);
    }
}

// Elimina todos los archivos y subcarpetas
deleteFiles($carpetaRuta);

// Vuelve a crear la carpeta vacía
mkdir($carpetaRuta, 0755, true);

echo json_encode(['success' => true, 'message' => "Todos los archivos han sido eliminados."]);
