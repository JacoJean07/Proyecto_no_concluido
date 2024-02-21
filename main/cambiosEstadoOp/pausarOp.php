<?php
require "../../sql/database.php";
require "../partials/kardex_delete.php";
session_start();

// Si la sesión no existe, redirigir al login.php y dejar de ejecutar el resto
if (!isset($_SESSION["user"])) {
    header("Location: ../login.php");
    return;
}

// Usaremos el método GET para buscar el row que vamos a Anular
$id = $_GET["id"];
//primero solicitamos a la based de datos
$stament = $conn->prepare("SELECT * FROM OP WHERE IDOP = :id");
$stament->execute([":id" => $id]);
// Verificar si la consulta devolvió resultados
if ($stament->rowCount() == 0) {
    // Si no se encuentra la OP, redirigir o manejar el error según corresponda
    http_response_code(404);
    header("Location: ../pages-error-404.html");
    return;
}

//OBTENER EL RESULTADO DE AL CONSULTA
$row = $stament->fetch(PDO::FETCH_ASSOC);

//ACTUALIZAMOS EL ESTADO DE LAS OP
$conn->prepare("UPDATE OP SET OPESTADO = :estado WHERE IDOP = :id")->execute([
    ":id" => $id,
    ":estado" => "3"
]);
//REGISTRA EL MOVIEMIENTO EN EL KARDEX
registrarEnKardex($_SESSION["user"]["ID_USER"], $_SESSION["user"]["USER"], "Se a Pausado una Op", 'OP', $id);

//RERIDIRIGIR A OPCIONESOP.PHP
header("Location: ../opcionesOp.php");
// Finalizamos el código aquí porque ya nos redirige a OPCIONESOP.PHP
return;
?>