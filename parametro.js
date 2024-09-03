
// Obtén la URL actual
const urlActual = window.location.href;

// Verifica si el parámetro 'nombre' ya está presente en la URL
var parametros = new URLSearchParams(window.location.search);
var carpetaNombre = parametros.get("nombre");

if (!carpetaNombre) {
    // Si 'nombre' no está presente, genera un número aleatorio
    carpetaNombre = generarCadenaAleatoria();
    // Agrega el parámetro 'nombre' a la URL
    const urlConParametro = urlActual.includes("?") ? `${urlActual}&nombre=${carpetaNombre}` : `${urlActual}?nombre=${carpetaNombre}`;
    // Redirige a la nueva URL con el parámetro 'nombre'
    window.location.href = urlConParametro;
} else {
    // Extrae el valor del parámetro de la URL
    const parametros = new URLSearchParams(window.location.search);
    const carpetaNombre = parametros.get("nombre");

    // Llama a la función para crear la carpeta con el nombre obtenido
    function crearCarpeta(carpetaNombre) {
    $.ajax({
        url: 'crearCarpeta.php', // Ruta del archivo PHP que crea la carpeta
        type: 'POST', // Puedes usar POST o GET según tus necesidades
        data: { nombreCarpeta: carpetaNombre }, // Envía el nombre de la carpeta como datos
        success: function(response) {
            console.log('Carpeta creada.'); // Mensaje de éxito (puedes personalizarlo)
        },
        error: function() {
            console.log('Error al crear la carpeta.'); // Mensaje de error (puedes personalizarlo)
        }
    });
}
    
}

// Función para generar un número aleatorio de 3 dígitos
function generarCadenaAleatoria() {
    const caracteres = 'abcdefghijklmnopqrstuvwxyz0123456789';
    let cadenaAleatoria = '';
    for (let i = 0; i < 3; i++) {
        const caracterAleatorio = caracteres.charAt(Math.floor(Math.random() * caracteres.length));
        cadenaAleatoria += caracterAleatorio;
    }
    return cadenaAleatoria;
}


//DROP AREA

// Obtén la zona de arrastre y el formulario
const dropArea = document.getElementById('drop-area');
const Form = document.getElementById('form');

// Agrega los siguientes eventos a la zona de arrastre
dropArea.addEventListener('dragover', (e) => {
    e.preventDefault();
    dropArea.classList.add('drag-over');
});

dropArea.addEventListener('dragleave', () => {
    dropArea.classList.remove('drag-over');
});

dropArea.addEventListener('drop', (e) => {
    e.preventDefault();
    dropArea.classList.remove('drag-over');
    const file = e.dataTransfer.files[0];
    handleFile(file);
});

// Función para manejar el archivo seleccionado
function handleFile(file) {
    if (file) {
        // Realiza alguna acción, como mostrar el nombre del archivo
        console.log('Archivo seleccionado:', file.name);

        // También puedes realizar otras acciones, como subir el archivo al servidor
        // Puedes agregar aquí el código para subir el archivo si lo deseas
    }
}

// Agrega esta función para manejar el evento de envío del formulario
Form.addEventListener('submit', (e) => {
    e.preventDefault();
    const fileInput = Form.querySelector('#archivo');
    const file = fileInput.files[0];
    if (file) {
        // Puedes enviar el archivo al servidor para su procesamiento aquí
        console.log('Subir archivo:', file.name);
    } else {
        alert('Por favor, seleccione un archivo primero.');
    }
});

// Obtén la zona de arrastre y el formulario
const form = document.getElementById('form');
const fileInput = document.getElementById('archivo');

// Eventos para la zona de arrastre
dropArea.addEventListener('dragover', (e) => {
    e.preventDefault();
    dropArea.classList.add('drag-over');
});

dropArea.addEventListener('dragleave', () => {
    dropArea.classList.remove('drag-over');
});

dropArea.addEventListener('drop', (e) => {
    e.preventDefault();
    dropArea.classList.remove('drag-over');
    handleFiles(e.dataTransfer.files);
});

// Manejar la selección de archivos mediante el input
fileInput.addEventListener('change', (e) => {
    handleFiles(e.target.files);
});

// Función para manejar múltiples archivos
function handleFiles(files) {
    for (let i = 0; i < files.length; i++) {
        uploadFile(files[i]);
    }
}

// Función para subir un archivo
function uploadFile(file) {
    const xhr = new XMLHttpRequest();
    const formData = new FormData();

    formData.append('archivo', file);

    xhr.open('POST', 'subir.php?nombre=<?php echo $carpetaNombre; ?>', true);

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
        } else {
            console.error(`Error al subir ${file.name}`);
        }
    };

    xhr.send(formData);
}

// Función para actualizar la barra de progreso
function updateProgress(fileName, percent) {
    // Aquí puedes implementar la lógica para mostrar el progreso de cada archivo
    console.log(`${fileName}: ${percent.toFixed(2)}% subido`);
}

// Función para actualizar la lista de archivos después de la subida
function updateFileList() {
    // Aquí puedes implementar la lógica para actualizar la lista de archivos
    // Por ejemplo, hacer una petición AJAX para obtener la lista actualizada
    location.reload(); // Por ahora, simplemente recargamos la página
}

// Prevenir el envío del formulario por defecto
form.addEventListener('submit', (e) => {
    e.preventDefault();
    handleFiles(fileInput.files);
});
