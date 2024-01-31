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
$state = 1;
$id = isset($_GET["id"]) ? $_GET["id"] : null;
$opEditar=null;
if (($_SESSION["user"]["ROL"]) && ($_SESSION["user"]["ROL"] == 1)) {
    //llamr los contactos de la base de datos y especificar que sean los que tengan la op_id de la funcion seccion_start
    $op = $conn->query("SELECT*FROM OP");
    //VERFIFICAMOS EL METODOD QUE SE USA EL FORM CON UN IF 
    if ($_SERVER["RQUEST_METHOD"] == "POST") {
        //VALIDFAMOS QUE NO SE MANDEN DATOS VASIOS
        if (empty($_POST["cliente"])||empty($_POST["ciudad"])||empty($_POST["notificacion"])|| empty($_POST["vendedor"])||empty($_POST["direccion"])||empty($_POST["contacto"])||empty($_POST["telefono"])||empty($_POST["observaciones"])||empty($_POST["estado"]) ) {
        } else {
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
            <div class="card">
                <div class="card-body">
                    <?php if ($id) : ?>
                        <h5 class="card-title">Editar Registro de la OP</h5>
                    <?php else : ?>
                        <h5 class="card_title">Nuevo Registro de la OP</h5>
                    <?php endif ?>
                    <!--si hay un error mandar un danger -->
                    <?php if ($error) : ?>
                        <p class="text_danger">
                            <?= $error ?>
                        </p>
                    <?php endif ?>
                    <form class="row g-3" method="POST" action="op.php<?= $id ? "?id=$id":""?>">
                        <div class="col-md-6">
                            <div class="form-floating">
                                <input type="text"class="form-control" id="cliente" name="cliente" placeholder="Cliente" value="<?=$opEditar ? $opEditar["OPCLIENTE"]:""?>">
                                <lavel for="cliente">Cliente</lavel>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-floating">
                                <input type="text"class="form-control" id="ciudad" name="ciudad" placeholder="Ciudad" value="<?=$opEditar ? $opEditar["OPCIUDAD"]:""?>">
                                <lavel for="ciudad">Ciudad</lavel>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-floating datetimepicker">
                                <input type="text"class="form-control" id="notificacion" name="notificacion" placeholder="Notificacion" value="<?=$opEditar ? $opEditar["OPNOTIFICACUIONCORREO"]:""?>">
                                <lavel for="notificacion">Notificacion Correo</lavel>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-floating">
                                <input type="text"class="form-control" id="vendedor" name="vendedor" placeholder="Vendedor" value="<?=$opEditar ? $opEditar["OPVENDEDOR"]:""?>">
                                <lavel for="vendedor">Vendedor</lavel>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-floating">
                                <input type="text"class="form-control" id="direccion" name="direccion" placeholder="Direccion" value="<?=$opEditar ? $opEditar["opDireccionLocal"]:""?>">
                                <lavel for="direccion">Direccion del Local</lavel>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-floating">
                                <input type="text"class="form-control" id="contacto" name="contacto" placeholder="Contacto" value="<?=$opEditar ? $opEditar["OPPERSONACONTACTO"]:""?>">
                                <lavel for="contacto">Persona de Contacto</lavel>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-floating">
                                <input type="text"class="form-control" id="telefono" name="telefono" placeholder="Telefono" value="<?=$opEditar ? $opEditar["TELEFONO"]:""?>">
                                <lavel for="telefono">Telefono</lavel>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-floating">
                                <input type="text"class="form-control" id="observaciones" name="obseravciones" placeholder="Observaciones" value="<?=$opEditar ? $opEditar["OPOBSERVACIONES"]:""?>">
                                <lavel for="observaciones">Observaciones</lavel>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-floating">
                                <input type="text"class="form-control" id="estado" name="estado" placeholder="Estado" value="<?=$opEditar ? $opEditar["OPESTADO"]:""?>">
                                <lavel for="estado">Estado</lavel>
                            </div>
                        </div>
                        <div class="text-center">
                            <button type="submit" class="btn btn-primary"><?= $id ? "Actualizar" : "Submit" ?></button>
                            <button type="reset" class="btn btn-secondary">Reset</button>
                        </div>
                    </form>

                </div>

            </div>
            <section class="section">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="col-md-4 mx-auto mb-3"></h5>
                                 <!-- si el array asociativo $teachers no tiene nada dentro, entonces imprimir el siguiente div -->
                                 <?php if($op->rowCount()==0):?>
                                    <div class="col-md4 mx-auto mb-3">
                                        <div class="card card-body text-center">
                                            <p>No hay Op Registradas</p>
                                        </div>

                                    </div>
                                <?php else: ?>
                                <!-- Table with stripped rows -->
                                <table class="table datatable">
                                    <thead>
                                        <tr>
                                            <th>OP</th>
                                            <th>Cliente</th>  
                                            <th>Detalle</th> 
                                            <th>Registro</th> 
                                            <th>Notificacion del Correo</th> 
                                            <th>Vendedor</th> 
                                            <th>Diseñador</th> 
                                            <th>Direccion del Local</th> 
                                            <th>Persona de Contacto</th> 
                                            <th>Telefono</th> 
                                            <th>Observaciones</th> 
                                            <th>Estado</th> 
                                            <th></th> 
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach($op as$op):?>
                                            <tr>
                                            <th><?=$op["IDOP"]?></th> 
                                            <td><?=$op["OPCLIENTE"]?></td> 
                                            <td><?=$op["OPDETALLE"]?></td>
                                            <td><?=$op["OPREGSITRO"]?></td>
                                            <td><?=$op["OPNOTIFICACIONCORREO"]?></td>
                                            <td><?=$op["OPCEDULA"]?></td>
                                            <td><?=$op["OPDISENIADOR"]?></td>
                                            <td><?=$op["OPDIRECCIONLOCAL"]?></td>
                                            <td><?=$op["OPPERSONACONTACTO"]?></td>
                                            <td><?=$op["TELEFONO"]?></td>
                                            <td><?=$op["OPOBSERVACIONES"]?></td>
                                            <td><?=$op["OPESTADO"]?></td>
                                            <td>
                                                <a href="op.php?id=<?$op["IDOP"]?>" class="btn btn-secondary mb-2">Editar</a>
                                            </td>
                                            <td>
                                                <
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