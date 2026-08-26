<?php
include 'conexion.php';
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['rol'] !== 'estudiante') {
    header("Location: login.php");
    exit();
}

if (!isset($_SESSION['aula_id'])) {
    header("Location: aulas.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Aula Virtual - Chat Anónimo</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <style>
        body { background-color: #f0f2f5; }
        .chat-room { height: 60vh; overflow-y: auto; background: white; border-radius: 8px; padding: 15px; scroll-behavior: smooth; }
        .msg-box { margin-bottom: 12px; }
        .msg-anon { background-color: #e9ecef; border-radius: 15px; padding: 8px 14px; display: inline-block; max-width: 75%; text-align: left; }
        .msg-me { background-color: #0d6efd; color: white; border-radius: 15px; padding: 8px 14px; display: inline-block; max-width: 75%; text-align: left; }
    </style>
</head>
<body>
    <div class="container py-4">
        <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
            <div>
                <h4 class="fw-bold mb-0">
                    <i class="bi bi-door-open-fill text-primary"></i> 
                    Aula: <?php echo htmlspecialchars($_SESSION['aula_nombre'] ?? 'General'); ?>
                </h4>
                <span class="badge bg-success mt-1">
                    Registrado como: <?php echo htmlspecialchars($_SESSION['nombre']); ?> (Identidad Anónima)
                </span>
            </div>
            <div>
                <a href="aulas.php" class="btn btn-outline-secondary btn-sm me-2">
                    <i class="bi bi-arrow-left"></i> Cambiar Aula
                </a>
                <a href="login.php" class="btn btn-outline-danger btn-sm">Cerrar Sesión</a>
            </div>
        </div>

        <div class="row">
            <div class="col-md-3 mb-3">
                <div class="card bg-light border-0 shadow-sm">
                    <div class="card-body">
                        <h6 class="fw-bold"><i class="bi bi-shield-check"></i> Código de Conducta</h6>
                        <p class="text-muted small">Este espacio promueve la participación inclusiva y el debate respetuoso. Se sanciona el cyberbullying.</p>
                    </div>
                </div>
            </div>

            <div class="col-md-9">
                <!-- Caja donde van los mensajes -->
                <div class="chat-room shadow-sm border mb-3 p-3" id="sala-chat">
                    <div class="messages" id="caja-mensajes">
                        <p class="text-center text-muted my-4">Cargando mensajes...</p>
                    </div>
                </div>

                <!-- Formulario AJAX -->
                <form id="form-chat" class="input-group">
                    <input type="text" id="input-mensaje" class="form-control" placeholder="Escribí una duda o comentario..." required autocomplete="off">
                    <button class="btn btn-primary" type="submit"><i class="bi bi-send"></i> Enviar</button>
                </form>
            </div>
        </div>
    </div>

    <script>
        const cajaMensajes = document.getElementById('caja-mensajes');
        const salaChat = document.getElementById('sala-chat');
        const formChat = document.getElementById('form-chat');
        const inputMensaje = document.getElementById('input-mensaje');
        
        let cantidadMensajes = 0;

        // Función que trae los mensajes (SIN CACHÉ)
        function actualizarChat() {
            // Le agregamos la hora actual al final (?t=...) para que el navegador nunca use caché
            fetch('cargar_mensajes.php?t=' + Date.now())
                .then(response => response.text())
                .then(html => {
                    cajaMensajes.innerHTML = html;
                    
                    let cantidadActual = cajaMensajes.querySelectorAll('.msg-box').length;
                    if (cantidadActual > cantidadMensajes) {
                        salaChat.scrollTop = salaChat.scrollHeight;
                        cantidadMensajes = cantidadActual;
                    }
                })
                .catch(error => console.error("Error al cargar mensajes:", error));
        }

        // Interceptamos el botón Enviar
        formChat.addEventListener('submit', function(e) {
            e.preventDefault(); 
            
            let textoMensaje = inputMensaje.value.trim();
            if (textoMensaje === "") return; // Evita mandar mensajes vacíos
            
            let formData = new FormData();
            formData.append('mensaje', textoMensaje);

            // Borramos el input inmediatamente para dar sensación de rapidez
            inputMensaje.value = ''; 

            fetch('enviar_mensaje.php', {
                method: 'POST',
                body: formData
            }).then(() => {
                actualizarChat(); // Forzamos actualización apenas se envía
            }).catch(error => console.error("Error al enviar:", error));
        });

        // Actualizamos cada 2 segundos
        setInterval(actualizarChat, 2000);
        
        // Carga inicial
        actualizarChat();
    </script>
</body>
</html>