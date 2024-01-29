<?php
require "../sql/database.php";

if (isset($_GET['cedula'])) {
    $cedula = $_GET['cedula'];

    // Realiza la consulta a la base de datos para obtener el PERNOMBRES del trabajador
    $statement = $conn->prepare("SELECT PERNOMBRES, PERAPELLIDOS FROM PERSONAS WHERE CEDULA = :cedula");
    $statement->bindParam(":cedula", $cedula);
    $statement->execute();
    $result = $statement->fetch(PDO::FETCH_ASSOC);

    // Verificar si la consulta fue exitosa antes de acceder a los valores del array
    if ($result !== false) {
        // Devuelve el PERNOMBRES del trabajador como respuesta
        echo $result['PERNOMBRES'] . ' ' . $result['PERAPELLIDOS'];
    } else {
        // Manejar el caso en que la consulta no fue exitosa
        echo "No se encontró un trabajador con esa cédula";
    }
}
?>

