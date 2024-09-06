<?php
$carpetaNombre = isset($_GET['nombre']) ? preg_replace('/[^a-zA-Z0-9_-]/', '', $_GET['nombre']) : '';
$carpetaRuta = "./descarga/" . $carpetaNombre;

try {
    if (!$carpetaNombre) {
        throw new Exception("Nombre de carpeta no especificado o inválido.");
    }

    if (!file_exists($carpetaRuta)) {
        if (!mkdir($carpetaRuta, 0755, true)) {
            throw new Exception("Error al crear la carpeta '$carpetaNombre'.");
        }
        $mensaje = "Carpeta '$carpetaNombre' creada con éxito.";
    } else {
        $mensaje = "La carpeta '$carpetaNombre' ya existe.";
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (isset($_FILES['archivo'])) {
            $archivo = $_FILES['archivo'];
            $archivoNombre = basename($archivo['name']);
            $archivoRuta = $carpetaRuta . '/' . $archivoNombre;

            if (!move_uploaded_file($archivo['tmp_name'], $archivoRuta)) {
                throw new Exception("Error al subir el archivo.");
            }
            $mensaje = "Archivo subido con éxito.";
        }

        if (isset($_POST['eliminarArchivo'])) {
            $archivoAEliminar = basename($_POST['eliminarArchivo']);
            $archivoRutaAEliminar = $carpetaRuta . '/' . $archivoAEliminar;

            if (!file_exists($archivoRutaAEliminar)) {
                throw new Exception("El archivo '$archivoAEliminar' no existe.");
            }

            if (!unlink($archivoRutaAEliminar)) {
                throw new Exception("Error al eliminar el archivo.");
            }
            $mensaje = "Archivo '$archivoAEliminar' eliminado con éxito.";
        }
    }
} catch (Exception $e) {
    $mensaje = "Error: " . htmlspecialchars($e->getMessage());
}

// Rest of the HTML remains the same
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Compartir archivos</title>
    <link rel="stylesheet" href="estilo.css">
    <link rel="stylesheet" href="responsive.css">
    <link rel="manifest" href="manifest.json">
</head>

<body>
    <h1>Compartir archivos <sup class="beta">BETA</sup></h1>
    <div class="content">
        <h3>Sube tus archivos y comparte este enlace temporal: <a
                href="http://senatinomt.online/?nombre=<?php echo $carpetaNombre; ?>"
                target="_blank">senatinomt.online/?nombre=<?php echo $carpetaNombre; ?></a>
        </h3>
        <div class="container">
            <div class="drop-area" id="drop-area">
                <form action="" id="form" method="POST" enctype="multipart/form-data">
                    <svg xmlns="http://www.w3.org/2000/svg" width="64" height="64" viewBox="0 0 24 24"
                        style="fill:#0730c5;transform: ;msFilter:;">
                        <path d="M13 19v-4h3l-4-5-4 5h3v4z"></path>
                        <path
                            d="M7 19h2v-2H7c-1.654 0-3-1.346-3-3 0-1.404 1.199-2.756 2.673-3.015l.581-.102.192-.558C8.149 8.274 9.895 7 12 7c2.757 0 5 2.243 5 5v1h1c1.103 0 2 .897 2 2s-.897 2-2 2h-3v2h3c2.206 0 4-1.794 4-4a4.01 4.01 0 0 0-3.056-3.888C18.507 7.67 15.56 5 12 5 9.244 5 6.85 6.611 5.757 9.15 3.609 9.792 2 11.82 2 14c0 2.757 2.243 5 5 5z">
                        </path>
                    </svg> <br>
                    <input type="file" class="file-input" name="archivo" id="archivo"
                        onchange="document.getElementById('form').submit()" multiple>
                    <p class="drop-text default-text">Arrastra tus archivos aquí<br>o<br><b>Abre el explorador</b></p>
                    <p class="drop-text drag-text" style="display: none;"><b>Suelta tu archivo</b></p>

                </form>
                <div id="progress-container" style="display: none; width: 100%; margin-top: 20px;">
                    <progress id="progress-bar" value="0" max="100" style="width: 100%;"></progress>
                    <p id="progress-status"></p>
                </div>

            </div>

            <div class="container2">


                <div id="file-list" class="pila">
                    <?php
                    $targetDir = $carpetaRuta;

                    $files = scandir($targetDir);
                    $files = array_diff($files, array('.', '..'));

                    if (count($files) > 0) {
                        echo " <h3 style='margin-bottom:10px;'>Archivos Subidos:</h3>";

                        foreach ($files as $file) {
                            echo "<div class='archivos_subidos'>
                            <div><a href='$carpetaRuta/$file' download class='boton-descargar'>$file</a></div>
                            <div>
                            <form action='' method='POST' style='display:inline;'>
                                <input type='hidden' name='eliminarArchivo' value='$file'>
                                <button type='submit' class='btn_delete'>
                                    <svg xmlns='http://www.w3.org/2000/svg' class='icon icon-tabler icon-tabler-trash' width='24' height='24' viewBox='0 0 24 24' stroke-width='2' stroke='currentColor' fill='none' stroke-linecap='round' stroke-linejoin='round'>
                                        <path stroke='none' d='M0 0h24v24H0z' fill='none'/>
                                        <path d='M4 7l16 0' />
                                        <path d='M10 11l0 6' />
                                        <path d='M14 11l0 6' />
                                        <path d='M5 7l1 12a2 2 0 0 0 2 2h8a2 2 0 0 0 2 -2l1 -12' />
                                        <path d='M9 7v-3a1 1 0 0 1 1 -1h4a1 1 0 0 1 1 1v3' />
                                    </svg>
                                </button>
                            </form>
                        </div>
                        </div>";
                        }
                        echo "<div class='download-zip-container'>";
                        echo "<a href='download_zip.php?nombre=$carpetaNombre' class='download-zip-btn'>Descargar todos como ZIP</a>";
                        echo "<button onclick='cleanAllFiles()' class='action-btn clean-all-btn'>Borrar todos los archivos</button>";
                        echo "</div>";
                    } else {
                        echo "No se han subido archivos.";
                    }
                    ?>
                </div>
            </div>
        </div>
    </div>


    <script src="parametro.js"></script>
    <script src="sync-tabs.js"></script>
    <script>
        document.getElementById('archivo').addEventListener('change', function (e) {
            var files = e.target.files; // Manejar múltiples archivos
            uploadFiles(files); // Llama a la función para manejar varios archivos
        });

        function uploadFiles(files) {
            const totalFiles = files.length;
            let uploadedFiles = 0;
            let totalBytes = 0;
            let loadedBytes = 0;

            // Calcular el tamaño total de los archivos
            for (let i = 0; i < totalFiles; i++) {
                totalBytes += files[i].size;
            }

            // Mostrar el contenedor de progreso
            document.getElementById('progress-container').style.display = 'block';

            for (let i = 0; i < totalFiles; i++) {
                uploadSingleFile(files[i], function (e) {
                    // Actualizar el progreso total de los archivos
                    loadedBytes += e.loaded;
                    var percentComplete = (loadedBytes / totalBytes) * 100;
                    document.getElementById('progress-bar').value = percentComplete;
                    document.getElementById('progress-status').textContent = Math.round(percentComplete) + '% subido';

                    // Si todos los archivos se han subido, recarga la página
                    if (i === totalFiles - 1 && e.loaded === e.total) {
                        document.getElementById('progress-status').textContent = 'Todos los archivos subidos con éxito';
                        location.reload(); // Recargar la página para mostrar los nuevos archivos
                    }
                });
            }
        }

        function uploadSingleFile(file, onProgressCallback) {
            var xhr = new XMLHttpRequest();
            var formData = new FormData();
            formData.append('archivo', file);

            xhr.open('POST', 'subir.php?nombre=<?php echo $carpetaNombre; ?>', true);

            xhr.upload.onprogress = onProgressCallback;

            xhr.onload = function () {
                if (xhr.status === 200) {
                    // Subida exitosa
                    console.log('Archivo subido con éxito:', file.name);
                } else {
                    document.getElementById('progress-status').textContent = 'Error al subir el archivo: ' + file.name;
                }
            };

            xhr.send(formData);
        }

        function cleanAllFiles() {
            if (confirm('¿Estás seguro de que quieres eliminar todos los archivos?')) {
                fetch('clean_files.php?nombre=<?php echo $carpetaNombre; ?>', {
                    method: 'POST'
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            alert(data.message);
                            location.reload(); // Recarga la página para actualizar la lista de archivos
                        } else {
                            alert('Error: ' + data.message);
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('Ocurrió un error al intentar limpiar los archivos.');
                    });
            }
        }
    </script>


</body>

</html>