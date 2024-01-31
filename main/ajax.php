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
<?php
require "../sql/database.php";
if(isset($_GET['nombres'])){
    $NOMBRES =$_GET['nombres'];

    //REALIZAR LA COULTA EN LA BASE DE DATOS PARA OBTENER LA EULA DEL TRABAJDOR
    $statement+$onn->prepare("SELECT CEDULA FROM PERSONAS WHERE PERNOMBRES =:nombres");
    $statement->bindParam(":nombres",$cedula);
    $statement->execute();
    $statement=$statement->fetch(PDO::FETCH_ASSOC);

    //VERIFICAR SI LAC ONSULTA FUE EXITOSA ANTES DE ACCEDER A LOS VALORES DEL DEL ARRAY
    if($result!== false){
        //DEVUELVA LA CEDULA DEL TRABAJADOR COMO RESPUETA
        echo $result['CEDULA'];
    }else{
        //MANEJAR EL CASO EN QUE LAC ONSULTA NO FUE EXITOSAMENTE
        echo"NO SE ENCONTRO TRABAJADOR CON ESE NOMBRE";
    }

}
?>
