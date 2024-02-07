<?php
// Conectar a la base de datos y realizar la búsqueda
require "../sql/database.php";

if(isset($_POST['op'])){
    $op = $_POST['op'];

    // Realizar la consulta a la base de datos (reemplaza esto con tu consulta real)
    $resultados = $conn->query("SELECT PLANNUMERO FROM planos WHERE IDOP LIKE '%$op%'");

    // Construir las opciones del select
    if($resultados->rowCount() > 0){
        $output = '<option value="" selected>Selecciona un número de plano</option>';
        while($row = $resultados->fetch(PDO::FETCH_ASSOC)){
            $output .= '<option value="' . $row['PLANNUMERO'] . '">' . $row['PLANNUMERO'] . '</option>';
        }
        echo $output;
    } else {
        echo '<option value="" selected>No se encontraron resultados</option>';
    }
}
?>
