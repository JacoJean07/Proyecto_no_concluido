<?php
require "../../sql/database.php";
// Iniciar sesión para identificar las sesiones
session_start();
// Verificar si la sesión no existe, redirigir al login.php y detener la ejecución del script
if (!isset($_SESSION["user"])) {
  header("Location: login.php");
  return;
}

// Obtener el ID y el detalle de la actividad a eliminar desde los parámetros GET
$id = $_GET["id"];
$detalle = urldecode($_GET["detalle"]);

// Verificar si el ID y el detalle están presentes
if (empty($id) || empty($detalle)) {
  // Si alguno de los parámetros está vacío, mostrar un mensaje de error y detener la ejecución
  echo "ID o detalle de la actividad no proporcionado.";
  return;
}

// Eliminar la actividad con el ID proporcionado
$statement = $conn->prepare("DELETE FROM od_actividades WHERE od_id = :id AND odAct_detalle = :detalle");
$statement->execute([":id" => $id, ":detalle" => $detalle]);

// Redirigir al usuario de regreso a la página de actividades
header("Location: ../od_actividades.php?id=$id");
// Finalizar el script para evitar que se ejecute más código
return;
?>
