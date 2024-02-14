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
                        VENDEDOR.PERNOMBRES AS VENDEDOR_NOMBRES, VENDEDOR.PERAPELLIDOS AS VENDEDOR_APELLIDOS,
                        COUNT(PLANOS.IDPLANO) AS TOTAL_PLANOS
                    FROM OP
                    LEFT JOIN PERSONAS AS CEDULA ON OP.CEDULA = CEDULA.CEDULA
                    LEFT JOIN PERSONAS AS VENDEDOR ON OP.OPVENDEDOR = VENDEDOR.CEDULA
                    LEFT JOIN PLANOS ON OP.IDOP = PLANOS.IDOP
                    WHERE OP.OPESTADO = 'OP CREADA'
                    GROUP BY OP.IDOP");

    // Obtener opciones para IDAREA desde la base de datos
    $lugarproduccion = $conn->query("SELECT * FROM LUGARPRODUCCION");
    
    $personas=$conn->query("SELECT*FROM PERSONAS");
    // Calculamos el número total de planos asociados a la OP actual
    $planoCountStatement = $conn->prepare("SELECT COUNT(*) AS total_planos FROM PLANOS WHERE IDOP = :id");
    $planoCountStatement->execute([":id" => $id]);
    $planoCountResult = $planoCountStatement->fetch(PDO::FETCH_ASSOC);
    $totalPlanos = $planoCountResult['total_planos'];
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
                registrarEnKardex($_SESSION["user"]["ID_USER"], $_SESSION["user"]["USER"], "EDITÓ", 'OP', $id);

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
                registrarEnKardex($_SESSION["user"]["ID_USER"], $_SESSION["user"]["USER"], "CREÓ", 'OP', $lastInsertId);

                // Obtenemos la cantidad de planos ingresados
                $cantidadPlanos = isset($_POST["planos"]) ? intval($_POST["planos"]) : 0;

                // Verificamos si la cantidad de planos es válida (mayor que cero)
                if ($cantidadPlanos > 0) {
                    

                    // Iteramos sobre la cantidad de planos e insertamos un registro en la tabla PLANOS por cada uno
                    for ($i = 1; $i <= $cantidadPlanos; $i++) {
                        $planoNumero = $i;

                        // Insertamos el registro en la tabla PLANOS
                        $stmt = $conn->prepare("INSERT INTO PLANOS (IDOP, PLANNUMERO, PLAESTADO, PLANOTIFICACION) VALUES (:idop, :plannumero, 1, 0)");
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
            <?php if (empty($id)) : ?>
                <div class="card accordion" id="accordionExample">
                    <div class="card-body accordion-item">
                        <h5 class="card-title accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
                                NUEVA OP
                            </button>
                        </h5>

                        <?php if ($error) : ?>
                            <p class="text_danger">
                                <?= $error ?>
                            </p>
                        <?php endif ?>
                        <div id="collapseOne" class="accordion-collapse collapse" aria-labelledby="headingOne" data-bs-parent="#accordionExample">
                            <div class="accordion-body">
                                <form class="row g-3" method="POST" action="op.php">
                                    <div class="col-md-6">
                                        <div class="form-floating mb-3">
                                            <input type="text" class="form-control" id="nombres" name="vendedor" placeholder="Buscar por nombre" list="nombresList" oninput="buscarPorNombres()">
                                            <label for="vendedor">Ingresar ambos nombres del vendedor</label>
                                            <datalist id="nombresList">
                                                <?php foreach ($personas as $persona) : ?>
                                                    <option value="<?= $persona["PERNOMBRES"] ?>">
                                                <?php endforeach ?>
                                            </datalist>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-floating">
                                            <input type="text" class="form-control" id="cedula" name="cedula" placeholder="Cedula" readonly>
                                            <label for="cedula"> Cédula</label>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-floating">
                                            <input type="number" class="form-control" id="planos" name="planos" placeholder="">
                                            <label for="planos"> Planos</label>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="idlugarproduccion" class="form-label">Lugar de Producción</label>
                                        <select class="form-select" id="idlugarproduccion" name="idlugarproduccion">
                                            <?php foreach ($lugarproduccion as $lugar) : ?>
                                                <option value="<?= $lugar["IDLUGAR"] ?>"><?= $lugar["CIUDAD"] ?></option>
                                            <?php endforeach ?>
                                        </select>
                                    </div>
                                    <!-- Agregar otros campos según la estructura de la tabla OP -->
                                    <div class="col-md-6">
                                        <div class="form-floating">
                                            <input type="text" class="form-control" id="cliente" name="cliente" placeholder="Cliente">
                                            <label for="cliente">Cliente</label>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-floating">
                                            <input type="text" class="form-control" id="ciudad" name="ciudad" placeholder="Ciudad">
                                            <label for="ciudad">Ciudad de Entrega</label>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-floating datetimepicker">
                                            <input type="text" class="form-control" id="detalle" name="detalle" placeholder="Detalle">
                                            <label for="detalle">Detalles </label>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-floating">
                                            <input type="text" class="form-control" id="direccion" name="direccion" placeholder="Direccion">
                                            <label for="direccion">Dirección del Local</label>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-floating">
                                            <input type="text" class="form-control" id="contacto" name="contacto" placeholder="Contacto">
                                            <label for="contacto">Persona de Contacto</label>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-floating">
                                            <input type="text" class="form-control" id="telefono" name="telefono" placeholder="Telefono">
                                            <label for="telefono">Teléfono</label>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-floating">
                                            <input type="text" class="form-control" id="observaciones" name="observaciones" placeholder="Observaciones">
                                            <label for="observaciones">Observaciones</label>
                                        </div>
                                    </div>
                                    <div class="text-center">
                                        <button type="submit" class="btn btn-primary">Guardar</button>
                                        <button type="reset" class="btn btn-secondary">Limpiar</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            <?php else : ?>
                <?php
                $statement = $conn->prepare("SELECT O.*, P.* FROM OP O INNER JOIN PERSONAS P ON O.CEDULA = P.CEDULA WHERE O.IDOP = :id");
                $statement->bindParam(":id", $id);
                $statement->execute();
                $opEditar = $statement->fetch(PDO::FETCH_ASSOC);
                ?>
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Editar OP</h5>

                        <?php if ($error) : ?>
                            <p class="text-danger">
                                <?= $error ?>
                            </p>
                        <?php endif ?>

                        <form class="row g-3" method="POST" action="op.php">
                            <div class="col-md-6">
                                <div class="form-floating mb-3">
                                    <input value="<?= $opEditar['PERNOMBRES'] ?>" type="text" class="form-control" id="vendedor" name="vendedor" placeholder="Buscar por nombre" list="nombresList" oninput="buscarPorNombres()">
                                    <label for="vendedor">Vendedor</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-floating">
                                    <input value="<?= $opEditar['CEDULA'] ?>" type="text" class="form-control" id="cedula" placeholder="Vendedor" readonly>
                                    <label for="cedula"> Cédula</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label for="idlugarproduccion" class="form-label">ID Lugar de Producción</label>
                                <select class="form-select" id="idlugarproduccion" name="idlugarproduccion">
                                    <?php foreach ($lugarproduccion as $lugar) : ?>
                                        <option value="<?= $lugar["IDLUGAR"] ?>" <?= $opEditar["IDLUGAR"] == $lugar["IDLUGAR"] ? "selected" : "" ?>>
                                            <?= $lugar["CIUDAD"] ?>
                                        </option>
                                    <?php endforeach ?>
                                </select>
                            </div>
                            <!-- Agregar otros campos según la estructura de la tabla OP -->
                            <div class="col-md-6">
                                <div class="form-floating">
                                    <input value="<?= $opEditar["OPCLIENTE"] ?>" type="text" class="form-control" id="cliente" name="cliente" placeholder="Cliente">
                                    <label for="cliente">Cliente</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-floating">
                                    <input value="<?= $opEditar["OPCIUDAD"] ?>" type="text" class="form-control" id="ciudad" name="ciudad" placeholder="Ciudad">
                                    <label for="ciudad">Ciudad</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-floating datetimepicker">
                                    <input value="<?= $opEditar["OPDETALLE"] ?>" type="text" class="form-control" id="detalle" name="detalle" placeholder="Detalle">
                                    <label for="detalle">Detalles </label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-floating datetimepicker">
                                    <input value="<?= $opEditar["OPNOTIFICACIONCORREO"] ?>" type="date" class="form-control" id="notificacion" name="notificacion" placeholder="Notificacion">
                                    <label for="notificacion">Notificación Correo</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-floating">
                                    <input value="<?= $opEditar["OPDIRECCIONLOCAL"] ?>" type="text" class="form-control" id="direccion" name="direccion" placeholder="Direccion">
                                    <label for="direccion">Dirección del Local</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-floating">
                                    <input value="<?= $opEditar["OPPERESONACONTACTO"] ?>" type="text" class="form-control" id="contacto" name="contacto" placeholder="Contacto">
                                    <label for="contacto">Persona de Contacto</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-floating">
                                    <input value="<?= $opEditar["TELEFONO"] ?>" type="text" class="form-control" id="telefono" name="telefono" placeholder="Telefono">
                                    <label for="telefono">Teléfono</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-floating">
                                    <input value="
                                    <?php if ( empty($opEditar["OPOBSERVACIONES"])) : ?>

                                    <?php else : ?>
                                    <?=$opEditar["OPOBSERVACIONES"]?>
                                    <?php endif ?>
                                    " type="text" class="form-control" id="observaciones" name="observaciones" placeholder="Observaciones">
                                    <label for="observaciones">Observaciones</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-floating">
                                    <input value="<?= $opEditar["OPESTADO"] ?>" type="text" class="form-control" id="estado" name="estado" placeholder="Estado">
                                    <label for="estado">Estado</label>
                                </div>
                            </div>
                            <div class="text-center">
                                <button type="submit" class="btn btn-primary">Actualizar</button>
                                <button type="reset" class="btn btn-secondary">Limpiar</button>
                            </div>
                        </form>
                    </div>
                </div>
            <?php endif ?>

            <section class="section">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="card"> 
                            <div class="card-body">
                                <div class="card-header"><h5 class="card-tittle">OP's sin notificar a producción</h5></div>
                                <h5 class="col-md-4 mx-auto mb-3"></h5>

                                <?php if ($op->rowCount() == 0) : ?>
                                    <div class="col-md-4 mx-auto mb-3">
                                        <div class="card card-body text-center">
                                            <p>No hay Op Registradas</p>
                                        </div>
                                    </div>
                                <?php else : ?>
                                    <!-- Table with stripped rows -->
                                    <table class="table datatable">
                                        <thead>
                                            <tr>
                                                <th>OP</th>
                                                <th>Diseñador</th>
                                                <th>Planos Totales</th>
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
                                                <th></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($op as $op) : ?>
                                                <tr>
                                                    <th><?= $op["IDOP"] ?></th>
                                                    <th><?= $op["CEDULA_NOMBRES"] . " " . $op["CEDULA_APELLIDOS"] ?></th>
                                                    <th><?= $op["TOTAL_PLANOS"] ?></th>
                                                    <td><?= $op["OPCLIENTE"] ?></td>
                                                    <td><?= $op["OPDETALLE"] ?></td>
                                                    <td><?= $op["OPREGISTRO"] ?></td>
                                                    <td>
                                                    <?php if ($op["TOTAL_PLANOS"] != 0) : ?>
                                                        <a href="./validaciones/notiOp.php?id=<?= $op["IDOP"] ?>" class="btn btn-primary mb-2">Notificar</a>
                                                    <?php else : ?>
                                                        <a href="planosAdd.php?id=<?= $op["IDOP"]?>" class="btn btn-secondary mb-2">Ingrese planos</a>
                                                    <?php endif ?>
                                                    </td>
                                                    <td><?= $op["VENDEDOR_NOMBRES"] . " " . $op["VENDEDOR_APELLIDOS"] ?></td>
                                                    <td><?= $op["OPDIRECCIONLOCAL"] ?></td>
                                                    <td><?= $op["OPPERESONACONTACTO"] ?></td>
                                                    <td><?= $op["TELEFONO"] ?></td>
                                                    <td><?= $op["OPOBSERAVACIONES"] ?></td>
                                                    <td><?= $op["OPESTADO"] ?></td>
                                                    <td>
                                                        <a href="op.php?id=<?= $op["IDOP"] ?>" class="btn btn-secondary mb-2">Editar</a>
                                                    </td>
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
