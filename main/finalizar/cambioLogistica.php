<?php
require "../../sql/database.php";
require "../partials/kardex_delete.php";
session_start();

// Si la sesión no existe, redirigir al login.php y dejar de ejecutar el resto
if (!isset($_SESSION["user"])) {
    header("Location: ../login.php");
    return;
}

// Usaremos el método GET para buscar el row que vamos a eliminar
$id = $_GET["id"];

// Primero lo solicitamos a la base de datos
$statement = $conn->prepare("SELECT * FROM LOGISTICA WHERE IDLOGISTICA = :id");
$statement->execute([":id" => $id]);

// Comprobamos que el ID exista, en caso de que el usuario no sea un navegador
if ($statement->rowCount() == 0) {
    http_response_code(404);
    echo("HTTP 404 NOT FOUND");
    return;
}

//ACTUALIZAMOS CON EL ID DE LOGISTICA SELECCIOANDO
$conn->prepare("UPDATE LOGISTICA SET  LOGHORAFINAL = CURRENT_TIMESTAMP, LOGESTADO = :estado WHERE IDLOGISTICA = :id")->execute([
   ":id" => $id,
   ":estado" => "FINALIZADO EL REGISTRO", 
]);
// Registramos el movimiento en el kardex
registrarEnKardex($_SESSION["user"]["ID_USER"], $_SESSION["user"]["USER"], "Finalizadó el Registro", 'LOGíSTICA', $id);

// Redirigimos a personas.php
header("Location: ../logistica.php");

// Finalizamos el código aquí porque ya nos redirige a personas.php
return;
?>