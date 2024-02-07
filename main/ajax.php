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

if(isset($_GET['nombres'])){
    $nombres =$_GET['nombres'];

    //REALIZAR LA COULTA EN LA BASE DE DATOS PARA OBTENER LA CEDULA DEL TRABAJADOR
    $statement = $conn->prepare("SELECT CEDULA FROM PERSONAS WHERE PERNOMBRES = :nombres");
    $statement->bindParam(":nombres", $nombres);
    $statement->execute();
    $result = $statement->fetch(PDO::FETCH_ASSOC);

    // VERIFICAR SI LA CONSULTA FUE EXITOSA ANTES DE ACCEDER A LOS VALORES DEL ARRAY
    if ($result !== false) {
        // DEVUELVE LA CEDULA DEL TRABAJADOR COMO RESPUESTA
        echo $result['CEDULA'];
    } else {
        // MANEJAR EL CASO EN QUE LA CONSULTA NO FUE EXITOSAMENTE
        echo "NO SE ENCONTRÓ TRABAJADOR CON ESE NOMBRE";
    }
}

if(isset($_GET['op'])){
    $op = $_GET['op'];

    //REALIZAR LA CONSULTA EN LA BASE DE DATOS PARA OBTENER EL CLIENTE DE LA OP
    $statement = $conn->prepare("SELECT OPCLIENTE FROM OP WHERE IDOP = :op");
    $statement->bindParam(":op", $op);
    $statement->execute();
    $result = $statement->fetch(PDO::FETCH_ASSOC);

    //VERIFICAR SI LAC ONSULTA FUE EXITOSA ANTES DE ACCEDER A LOS VALORES DEL ARRAY
    if($result !== false){
        //DEVUELVE EL CLIENTE DEL TRABAJADOR COMO RESPUESTA
        echo $result['OPCLIENTE'];
    }else{
        //MANEJAR EL CASO EN QUE LA CONSULTA NO FUE EXITOSAMENTE
        echo "NO SE ENCONTRO EL CLIENTE CON LA OP INGRESADA";
    }
}
?>
