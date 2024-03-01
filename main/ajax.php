<?php
require "../sql/database.php";

if (isset($_GET['cedula'])) {
    $cedula = $_GET['cedula'];

    // Realiza la consulta a la base de datos para obtener el per_nombres del trabajador
    $statement = $conn->prepare("SELECT per_nombres, per_apellidos FROM personas WHERE cedula = :cedula");
    $statement->bindParam(":cedula", $cedula);
    $statement->execute();
    $result = $statement->fetch(PDO::FETCH_ASSOC);

    // Verificar si la consulta fue exitosa antes de acceder a los valores del array
    if ($result !== false) {
        // Devuelve el per_nombres del trabajador como respuesta
        echo $result['per_nombres'] . ' ' . $result['per_apellidos'];
    } else {
        // Manejar el caso en que la consulta no fue exitosa
        echo "NO SE ENCONTRÓ TRABAJADOR CON ESA CÉDULA.";
    }
}

if(isset($_GET['nombres'])){
    $nombres =$_GET['nombres'];

    //REALIZAR LA COULTA EN LA BASE DE DATOS PARA OBTENER LA cedula DEL TRABAJADOR
    $statement = $conn->prepare("SELECT cedula FROM personas WHERE per_nombres = :nombres");
    $statement->bindParam(":nombres", $nombres);
    $statement->execute();
    $result = $statement->fetch(PDO::FETCH_ASSOC);

    // VERIFICAR SI LA CONSULTA FUE EXITOSA ANTES DE ACCEDER A LOS VALORES DEL ARRAY
    if ($result !== false) {
        // DEVUELVE LA cedula DEL TRABAJADOR COMO RESPUESTA
        echo $result['cedula'];
    } else {
        // MANEJAR EL CASO EN QUE LA CONSULTA NO FUE EXITOSAMENTE
        echo "NO SE ENCONTRÓ TRABAJADOR CON ESE NOMBRE.";
    }
}

if(isset($_GET['op'])){
    $op = $_GET['op'];

    //REALIZAR LA CONSULTA EN LA BASE DE DATOS PARA OBTENER EL CLIENTE DE LA OP
    $statement = $conn->prepare("SELECT op_cliente FROM op WHERE op_id = :op");
    $statement->bindParam(":op", $op);
    $statement->execute();
    $result = $statement->fetch(PDO::FETCH_ASSOC);

    //VERIFICAR SI LAC ONSULTA FUE EXITOSA ANTES DE ACCEDER A LOS VALORES DEL ARRAY
    if($result !== false){
        //DEVUELVE EL CLIENTE DEL TRABAJADOR COMO RESPUESTA
        echo $result['op_cliente'];
    }else{
        //MANEJAR EL CASO EN QUE LA CONSULTA NO FUE EXITOSAMENTE
        echo "NO SE ENCONTRO EL CLIENTE CON LA OP INGRESADA";
    }
}
?>
