<?php
include 'conexion.php';
session_start();

if (!isset($_SESSION['user_id']) || !isset($_SESSION['aula_id'])) {
    exit();
}

$id_aula = $_SESSION['aula_id'];

$query = "SELECT c.Mensajes, c.ID_Us, u.Nombre_y_Apellido 
          FROM Chats c 
          JOIN Usuario u ON c.ID_Us = u.ID_Us 
          WHERE c.ID_Aula = ? 
          ORDER BY c.ID_Chat ASC";

$stmt_get = $conn->prepare($query);
$stmt_get->bind_param("i", $id_aula);
$stmt_get->execute();
$result = $stmt_get->get_result();

if ($result && $result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $es_propio = ($row['ID_Us'] == $_SESSION['user_id']);
        $alineacion = $es_propio ? 'text-end' : 'text-start';
        $nombre = $es_propio ? 'Vos (Anónimo)' : 'Estudiante Anónimo';
        $estilo = $es_propio ? 'msg-me' : 'msg-anon';
        
        echo "<div class='msg-box $alineacion'>";
        echo "<span class='d-block small text-muted mb-1'>$nombre</span>";
        echo "<div class='$estilo'>" . htmlspecialchars($row['Mensajes']) . "</div>";
        echo "</div>";
    }
} else {
    echo "<p class='text-center text-muted my-4'>Aún no hay mensajes en esta aula. ¡Sé el primero en preguntar!</p>";
}
?>