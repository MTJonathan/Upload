<?php
$carpetaNombre = isset($_GET['nombre']) ? $_GET['nombre'] : '';
$carpetaRuta = "./descarga/" . $carpetaNombre;

// Verifica si la carpeta existe
if (!file_exists($carpetaRuta)) {
    die("La carpeta no existe.");
}

$zipname = $carpetaNombre . '.zip';

// Crea un archivo temporal
$temp_file = tempnam(sys_get_temp_dir(), 'zip');
$zip = fopen($temp_file, 'w');

// Función recursiva para añadir archivos al ZIP
function addFilesToZip($zip, $dir, $zipdir = '') {
    if ($handle = opendir($dir)) {
        while (false !== ($entry = readdir($handle))) {
            if ($entry != "." && $entry != "..") {
                $file = $dir . '/' . $entry;
                $zipfile = $zipdir . ($zipdir ? '/' : '') . $entry;
                if (is_dir($file)) {
                    addFilesToZip($zip, $file, $zipfile);
                } else {
                    $content = file_get_contents($file);
                    $zip_content_start = ftell($zip);

                    fwrite($zip, "\x50\x4b\x03\x04");
                    fwrite($zip, pack('v', 10)); 
                    fwrite($zip, pack('v', 0)); 
                    fwrite($zip, pack('v', 0)); 
                    fwrite($zip, pack('v', 0)); 
                    fwrite($zip, pack('v', 0)); 
                    fwrite($zip, pack('V', crc32($content))); 
                    fwrite($zip, pack('V', strlen($content))); 
                    fwrite($zip, pack('V', strlen($content))); 
                    fwrite($zip, pack('v', strlen($zipfile))); 
                    fwrite($zip, pack('v', 0)); 
                    fwrite($zip, $zipfile); 
                 
                    fwrite($zip, $content);

                    fwrite($zip, pack('V', crc32($content)));
                    fwrite($zip, pack('V', strlen($content)));
                    fwrite($zip, pack('V', strlen($content)));
                }
            }
        }
        closedir($handle);
    }
}

// Añade los archivos al ZIP
addFilesToZip($zip, $carpetaRuta);

// Central directory
$central_dir_start = ftell($zip);
$central_dir = '';
$entries = 0;

if ($handle = opendir($carpetaRuta)) {
    while (false !== ($entry = readdir($handle))) {
        if ($entry != "." && $entry != "..") {
            $file = $carpetaRuta . '/' . $entry;
            if (!is_dir($file)) {
                $content = file_get_contents($file);
                $central_dir .= "\x50\x4b\x01\x02";
                $central_dir .= pack('v', 10); 
                $central_dir .= pack('v', 10); 
                $central_dir .= pack('v', 0); 
                $central_dir .= pack('v', 0); 
                $central_dir .= pack('v', 0); 
                $central_dir .= pack('v', 0); 
                $central_dir .= pack('V', crc32($content)); 
                $central_dir .= pack('V', strlen($content)); 
                $central_dir .= pack('V', strlen($content)); 
                $central_dir .= pack('v', strlen($entry)); 
                $central_dir .= pack('v', 0); 
                $central_dir .= pack('v', 0); 
                $central_dir .= pack('v', 0); 
                $central_dir .= pack('v', 0); 
                $central_dir .= pack('V', 32); 
                $central_dir .= pack('V', 0); 
                $central_dir .= $entry;
                $entries++;
            }
        }
    }
    closedir($handle);
}

fwrite($zip, $central_dir);

fwrite($zip, "\x50\x4b\x05\x06");
fwrite($zip, pack('v', 0)); 
fwrite($zip, pack('v', 0)); 
fwrite($zip, pack('v', $entries)); 
fwrite($zip, pack('v', $entries)); 
fwrite($zip, pack('V', strlen($central_dir))); 
fwrite($zip, pack('V', $central_dir_start));
fwrite($zip, pack('v', 0)); 

fclose($zip);

// Envía el archivo al navegador
header("Content-Type: application/zip");
header("Content-Disposition: attachment; filename=$zipname");
header("Content-Length: " . filesize($temp_file));
readfile($temp_file);

// Elimina el archivo temporal
unlink($temp_file);
?>