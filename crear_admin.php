<?php
include 'conexion.php';

$nombre = "Moderador Principal";
$mail = "admin@sistema.com";
$telefono = "11-9999-9999";
$contrasena_plana = "admin123";

// Encriptamos la contraseña usando tu propia versión de PHP
$hash = password_hash($contrasena_plana, PASSWORD_DEFAULT);

// Verificamos si el correo ya existe para no duplicarlo
$check = $conn->query("SELECT * FROM Moderador WHERE Mail = '$mail'");

if ($check->num_rows > 0) {
    echo "<h3 style='color: orange;'>El moderador ya existe.</h3>";
    echo "Ya podés ir al login e ingresar con:<br>";
    echo "Correo: <b>$mail</b><br>Contraseña: <b>$contrasena_plana</b>";
} else {
    // Insertamos el nuevo moderador
    $stmt = $conn->prepare("INSERT INTO Moderador (Nombre_y_Apellido, Mail, Telefono, Contrasena) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $nombre, $mail, $telefono, $hash);
    
    if ($stmt->execute()) {
        echo "<h3 style='color: green;'>¡Moderador creado con éxito!</h3>";
        echo "Ahora podés ir al <a href='login.php'>Login</a> e ingresar con:<br><br>";
        echo "Correo: <b>$mail</b><br>";
        echo "Contraseña: <b>$contrasena_plana</b>";
    } else {
        echo "Error al crear moderador: " . $conn->error;
    }
}
?>