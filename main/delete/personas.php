<?php

require "../../sql/database.php";
session_start();

// Si la sesión no existe, redirigir al login.php y dejar de ejecutar el resto
if (!isset($_SESSION["user"])) {
    header("Location: ../login.php");
    return;
}

// Usaremos el método GET para buscar el row que vamos a eliminar
$id = $_GET["id"];

// Primero lo solicitamos a la base de datos
$statement = $conn->prepare("SELECT * FROM PERSONAS WHERE CEDULA = :id");
$statement->execute([":id" => $id]);

// Comprobamos que el ID exista, en caso de que el usuario no sea un navegador
if ($statement->rowCount() == 0) {
    http_response_code(404);
    echo("HTTP 404 NOT FOUND");
    return;
}

// Actualizamos el row con el ID de la cédula seleccionada
$conn->prepare("UPDATE PERSONAS SET PERESTADO = :estado WHERE CEDULA = :id")->execute([
    ":id" => $id,
    ":estado" => 0,
]);

// Redirigimos a personas.php
header("Location: ../personas.php");

// Finalizamos el código aquí porque ya nos redirige a personas.php
return;
?>