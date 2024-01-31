<?php
require  "../sql/database.php";
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
    $op = $conn->query("SELECT*FROM OP");
    // Obtener opciones para IDAREA desde la base de datos
    $lugarproduccion = $conn->query("SELECT * FROM LUGARPRODUCCION");
    
    $personas=$conn->query("SELECT*FROM PERSONAS");
    //VERFIFICAMOS EL METODOD QUE SE USA EL FORM CON UN IF 
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        //VALIDFAMOS QUE NO SE MANDEN DATOS VASIOS
        if (empty($_POST["cliente"])||empty($_POST["ciudad"])|| empty($_POST["vendedor"])||empty($_POST["direccion"])||empty($_POST["contacto"])||empty($_POST["telefono"]) ) {
            $error="POR FAVOR LLENAR TODOS LOS CAMPOS";
        } else {
            //VERIFICAMOS SI YA EXISTE UN REGISTRO PARA  OP ACTUAL
            $existingStament=$conn->prepare("SELECT IDOP FROM OP  WHERE CEDULA=:cedula");
            $existingStament->execute([":cedula"=> $_POST['cedula']]);
            $existingDiseniador=$existingStament->fetch(PDO::FETCH_ASSOC);

            if($existingDiseniador){
                //SI EXITE, SE ACTUALIZA LA OP
                $stament =$conn->prepare("UPDATE OP SET
                OPCIUDAD=:ciudad,
                OPDETALLE=:detalle,
                OPNOTIFICACIONCORREO=:notificacion,
                OPDIRECCIONLOCAL=:dirrecion,
                OPPERSONACONTACTO=:contacto,
                TELEFONO=:telefono,
                OPOBSERVACIONES=:observaciones");
                $stament->execute([
                    ":ciudad"=>$_POST["ciudad"],
                    ":detalle"=>$_POST["detalle"],
                    "notificacion"=>$_POST["notificacion"],
                    ":dirrecion"=>$_POST["direccion"],
                    ":contacto"=>$_POST["contacto"],
                    ":telefono"=>$_POST["telefono"],
                    ":observaciones"=>$_POST["observaciones"]
                ]);

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
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">NUEVA OP</h5>

                        <?php if ($error) : ?>
                            <p class="text_danger">
                                <?= $error ?>
                            </p>
                        <?php endif ?>

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
                                    <label for="cedula"> Cedula</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label for="idlugarproduccion" class="form-label">Lugar de Produccion</label>
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
                                <div class="form-floating datetimepicker">
                                    <input type="date" class="form-control" id="notificacion" name="notificacion" placeholder="Notificacion">
                                    <label for="notificacion">Notificacion Correo</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-floating">
                                    <input type="text" class="form-control" id="direccion" name="direccion" placeholder="Direccion">
                                    <label for="direccion">Direccion del Local</label>
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
                                    <label for="telefono">Telefono</label>
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
                                <button type="reset" class="btn btn-secondary">Reset</button>
                            </div>
                        </form>
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
                                    <label for="cedula"> Cedula</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label for="idlugarproduccion" class="form-label">ID Lugar de Produccion</label>
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
                                    <label for="notificacion">Notificacion Correo</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-floating">
                                    <input value="<?= $opEditar["OPDIRECCIONLOCAL"] ?>" type="text" class="form-control" id="direccion" name="direccion" placeholder="Direccion">
                                    <label for="direccion">Direccion del Local</label>
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
                                    <label for="telefono">Telefono</label>
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
                                <button type="reset" class="btn btn-secondary">Reset</button>
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
                                                <th>Lugar de Produccion</th>
                                                <th>Cliente</th>
                                                <th>Detalle</th>
                                                <th>Registro</th>
                                                <th>Notificacion del Correo</th>
                                                <th>Vendedor</th>
                                                <th>Direccion del Local</th>
                                                <th>Persona de Contacto</th>
                                                <th>Telefono</th>
                                                <th>Observaciones</th>
                                                <th>Estado</th>
                                                <th></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($op as $op) : ?>
                                                <tr>
                                                    <th><?= $op["IDOP"] ?></th>
                                                    <th><?= $op["CEDULA"] ?></th>
                                                    <th><?= $op["IDLUGAR"] ?></th>
                                                    <td><?= $op["OPCLIENTE"] ?></td>
                                                    <td><?= $op["OPDETALLE"] ?></td>
                                                    <td><?= $op["OPREGISTRO"] ?></td>
                                                    <td><?= $op["OPNOTIFICACIONCORREO"] ?></td>
                                                    <td><?= $op["OPVENDEDOR"] ?></td>
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
