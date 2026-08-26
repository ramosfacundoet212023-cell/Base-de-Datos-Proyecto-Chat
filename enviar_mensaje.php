<?php
include 'conexion.php';
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['mensaje']) && isset($_SESSION['aula_id'])) {
    $mensaje = trim($_POST['mensaje']);
    $id_us = $_SESSION['user_id'];
    $id_aula = $_SESSION['aula_id'];

    if (!empty($mensaje)) {
        // Guardamos el mensaje poniendo NULL en el moderador (como solucionamos antes)
        $stmt = $conn->prepare("INSERT INTO Chats (ID_Us, ID_Aula, Mensajes, ID_AGBD) VALUES (?, ?, ?, NULL)");
        $stmt->bind_param("iis", $id_us, $id_aula, $mensaje);
        $stmt->execute();
    }
}
?>