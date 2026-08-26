<?php
include 'conexion.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['credential'])) {
    $jwt = $_POST['credential'];

    $tokenParts = explode(".", $jwt);
    if (count($tokenParts) === 3) {
        $tokenPayload = base64_decode($tokenParts[1]);
        $jwtPayload = json_decode($tokenPayload, true);

        if ($jwtPayload && isset($jwtPayload['email'])) {
            $mail = trim($jwtPayload['email']);
            $nombre = $jwtPayload['name'] ?? 'Usuario Google';

            // 1. PRIMERO buscamos en Moderador
            $stmt2 = $conn->prepare("SELECT ID_AGBD, Nombre_y_Apellido FROM Moderador WHERE Mail = ?");
            $stmt2->bind_param("s", $mail);
            $stmt2->execute();
            $res_mod = $stmt2->get_result();

            if ($res_mod->num_rows > 0) {
                $mod = $res_mod->fetch_assoc();
                $_SESSION['user_id'] = $mod['ID_AGBD'];
                $_SESSION['nombre'] = $mod['Nombre_y_Apellido'];
                $_SESSION['rol'] = 'moderador';
                header("Location: moderador.php");
                exit();
            }

            // 2. Si no es moderador, buscamos en Usuario
            $stmt = $conn->prepare("SELECT ID_Us, Nombre_y_Apellido FROM Usuario WHERE Mail = ?");
            $stmt->bind_param("s", $mail);
            $stmt->execute();
            $res_us = $stmt->get_result();

            if ($res_us->num_rows > 0) {
                $user = $res_us->fetch_assoc();
                $_SESSION['user_id'] = $user['ID_Us'];
                $_SESSION['nombre'] = $user['Nombre_y_Apellido'];
                $_SESSION['rol'] = 'estudiante';
                header("Location: aulas.php");
                exit();
            }

            $msg_error = "El correo de Google ($mail) no está registrado en el sistema.";
            header("Location: login.php?error=" . urlencode($msg_error));
            exit();
        }
    }
}

header("Location: login.php");
exit();
?>