// Función para notificar a otras pestañas sobre cambios
function notifyTabsOfChange() {
    localStorage.setItem('lastChange', Date.now().toString());
}

// Función para actualizar la lista de archivos
function updateFileList() {
    fetch('get_file_list.php?nombre=' + carpetaNombre)
        .then(response => response.json())
        .then(data => {
            const fileListElement = document.getElementById('file-list');
            fileListElement.innerHTML = ''; // Limpiar la lista actual

            if (data.files.length > 0) {
                fileListElement.innerHTML = "<h3 style='margin-bottom:10px;'>Archivos Subidos:</h3>";
                data.files.forEach(file => {
                    const fileElement = document.createElement('div');
                    fileElement.className = 'archivos_subidos';
                    fileElement.innerHTML = `
                        <div><a href='${data.carpetaRuta}/${file}' download class='boton-descargar'>${file}</a></div>
                        <div>
                            <form action='' method='POST' style='display:inline;'>
                                <input type='hidden' name='eliminarArchivo' value='${file}'>
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
                    `;
                    fileListElement.appendChild(fileElement);
                });

                const downloadZipContainer = document.createElement('div');
                downloadZipContainer.className = 'download-zip-container';
                downloadZipContainer.innerHTML = `
                    <a href='download_zip.php?nombre=${carpetaNombre}' class='download-zip-btn'>Descargar todos como ZIP</a>
                    <button onclick='cleanAllFiles()' class='action-btn clean-all-btn'>Borrar todos los archivos</button>
                `;
                fileListElement.appendChild(downloadZipContainer);
            } else {
                fileListElement.textContent = "No se han subido archivos.";
            }
        })
        .catch(error => {
            console.error('Error al actualizar la lista de archivos:', error);
        });
}

// Escuchar cambios en el almacenamiento local
window.addEventListener('storage', function(e) {
    if (e.key === 'lastChange') {
        updateFileList();
    }
});

// Modificar las funciones existentes para notificar cambios

// Función para subir un archivo
function uploadFile(file) {
    const xhr = new XMLHttpRequest();
    const formData = new FormData();

    formData.append('archivo', file);

    xhr.open('POST', 'subir.php?nombre=' + carpetaNombre, true);

    xhr.upload.onprogress = function(e) {
        if (e.lengthComputable) {
            const percentComplete = (e.loaded / e.total) * 100;
            updateProgress(file.name, percentComplete);
        }
    };

    xhr.onload = function() {
        if (xhr.status === 200) {
            console.log(`${file.name} subido con éxito`);
            updateFileList();
            notifyTabsOfChange(); // Notificar a otras pestañas
        } else {
            console.error(`Error al subir ${file.name}`);
        }
    };

    xhr.send(formData);
}

// Función para limpiar todos los archivos
function cleanAllFiles() {
    if (confirm('¿Estás seguro de que quieres eliminar todos los archivos?')) {
        fetch('clean_files.php?nombre=' + carpetaNombre, {
            method: 'POST'
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert(data.message);
                updateFileList();
                notifyTabsOfChange(); // Notificar a otras pestañas
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

// Inicializar la lista de archivos al cargar la página
document.addEventListener('DOMContentLoaded', function() {
    updateFileList();
});