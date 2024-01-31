<?php

require "../sql/database.php";  

function registrarEnKardex($idUser, $user, $accion, $tabla, $row) {
    global $conn;

    try {
        $statement = $conn->prepare("INSERT INTO KARDEX (ID_USERKARDEX, KARUSER, KARACCION, KARTABLA, KARROW) 
                                     VALUES (:idUser, :user, :accion, :tabla, :row)");

        $statement->execute([
            ":idUser" => $idUser,
            ":user" => $user,
            ":accion" => $accion,
            ":tabla" => $tabla,
            ":row" => $row
        ]);

        return true;  // Indica que el registro en el kardex fue exitoso
    } catch (PDOException $e) {
        // Manejo de errores (puedes loguear el error, mostrar un mensaje de error, etc.)
        // En un entorno de producción, manejar los errores de manera adecuada es crucial
        return false;  // Indica que hubo un error al intentar registrar en el kardex
    }
}

?>
