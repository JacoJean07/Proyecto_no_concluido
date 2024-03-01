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
$state = "op CREADA";
//$state = 1;
$id = isset($_GET["id"]) ? $_GET["id"] : null;
$opEditar = null;
if (($_SESSION["user"]["usu_rol"]) && ($_SESSION["user"]["usu_rol"] == 1)) {
    //llamr los contactos de la base de datos y especificar que sean los que tengan la op_id de la funcion seccion_start
    $op = $conn->query("SELECT op.*, 
        orden.od_responsable,
        responsable.per_nombres AS responsable_nombres,
        responsable.per_apellidos AS responsable_apellidos,
        orden.od_comercial,
        comercial.per_nombres AS comercial_nombres,
        comercial.per_apellidos AS comercial_apellidos,
        orden.od_detalle,
        orden.od_cliente,
        COUNT(planos.pla_id) AS total_planos
    FROM op
    LEFT JOIN orden_disenio AS orden ON op.od_id = orden.od_id
    LEFT JOIN personas AS responsable ON orden.od_responsable = responsable.cedula
    LEFT JOIN personas AS comercial ON orden.od_comercial = comercial.cedula
    LEFT JOIN planos ON op.op_id = planos.op_id
    WHERE op.op_estado = 'OP CREADA'
    GROUP BY op.op_id"
    );

    // Obtener opciones para IDAREA desde la base de datos
    $lugarproduccion = $conn->query("SELECT * FROM ciudad_produccion");

    $personas = $conn->query("SELECT*FROM personas");
    
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
                                <div class="card-header">
                                    <h5 class="card-tittle">OP'S EN PRODUCCIÓN</h5>
                                </div>
                                <h5 class="col-md-4 mx-auto mb-3"></h5>

                                <?php if ($op->rowCount() == 0) : ?>
                                    <div class="col-md-4 mx-auto mb-3">
                                        <div class="card card-body text-center">
                                            <p>NO HAY OP'S EN PRODUCCIÓN</p>
                                        </div>
                                    </div>
                                <?php else : ?>
                                    <!-- Table with stripped rows -->
                                    <table class="table datatable">
                                        <thead>
                                            <tr>
                                                <th>OP</th>
                                                <th>DISEÑADOR</th>
                                                <th>CIUDAD DE PRODUCCIÓN</th>
                                                <th>CLIENTE</th>
                                                <th>DETALLE</th>
                                                <th>REGISTRO</th>
                                                <th>COMERCIAL</th>
                                                <th>DIRECCIÓN DEL LOCAL</th>
                                                <th>PERSONA DE CONTACTO</th>
                                                <th>TELÉFONO</th>
                                                <th>ESTADO</th>
                                                <th>REPROCESO</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($op as $op) : ?>

                                                <tr>
                                                    <th><?= $op["op_id"] ?></th>
                                                    <td><?= $op["responsable_nombres"] . " " . $op["responsable_apellidos"] ?></td>
                                                    <th><?= $op["lu_id"] ?></th>
                                                    <td><?= $op["od_cliente"] ?></td>
                                                    <td><?= $op["od_detalle"] ?></td>
                                                    <td><?= $op["op_registro"] ?></td>
                                                    <td><?= $op["comercial_nombres"] . " " . $op["comercial_apellidos"] ?></td>
                                                    <td><?= $op["op_direccionLocal"] ?></td>
                                                    <td><?= $op["op_personaContacto"] ?></td>
                                                    <td><?= $op["op_telefono"] ?></td>
                                                    <td><?= $op["op_estado"] ?></td>
                                                    <td>
                                                        <?php
                                                        $reprosoo = $op["op_reproceso"];
                                                        switch ($reprosoo) {
                                                            case 0:
                                                                echo "NO HAY REPROCESO";
                                                                break;
                                                            case 1:
                                                                echo "ES UN REPROCESO";
                                                                break;
                                                                // Agrega más casos según tus necesidades
                                                            default:
                                                                echo "Estado desconocido";
                                                        }
                                                        ?>
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