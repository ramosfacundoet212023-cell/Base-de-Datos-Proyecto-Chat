<?php
$host = "localhost";
$user = "root";
$pass = ""; // Por defecto en XAMPP viene vacío
$db = "sistema_chat";

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}
?>