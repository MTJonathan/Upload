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

                    // Local file header
                    fwrite($zip, "\x50\x4b\x03\x04");
                    fwrite($zip, pack('v', 10)); // Version needed to extract
                    fwrite($zip, pack('v', 0)); // General purpose bit flag
                    fwrite($zip, pack('v', 0)); // Compression method
                    fwrite($zip, pack('v', 0)); // Last mod file time
                    fwrite($zip, pack('v', 0)); // Last mod file date
                    fwrite($zip, pack('V', crc32($content))); // CRC32
                    fwrite($zip, pack('V', strlen($content))); // Compressed size
                    fwrite($zip, pack('V', strlen($content))); // Uncompressed size
                    fwrite($zip, pack('v', strlen($zipfile))); // File name length
                    fwrite($zip, pack('v', 0)); // Extra field length
                    fwrite($zip, $zipfile); // File name
                    
                    // File data
                    fwrite($zip, $content);

                    // Data descriptor
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
                $central_dir .= pack('v', 10); // Version made by
                $central_dir .= pack('v', 10); // Version needed to extract
                $central_dir .= pack('v', 0); // General purpose bit flag
                $central_dir .= pack('v', 0); // Compression method
                $central_dir .= pack('v', 0); // Last mod file time
                $central_dir .= pack('v', 0); // Last mod file date
                $central_dir .= pack('V', crc32($content)); // CRC32
                $central_dir .= pack('V', strlen($content)); // Compressed size
                $central_dir .= pack('V', strlen($content)); // Uncompressed size
                $central_dir .= pack('v', strlen($entry)); // File name length
                $central_dir .= pack('v', 0); // Extra field length
                $central_dir .= pack('v', 0); // File comment length
                $central_dir .= pack('v', 0); // Disk number start
                $central_dir .= pack('v', 0); // Internal file attributes
                $central_dir .= pack('V', 32); // External file attributes
                $central_dir .= pack('V', 0); // Relative offset of local header
                $central_dir .= $entry;
                $entries++;
            }
        }
    }
    closedir($handle);
}

fwrite($zip, $central_dir);

// End of central directory record
fwrite($zip, "\x50\x4b\x05\x06");
fwrite($zip, pack('v', 0)); // Number of this disk
fwrite($zip, pack('v', 0)); // Disk where central directory starts
fwrite($zip, pack('v', $entries)); // Number of central directory records on this disk
fwrite($zip, pack('v', $entries)); // Total number of central directory records
fwrite($zip, pack('V', strlen($central_dir))); // Size of central directory
fwrite($zip, pack('V', $central_dir_start)); // Offset of start of central directory, relative to start of archive
fwrite($zip, pack('v', 0)); // Comment length

fclose($zip);

// Envía el archivo al navegador
header("Content-Type: application/zip");
header("Content-Disposition: attachment; filename=$zipname");
header("Content-Length: " . filesize($temp_file));
readfile($temp_file);

// Elimina el archivo temporal
unlink($temp_file);
?>