<?php
include 'conexion.php';
session_start();

$mensaje = "";
$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nombre = trim($_POST['nombre']);
    $mail = trim($_POST['mail']);
    $telefono = trim($_POST['telefono']);
    $contrasena = $_POST['contrasena'];

    // 1. Verificamos que el correo no esté ya registrado en Usuario ni en Moderador
    $stmt_check = $conn->prepare("SELECT Mail FROM Usuario WHERE Mail = ? UNION SELECT Mail FROM Moderador WHERE Mail = ?");
    $stmt_check->bind_param("ss", $mail, $mail);
    $stmt_check->execute();
    $res_check = $stmt_check->get_result();

    if ($res_check->num_rows > 0) {
        $error = "Este correo ya está registrado en el sistema.";
    } else {
        // 2. Encriptamos la contraseña de manera segura
        $hash_contrasena = password_hash($contrasena, PASSWORD_DEFAULT);

        // 3. Insertamos al nuevo estudiante en la base de datos
        $stmt = $conn->prepare("INSERT INTO Usuario (Nombre_y_Apellido, Mail, Telefono, Contrasena) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $nombre, $mail, $telefono, $hash_contrasena);
        
        if ($stmt->execute()) {
            $mensaje = "¡Registro exitoso! Ya podés iniciar sesión con tus datos.";
        } else {
            $error = "Ocurrió un error al registrar: " . $conn->error;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro de Estudiante</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; }
        .register-container { max-width: 450px; margin-top: 5%; }
    </style>
</head>
<body>
    <div class="container register-container">
        <div class="card shadow p-4">
            <div class="text-center mb-4">
                <h3 class="text-success fw-bold">Crear Cuenta</h3>
                <p class="text-muted small">Registrate como estudiante para acceder al chat</p>
            </div>
            
            <?php if ($error != ""): ?>
                <div class="alert alert-danger py-2 small"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            <?php if ($mensaje != ""): ?>
                <div class="alert alert-success py-2 small"><?php echo htmlspecialchars($mensaje); ?></div>
            <?php endif; ?>

            <form action="registro.php" method="POST">
                <div class="mb-3">
                    <label class="form-label">Nombre y Apellido</label>
                    <input type="text" class="form-control" name="nombre" placeholder="Ej: Juan Pérez" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Correo Electrónico</label>
                    <input type="email" class="form-control" name="mail" placeholder="ejemplo@correo.com" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Teléfono</label>
                    <input type="text" class="form-control" name="telefono" placeholder="Ej: 11-1234-5678" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Contraseña</label>
                    <input type="password" class="form-control" name="contrasena" placeholder="Crea una contraseña" required>
                </div>
                <div class="d-grid gap-2 mb-3">
                    <button type="submit" class="btn btn-success">Registrarse</button>
                </div>
            </form>
            
            <div class="text-center mt-3">
                <a href="login.php" class="text-decoration-none small">¿Ya tenés cuenta? Iniciá sesión acá</a>
            </div>
        </div>
    </div>
</body>
</html>