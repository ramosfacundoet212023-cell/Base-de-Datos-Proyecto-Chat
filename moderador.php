<?php
include 'conexion.php';
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['rol'] !== 'moderador') {
    header("Location: login.php");
    exit();
}

$mensaje_exito = "";
$mensaje_error = "";

// 1. Lógica para ascender a un estudiante a Moderador
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['ascender'])) {
    $id_estudiante = (int)$_POST['id_estudiante'];

    $stmt = $conn->prepare("SELECT Nombre_y_Apellido, Mail, Telefono, Contrasena FROM Usuario WHERE ID_Us = ?");
    $stmt->bind_param("i", $id_estudiante);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($res->num_rows > 0) {
        $estudiante = $res->fetch_assoc();
        
        $stmt_check = $conn->prepare("SELECT Mail FROM Moderador WHERE Mail = ?");
        $stmt_check->bind_param("s", $estudiante['Mail']);
        $stmt_check->execute();
        
        if ($stmt_check->get_result()->num_rows == 0) {
            $stmt_ins = $conn->prepare("INSERT INTO Moderador (Nombre_y_Apellido, Mail, Telefono, Contrasena) VALUES (?, ?, ?, ?)");
            $stmt_ins->bind_param("ssss", $estudiante['Nombre_y_Apellido'], $estudiante['Mail'], $estudiante['Telefono'], $estudiante['Contrasena']);
            
            if ($stmt_ins->execute()) {
                $mensaje_exito = "¡El estudiante " . htmlspecialchars($estudiante['Nombre_y_Apellido']) . " ahora es Moderador!";
            } else {
                $mensaje_error = "Error al ascender: " . $conn->error;
            }
        } else {
            $mensaje_error = "Este usuario ya tiene permisos de moderador.";
        }
    }
}

// 2. Lógica para Crear una Nueva Aula
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['crear_aula'])) {
    $nombre_aula = trim($_POST['nombre_aula']);
    $clave_aula = trim($_POST['clave_aula']);

    if (!empty($nombre_aula) && !empty($clave_aula)) {
        // Encriptamos la clave del aula de forma segura
        $hash_clave = password_hash($clave_aula, PASSWORD_DEFAULT);

        $stmt_aula = $conn->prepare("INSERT INTO Aula (Nombre_Aula, Clave) VALUES (?, ?)");
        $stmt_aula->bind_param("ss", $nombre_aula, $hash_clave);

        if ($stmt_aula->execute()) {
            $mensaje_exito = "¡Aula '$nombre_aula' creada exitosamente!";
        } else {
            $mensaje_error = "Error al crear el aula: " . $conn->error;
        }
    } else {
        $mensaje_error = "El nombre y la contraseña del aula son obligatorios.";
    }
}

// 3. Lógica para Eliminar un Aula
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['eliminar_aula'])) {
    $id_aula_del = (int)$_POST['id_aula'];

    // Primero borramos los chats asociados a esa aula para evitar errores de clave foránea
    $stmt_del_chats = $conn->prepare("DELETE FROM Chats WHERE ID_Aula = ?");
    $stmt_del_chats->bind_param("i", $id_aula_del);
    $stmt_del_chats->execute();

    // Luego borramos el aula
    $stmt_del_aula = $conn->prepare("DELETE FROM Aula WHERE ID_Aula = ?");
    $stmt_del_aula->bind_param("i", $id_aula_del);
    
    if ($stmt_del_aula->execute()) {
        $mensaje_exito = "Aula eliminada correctamente.";
    } else {
        $mensaje_error = "Error al eliminar el aula: " . $conn->error;
    }
}

// Obtener listados para la vista
$query_usuarios = "SELECT u.ID_Us, u.Nombre_y_Apellido, u.Mail, u.Telefono, 
                  (SELECT Mensajes FROM Chats WHERE ID_Us = u.ID_Us ORDER BY ID_Chat DESC LIMIT 1) as Ultimo_Mensaje 
                  FROM Usuario u";
$result_usuarios = $conn->query($query_usuarios);

$query_aulas = "SELECT * FROM Aula";
$result_aulas = $conn->query($query_aulas);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Panel de Control - AGBD / Moderación</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
</head>
<body class="bg-light">
    <nav class="navbar navbar-dark bg-dark shadow-sm mb-4">
        <div class="container">
            <span class="navbar-brand mb-0 h1"><i class="bi bi-shield-lock-fill text-warning"></i> Panel AGBD - Control Total</span>
            <a href="login.php" class="btn btn-outline-light btn-sm">Cerrar Sesión</a>
        </div>
    </nav>

    <div class="container">
        <div class="row mb-3">
            <div class="col">
                <h2>Panel de Administración y Moderación</h2>
                <p class="text-muted">Gestioná identidades, asciende moderadores, crea y administra las aulas virtuales.</p>
            </div>
        </div>

        <?php if ($mensaje_exito != ""): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle"></i> <?php echo $mensaje_exito; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
        <?php if ($mensaje_error != ""): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-octagon"></i> <?php echo $mensaje_error; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <!-- SECCIÓN DE GESTIÓN DE AULAS -->
        <div class="row mb-4">
            <!-- Crear Aula -->
            <div class="col-md-5 mb-3">
                <div class="card shadow border-0 h-100">
                    <div class="card-header bg-primary text-white fw-bold">
                        <i class="bi bi-plus-circle"></i> Crear Nueva Aula
                    </div>
                    <div class="card-body">
                        <form method="POST">
                            <div class="mb-3">
                                <label class="form-label">Nombre del Aula</label>
                                <input type="text" name="nombre_aula" class="form-control" placeholder="Ej: Programación II" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Contraseña de Acceso</label>
                                <input type="password" name="clave_aula" class="form-control" placeholder="Clave para los alumnos" required>
                            </div>
                            <button type="submit" name="crear_aula" class="btn btn-primary w-100">Crear Aula</button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Listado y Eliminación de Aulas -->
            <div class="col-md-7 mb-3">
                <div class="card shadow border-0 h-100">
                    <div class="card-header bg-secondary text-white fw-bold">
                        <i class="bi bi-door-open"></i> Aulas Existentes
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive" style="max-height: 250px; overflow-y: auto;">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>ID</th>
                                        <th>Nombre del Aula</th>
                                        <th>Acción</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if ($result_aulas && $result_aulas->num_rows > 0): ?>
                                        <?php while($aula = $result_aulas->fetch_assoc()): ?>
                                            <tr>
                                                <td><code><?php echo $aula['ID_Aula']; ?></code></td>
                                                <td><?php echo htmlspecialchars($aula['Nombre_Aula']); ?></td>
                                                <td>
                                                    <form method="POST" class="d-inline" onsubmit="return confirm('¿Estás seguro de eliminar esta aula y todo su chat?');">
                                                        <input type="hidden" name="id_aula" value="<?php echo $aula['ID_Aula']; ?>">
                                                        <button type="submit" name="eliminar_aula" class="btn btn-danger btn-sm" title="Eliminar Aula">
                                                            <i class="bi bi-trash"></i> Eliminar
                                                        </button>
                                                    </form>
                                                </td>
                                            </tr>
                                        <?php endwhile; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="3" class="text-center text-muted py-3">No hay aulas creadas.</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- SECCIÓN DE TRAZABILIDAD Y USUARIOS -->
        <div class="card shadow border-0 mb-4">
            <div class="card-header bg-dark text-white fw-bold">
                <i class="bi bi-people"></i> Registro de Auditoría y Gestión de Usuarios
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Nombre Completo</th>
                            <th>Mail Institucional</th>
                            <th>Último Mensaje Auditado</th>
                            <th>Acciones de Moderación</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($row = $result_usuarios->fetch_assoc()): ?>
                        <tr>
                            <td><code><?php echo $row['ID_Us']; ?></code></td>
                            <td><?php echo htmlspecialchars($row['Nombre_y_Apellido']); ?></td>
                            <td><?php echo htmlspecialchars($row['Mail']); ?></td>
                            <td><span class="text-muted"><?php echo htmlspecialchars($row['Ultimo_Mensaje'] ?? 'Sin mensajes'); ?></span></td>
                            <td>
                                <form method="POST" class="d-inline">
                                    <input type="hidden" name="id_estudiante" value="<?php echo $row['ID_Us']; ?>">
                                    <button type="submit" name="ascender" class="btn btn-success btn-sm mb-1" title="Hacer Moderador">
                                        <i class="bi bi-arrow-up-circle"></i> Ascender
                                    </button>
                                </form>
                                <button class="btn btn-warning btn-sm mb-1" title="Advertir"><i class="bi bi-exclamation-triangle"></i></button>
                                <button class="btn btn-danger btn-sm mb-1" title="Suspender"><i class="bi bi-slash-circle"></i></button>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>