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

// Consultar la información de la OP en la base de datos
$statement = $conn->prepare("SELECT * FROM OP WHERE IDOP = :id");
$statement->execute([":id" => $id]);

// Verificar si la consulta devolvió resultados
if ($statement->rowCount() == 0) {
    // Si no se encuentra la OP, redirigir o manejar el error según corresponda
    http_response_code(404); // Not Found
    header("Location: ../pages-error-404.html");
    exit;
}

// Obtener el resultado de la consulta
$row = $statement->fetch(PDO::FETCH_ASSOC);

// Actualizar el estado de la OP
$conn->prepare("UPDATE OP SET OPESTADO = :estado WHERE IDOP = :id")->execute([
    ":id" => $id,
    ":estado" => "3"
]);

// Registrar el movimiento en el kardex
registrarEnKardex($_SESSION["user"]["ID_USER"], $_SESSION["user"]["USER"], "Se ha pausada una OP", 'OP', $id);

// Redirigir según el rol
if ($_SESSION["user"]["ROL"] == 1) {
    // Redirigir a OPCIONESOP.PHP para el rol 1
    header("Location: ../opcionesOp.php");
} elseif ($_SESSION["user"]["ROL"] == 2) {
    // Redirigir a OTRA_PAGINA.PHP para el rol 2
    header("Location: ../opcionesOp1.php");
} else {
    // Redirigir a otra página por defecto o mostrar un mensaje de error
    header("Location: ./index.php");
}

return;
?>
