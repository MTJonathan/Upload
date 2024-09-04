<?php
$carpetaNombre = isset($_GET['nombre']) ? $_GET['nombre'] : '';
$carpetaRuta = "./descarga/" . $carpetaNombre;

$files = [];
if (file_exists($carpetaRuta)) {
    $files = array_diff(scandir($carpetaRuta), array('.', '..'));
}

echo json_encode([
    'files' => array_values($files),
    'carpetaRuta' => $carpetaRuta
]);