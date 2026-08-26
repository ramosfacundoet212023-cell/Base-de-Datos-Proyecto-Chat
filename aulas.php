<?php
include 'conexion.php';
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['rol'] !== 'estudiante') {
    header("Location: login.php");
    exit();
}

$error = "";

// Validar clave del aula
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['ingresar_aula'])) {
    $id_aula = $_POST['id_aula'];
    $clave_ingresada = $_POST['clave_aula'];

    $stmt = $conn->prepare("SELECT ID_Aula, Nombre_Aula, Clave FROM Aula WHERE ID_Aula = ?");
    $stmt->bind_param("i", $id_aula);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($res->num_rows > 0) {
        $aula = $res->fetch_assoc();
        // Verificación con hash (o clave directa si es plana)
        if (password_verify($clave_ingresada, $aula['Clave']) || $clave_ingresada === $aula['Clave']) {
            $_SESSION['aula_id'] = $aula['ID_Aula'];
            $_SESSION['aula_nombre'] = $aula['Nombre_Aula'];
            header("Location: chat.php");
            exit();
        } else {
            $error = "Contraseña de aula incorrecta.";
        }
    }
}

// Obtener todas las aulas disponibles
$aulas = $conn->query("SELECT ID_Aula, Nombre_Aula FROM Aula");
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Seleccionar Aula</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container py-5" style="max-width: 600px;">
        <h3 class="fw-bold mb-4">Aulas Disponibles</h3>
        <?php if ($error != ""): ?>
            <div class="alert alert-danger py-2 small"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <div class="list-group shadow-sm">
            <?php if ($aulas && $aulas->num_rows > 0): ?>
                <?php while($row = $aulas->fetch_assoc()): ?>
                    <div class="list-group-item p-3">
                        <h5 class="mb-2"><?php echo htmlspecialchars($row['Nombre_Aula']); ?></h5>
                        <form action="aulas.php" method="POST" class="d-flex gap-2">
                            <input type="hidden" name="id_aula" value="<?php echo $row['ID_Aula']; ?>">
                            <input type="password" name="clave_aula" class="form-control form-control-sm" placeholder="Clave del aula" required>
                            <button type="submit" name="ingresar_aula" class="btn btn-sm btn-primary">Entrar</button>
                        </form>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="p-3 text-muted">No hay aulas disponibles creadas.</div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>