<?php
include 'conexion.php';
session_start();

$error = "";

if (isset($_GET['error'])) {
    $error = $_GET['error'];
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $mail = trim($_POST['mail']);
    $password = $_POST['contrasena'];

    // 1. PRIMERO buscamos en Moderador
    $stmt2 = $conn->prepare("SELECT ID_AGBD, Nombre_y_Apellido, Contrasena FROM Moderador WHERE Mail = ?");
    $stmt2->bind_param("s", $mail);
    $stmt2->execute();
    $res_mod = $stmt2->get_result();

    if ($res_mod->num_rows > 0) {
        $mod = $res_mod->fetch_assoc();
        if (password_verify($password, $mod['Contrasena'])) {
            $_SESSION['user_id'] = $mod['ID_AGBD'];
            $_SESSION['nombre'] = $mod['Nombre_y_Apellido'];
            $_SESSION['rol'] = 'moderador';
            header("Location: moderador.php");
            exit();
        } else {
            $error = "Error: El moderador existe, pero la contraseña no coincide con nuestra base de datos.";
        }
    } else {
        // 2. Si NO es moderador, buscamos en Usuario (Estudiante)
        $stmt = $conn->prepare("SELECT ID_Us, Nombre_y_Apellido, Contrasena FROM Usuario WHERE Mail = ?");
        $stmt->bind_param("s", $mail);
        $stmt->execute();
        $res_us = $stmt->get_result();

        if ($res_us->num_rows > 0) {
            $user = $res_us->fetch_assoc();
            if (password_verify($password, $user['Contrasena'])) {
                $_SESSION['user_id'] = $user['ID_Us'];
                $_SESSION['nombre'] = $user['Nombre_y_Apellido'];
                $_SESSION['rol'] = 'estudiante';
                header("Location: aulas.php");
                exit();
            } else {
                $error = "Error: El estudiante existe, pero la contraseña es incorrecta.";
            }
        } else {
            $error = "Error crítico: El correo ingresado no está registrado en ninguna parte del sistema.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema de Chat - Ingreso</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://accounts.google.com/gsi/client" async defer></script>
    <style>
        body { background-color: #f8f9fa; }
        .login-container { max-width: 420px; margin-top: 8%; }
    </style>
</head>
<body>
    <div class="container login-container">
        <div class="card shadow p-4">
            <div class="text-center mb-4">
                <h3 class="text-primary fw-bold">Aula Virtual Chat</h3>
                <p class="text-muted small">Conectá y colaborá de manera constructiva</p>
            </div>
            
            <?php if ($error != ""): ?>
                <div class="alert alert-danger py-2 small fw-bold"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <form action="login.php" method="POST">
                <div class="mb-3">
                    <label class="form-label">Correo Institucional</label>
                    <input type="email" class="form-control" name="mail" placeholder="ejemplo@institucion.edu" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Contraseña</label>
                    <input type="password" class="form-control" name="contrasena" placeholder="••••••••" required>
                </div>
                <div class="d-grid gap-2 mb-3">
                    <button type="submit" class="btn btn-primary">Ingresar al Sistema</button>
                </div>
            </form>

            <div class="text-center mt-2">
                <p class="small mb-1">¿No tenés una cuenta?</p>
                <a href="registro.php" class="btn btn-sm btn-outline-success">Registrarse como Estudiante</a>
            </div>

            <div class="position-relative my-3 text-center">
                <hr class="text-muted">
                <span class="position-absolute top-50 start-50 translate-middle bg-white px-2 text-muted small">O entrá con</span>
            </div>

            <div class="d-flex justify-content-center mt-3">
                <div id="g_id_onload"
                     data-client_id="605947431961-3239d518p3u6du117qg3d19jppj4q2j0.apps.googleusercontent.com"
                     data-context="signin"
                     data-ux_mode="popup"
                     data-callback="handleCredentialResponse"
                     data-auto_prompt="false">
                </div>

                <div class="g_id_signin"
                     data-type="standard"
                     data-shape="rectangular"
                     data-theme="outline"
                     data-text="signin_with"
                     data-size="large"
                     data-logo_alignment="left">
                </div>
            </div>
        </div>
    </div>

    <script>
        function handleCredentialResponse(response) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = 'login_google.php';

            const hiddenInput = document.createElement('input');
            hiddenInput.type = 'hidden';
            hiddenInput.name = 'credential';
            hiddenInput.value = response.credential;

            form.appendChild(hiddenInput);
            document.body.appendChild(form);
            form.submit();
        }
    </script>
</body>
</html>