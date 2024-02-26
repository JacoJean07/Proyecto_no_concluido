<?php
require "../../sql/database.php";
require "../partials/kardex_delete.php";
session_start();

// Verificar si la sesión está iniciada correctamente y el rol es 1 o 2
if (!isset($_SESSION["user"]) || !isset($_SESSION["user"]["ROL"]) || ($_SESSION["user"]["ROL"] == 1 || $_SESSION["user"]["ROL"] == 2)) {
    // Redirigir a index.php si la sesión no es válida o el rol no es correcto
    header("Location: ./index.php");
    return;
}

// Obtener el ID de la OP desde la URL
$id = isset($_GET["id"]) ? intval($_GET["id"]) : 0;
if ($id <= 0) {
    // Manejar error o redirigir a una página de error
    http_response_code(400); // Bad Request
    exit("ID de OP no válido");
}
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
    ":estado" => "2"
]);
//REGISTRA EL MOVIEMIENTO EN EL KARDEX
registrarEnKardex($_SESSION["user"]["ID_USER"], $_SESSION["user"]["USER"], "Se a Activado una Op", 'OP', $id);

//RERIDIRIGIR A OPCIONESOP.PHP
header("Location: ../opcionesOp.php");
// Finalizamos el código aquí porque ya nos redirige a OPCIONESOP.PHP
return;
?>