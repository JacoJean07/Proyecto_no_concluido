<?php
require  "../sql/database.php";
require "./partials/kardex.php";

session_start();
//si la sesion no existe, mandar al login.php y dejar de ejecutar el resto; se puede hacer un required para ahorra codigo
if (!isset($_SESSION["user"])) {
    header("Location: ../login-form/login.php");
    return;
}
//declaramos la variable error que nos ayudara a mostrar errores, etc.
$error = null;
$state = "OP CREADA";
//$state = 1;
$id = isset($_GET["id"]) ? $_GET["id"] : null;
$opEditar=null;
if (($_SESSION["user"]["ROL"]) && ($_SESSION["user"]["ROL"] == 1)) {
    //llamr los contactos de la base de datos y especificar que sean los que tengan la op_id de la funcion seccion_start
    $op = $conn->query("SELECT OP.*, 
                          CEDULA.PERNOMBRES AS CEDULA_NOMBRES, CEDULA.PERAPELLIDOS AS CEDULA_APELLIDOS,
                          VENDEDOR.PERNOMBRES AS VENDEDOR_NOMBRES, VENDEDOR.PERAPELLIDOS AS VENDEDOR_APELLIDOS
                   FROM OP
                   LEFT JOIN PERSONAS AS CEDULA ON OP.CEDULA = CEDULA.CEDULA
                   LEFT JOIN PERSONAS AS VENDEDOR ON OP.OPVENDEDOR = VENDEDOR.CEDULA
                   WHERE OP.OPESTADO = 'EN PRODUCCION'");

    // Obtener opciones para IDAREA desde la base de datos
    $lugarproduccion = $conn->query("SELECT * FROM LUGARPRODUCCION");
    
    $personas=$conn->query("SELECT*FROM PERSONAS");
    //VERFIFICAMOS EL METODOD QUE SE USA EL FORM CON UN IF 
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        

        //VALIDFAMOS QUE NO SE MANDEN DATOS VASIOS
        if (empty($_POST["cedula"])|| empty($_POST["cliente"])||empty($_POST["ciudad"])|| empty($_POST["vendedor"])||empty($_POST["direccion"])||empty($_POST["contacto"])||empty($_POST["telefono"]) ) {
            $error="POR FAVOR LLENAR TODOS LOS CAMPOS";
        } elseif (!preg_match('/^[0-9]{10}$/', $_POST["cedula"])) {
            $error = "La cédula debe contener 10 dígitos numéricos.";
        } elseif (!preg_match('/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/', $_POST["cliente"])) {
            $error = "Nombres del cliente inválidos.";
        } elseif (!preg_match('/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/', $_POST["ciudad"])) {
            $error = "Nombre de la ciudad inválidos.";
        } elseif (!preg_match('/^[a-zA-Z0-9áéíóúÁÉÍÓÚñÑ.,\s\-]+$/', $_POST["direccion"])) {
            $error = "Dirección inválida.";
        } elseif (!preg_match('/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/', $_POST["contacto"])) {
            $error = "Contacto inválida.";
        } elseif (!preg_match('/^[0-9]{10}$/', $_POST["telefono"])) {
            $error = "El telefono debe contener 10 dígitos numéricos.";
        } else {
            //VERIFICAMOS SI YA EXISTE UN REGISTRO PARA  OP ACTUAL
            $existingStament=$conn->prepare("SELECT * FROM OP  WHERE IDOP=:id");
            $existingStament->execute([":id"=> $id]);
            $existingDiseniador=$existingStament->fetch(PDO::FETCH_ASSOC);

            if($existingDiseniador){
                //SI EXITE, SE ACTUALIZA LA OP
                $stament =$conn->prepare("UPDATE OP SET
                OPCIUDAD=:ciudad,
                OPDETALLE=:detalle,
                OPNOTIFICACIONCORREO=:notificacion,
                OPDIRECCIONLOCAL=:dirrecion,
                OPPERESONACONTACTO=:contacto,
                TELEFONO=:telefono,
                OPOBSERAVACIONES=:observaciones");
                $stament->execute([
                    ":ciudad"=>$_POST["ciudad"],
                    ":detalle"=>$_POST["detalle"],
                    "notificacion"=>$_POST["notificacion"],
                    ":dirrecion"=>$_POST["direccion"],
                    ":contacto"=>$_POST["contacto"],
                    ":telefono"=>$_POST["telefono"],
                    ":observaciones"=>$_POST["observaciones"]
                ]);
                // Registramos el movimiento en el kardex
                registrarEnKardex($_SESSION["user"]["ID_USER"], $_SESSION["user"]["USER"], "EDITO", 'OP', $id);

            }else{
                //SINO AY UN REGISTRO ACTUALIZARME
                $stament = $conn->prepare("INSERT INTO OP (CEDULA, IDLUGAR, OPCLIENTE, OPCIUDAD, OPDETALLE, OPNOTIFICACIONCORREO, OPVENDEDOR, OPDIRECCIONLOCAL, OPPERESONACONTACTO, TELEFONO, OPOBSERAVACIONES, OPESTADO)
                VALUES (:cedula, :idlugar, :cliente, :ciudad, :detalle, :notificacion, :vendedor, :direccion, :contacto, :telefono, :observaciones, :estado)");

                $stament->execute([
                    ":cedula" => $_SESSION["user"]["CEDULA"],
                    ":idlugar" => $_POST["idlugarproduccion"],
                    ":cliente" => $_POST["cliente"],
                    ":ciudad" => $_POST["ciudad"],
                    ":detalle" => $_POST["detalle"],
                    ":notificacion" => $_POST["notificacion"],
                    ":vendedor" => $_POST["cedula"],
                    ":direccion" => $_POST["direccion"],
                    ":contacto" => $_POST["contacto"],
                    ":telefono" => $_POST["telefono"],
                    ":observaciones" => $_POST["observaciones"],
                    ":estado" => $state
                ]);

                // Registramos el movimiento en el kardex
                // Obtenemos el último IDOP insertado o actualizado
                $lastInsertId = $conn->lastInsertId();
                registrarEnKardex($_SESSION["user"]["ID_USER"], $_SESSION["user"]["USER"], "CREO", 'OP', $lastInsertId);

                // Obtenemos la cantidad de planos ingresados
                $cantidadPlanos = isset($_POST["planos"]) ? intval($_POST["planos"]) : 0;

                // Verificamos si la cantidad de planos es válida (mayor que cero)
                if ($cantidadPlanos > 0) {
                    

                    // Iteramos sobre la cantidad de planos e insertamos un registro en la tabla PLANOS por cada uno
                    for ($i = 1; $i <= $cantidadPlanos; $i++) {
                        $planoNumero = $i;

                        // Insertamos el registro en la tabla PLANOS
                        $stmt = $conn->prepare("INSERT INTO PLANOS (IDOP, PLANNUMERO) VALUES (:idop, :plannumero)");
                        $stmt->execute([
                            ":idop" => $lastInsertId,
                            ":plannumero" => $planoNumero
                        ]);
                    }
                }
            }
            //REDIRIGIREMOS AHOME.PHP
            header("Location: op.php");
            return;
        
        }
    }
} else {
    header("Location:./index.php");
    return;
}
?>
<?php require "./partials/header.php"; ?>
<?php require "./partials/dashboard.php"; ?>

<section class="section">
    <div class="row">
        <div class="">

            <section class="section">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="card"> 
                            <div class="card-body">
                                <div class="card-header"><h5 class="card-tittle">OP's en producción</h5></div>
                                <h5 class="col-md-4 mx-auto mb-3"></h5>

                                <?php if ($op->rowCount() == 0) : ?>
                                    <div class="col-md-4 mx-auto mb-3">
                                        <div class="card card-body text-center">
                                            <p>No hay Op en Producción</p>
                                        </div>
                                    </div>
                                <?php else : ?>
                                    <!-- Table with stripped rows -->
                                    <table class="table datatable">
                                        <thead>
                                            <tr>
                                                <th>OP</th>
                                                <th>Diseñador</th>
                                                <th>Lugar de Producción</th>
                                                <th>Cliente</th>
                                                <th>Detalle</th>
                                                <th>Registro</th>
                                                <th>Notificación del Correo</th>
                                                <th>Vendedor</th>
                                                <th>Dirección del Local</th>
                                                <th>Persona de Contacto</th>
                                                <th>Teléfono</th>
                                                <th>Observaciones</th>
                                                <th>Estado</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($op as $op) : ?>

                                                <tr>
                                                    <th><?= $op["IDOP"] ?></th>
                                                    <th><?= $op["CEDULA_NOMBRES"] . " " . $op["CEDULA_APELLIDOS"] ?></th>
                                                    <th><?= $op["IDLUGAR"] ?></th>
                                                    <td><?= $op["OPCLIENTE"] ?></td>
                                                    <td><?= $op["OPDETALLE"] ?></td>
                                                    <td><?= $op["OPREGISTRO"] ?></td>
                                                    <td><?= $op["OPNOTIFICACIONCORREO"] ?></td>
                                                    <td><?= $op["VENDEDOR_NOMBRES"] . " " . $op["VENDEDOR_APELLIDOS"] ?></td>
                                                    <td><?= $op["OPDIRECCIONLOCAL"] ?></td>
                                                    <td><?= $op["OPPERESONACONTACTO"] ?></td>
                                                    <td><?= $op["TELEFONO"] ?></td>
                                                    <td><?= $op["OPOBSERAVACIONES"] ?></td>
                                                    <td><?= $op["OPESTADO"] ?></td>
                                                </tr>
                                            <?php endforeach ?>
                                        </tbody>
                                    </table>
                                <?php endif ?>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </div>
</section>

<?php require "./partials/footer.php"; ?>
