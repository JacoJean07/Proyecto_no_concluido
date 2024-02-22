<?php
require "../sql/database.php";
require "./partials/kardex.php";

session_start();

// Si la sesión no existe, redirigir al login.php y dejar de ejecutar el resto
if (!isset($_SESSION["user"])) {
    header("Location: ../login-form/login.php");
    return;
}
// Declaramos la variable error que nos ayudará a mostrar errores, etc.
$error = null;
$idop = isset($_GET["idop"]) ? $_GET["idop"] : null;
$opInfo = null;
$opPlanos = null;
if ($_SESSION["user"]["ROL"] && $_SESSION["user"]["ROL"] == 1) {
    //llamr los contactos de la base de datos y especificar que sean los que tengan la op_id de la funcion seccion_start
    $op = $conn->query("SELECT OP.*, 
                          CEDULA.PERNOMBRES AS CEDULA_NOMBRES, CEDULA.PERAPELLIDOS AS CEDULA_APELLIDOS,
                          VENDEDOR.PERNOMBRES AS VENDEDOR_NOMBRES, VENDEDOR.PERAPELLIDOS AS VENDEDOR_APELLIDOS
                    FROM OP
                    LEFT JOIN PERSONAS AS CEDULA ON OP.CEDULA = CEDULA.CEDULA
                    LEFT JOIN PERSONAS AS VENDEDOR ON OP.OPVENDEDOR = VENDEDOR.CEDULA
                    WHERE OP.OPESTADO NOT IN ('5', '4')");

    $opanulada = $conn->query("SELECT OP.*, 
                            CEDULA.PERNOMBRES AS CEDULA_NOMBRES, CEDULA.PERAPELLIDOS AS CEDULA_APELLIDOS,
                            VENDEDOR.PERNOMBRES AS VENDEDOR_NOMBRES, VENDEDOR.PERAPELLIDOS AS VENDEDOR_APELLIDOS
                    FROM OP
                    LEFT JOIN PERSONAS AS CEDULA ON OP.CEDULA = CEDULA.CEDULA
                    LEFT JOIN PERSONAS AS VENDEDOR ON OP.OPVENDEDOR = VENDEDOR.CEDULA
                    WHERE OP.OPESTADO IN ('4')");
    $opfinalizada = $conn->query("SELECT OP.*, 
                            CEDULA.PERNOMBRES AS CEDULA_NOMBRES, CEDULA.PERAPELLIDOS AS CEDULA_APELLIDOS,
                            VENDEDOR.PERNOMBRES AS VENDEDOR_NOMBRES, VENDEDOR.PERAPELLIDOS AS VENDEDOR_APELLIDOS
                    FROM OP
                    LEFT JOIN PERSONAS AS CEDULA ON OP.CEDULA = CEDULA.CEDULA
                    LEFT JOIN PERSONAS AS VENDEDOR ON OP.OPVENDEDOR = VENDEDOR.CEDULA
                    WHERE OP.OPESTADO IN ('5')");
    $optotal = $conn->query("SELECT OP.*, 
                            CEDULA.PERNOMBRES AS CEDULA_NOMBRES, CEDULA.PERAPELLIDOS AS CEDULA_APELLIDOS,
                            VENDEDOR.PERNOMBRES AS VENDEDOR_NOMBRES, VENDEDOR.PERAPELLIDOS AS VENDEDOR_APELLIDOS
                    FROM OP
                    LEFT JOIN PERSONAS AS CEDULA ON OP.CEDULA = CEDULA.CEDULA
                    LEFT JOIN PERSONAS AS VENDEDOR ON OP.OPVENDEDOR = VENDEDOR.CEDULA");

    // Obtener opciones para IDAREA desde la base de datos
    $lugarproduccion = $conn->query("SELECT * FROM LUGARPRODUCCION");

    $personas = $conn->query("SELECT*FROM PERSONAS");
    //VERIFICAMOS EL METODO QUE SE USA CON EL IF
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        //VALIDAMOS QUE LOS DATOS NO ESTEN VACIOS
        if (empty($_POST["idop"])) {
            $error = "POR FAVOR DEBE RELLENAR EL CAMPO DE LA OP";
        } else {
        }
    }
}
?>

<?php require "./partials/header.php"; ?>
<?php require "./partials/dashboard.php"; ?>
<section class="section">
    <div class="row">
        <div class="">
            <div class="card">
                <h5 class="card-title">Litas de lo tipos de Op</h5>
                <ul class="nav nav-tabs" id="myTab" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="estado-tab" data-bs-toggle="tab" data-bs-target="#estado" type="button" role="tab" aria-controls="estado" aria-selected="true">Cambio de los estados de las OP</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="finalizada-tab" data-bs-toggle="tab" data-bs-target="#finalizado" type="button" role="tab" aria-controls="finalizado" aria-selected="false" tabindex="-1">Op's Finalizadas</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="anulado-tab" data-bs-toggle="tab" data-bs-target="#anulado" type="button" role="tab" aria-controls="anulado" aria-selected="false" tabindex="-2">Op's Anuladas</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="op-tab" data-bs-toggle="tab" data-bs-target="#op" type="button" role="tab" aria-controls="op" aria-selected="false" tabindex="-3">OP</button>
                    </li>
                </ul>
                <div class="tab-content pt-2" id="myTabContent">
                    <div class="tab-pane fade show active" id="estado" role="tabpanel" aria-labelledby="estado-tab">
                        <section class="section">
                            <div class="row">
                                <div class="col-lg-12">
                                    <div class="card">
                                        <div class="card-body">
                                            <div class="card-header">
                                                <h5 class="card-title">cambios Op</h5>
                                            </div>
                                            <table class="table datatable">
                                                <thead>
                                                    <tr>
                                                        <th>Op</th>
                                                        <th>Cliente</th>
                                                        <th>Diseñador</th>
                                                        <th>Estado</th>
                                                        <th>Reproseso</th>
                                                        <th></th>
                                                        <th></th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach ($op as $op) : ?>
                                                        <tr>
                                                            <td><?= $op["IDOP"] ?> </td>
                                                            <td><?= $op["OPCLIENTE"] ?></td>
                                                            <td><?= $op["CEDULA_NOMBRES"] . " " . $op["CEDULA_APELLIDOS"] ?></td>
                                                            <td>
                                                                <?php
                                                                $estado = $op["OPESTADO"];
                                                                switch ($estado) {
                                                                    case 1:
                                                                        echo "OP CREADA";
                                                                        break;
                                                                    case 2:
                                                                        echo "OP PRODUCCION";
                                                                        break;
                                                                    case 3:
                                                                        echo "OP PAUSADA";
                                                                        break;
                                                                }
                                                                ?>
                                                            </td>
                                                            <td>
                                                                <?php if ($op["OPREPROSESO"] != 0) : ?>
                                                                    Es un reproceso
                                                                <?php else : ?>
                                                                    <button type="button" class="btn btn-warning mb-2" onclick="openReprosesoModal(<?= $op["IDOP"] ?>)">Reproseso</button>
                                                                    <div class="modal fade" id="reproseso-<?= $op["IDOP"] ?>" tabindex="-1" style="display: none;" aria-modal="true" role="dialog">
                                                                        <div class="modal-dialog modal-dialog-centered">
                                                                            <div class="modal-content">
                                                                                <div class="modal-header">
                                                                                    <h5 class="modal-title">Reproseso de la OP</h5>
                                                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                                                </div>
                                                                                <div class="modal-body">
                                                                                    <p>Esta usted de acuerdo en generar u reproseso en la OP <?= $op["IDOP"] ?> del cliente <?= $op["OPCLIENTE"] ?></p>
                                                                                    <section class="section">
                                                                                        <div class="row">
                                                                                            <div class="">
                                                                                                <?php if ($error) : ?>
                                                                                                    <p class="text_danger">
                                                                                                        <?= $error ?>
                                                                                                    </p>
                                                                                                <?php endif ?>
                                                                                                <div class="card-body">
                                                                                                    <form class="row g-3" method="post" action="">
                                                                                                        <div class="col-md-6">
                                                                                                            <div class="form-floating">
                                                                                                                <input type="text" class="form-control" id="observacion" name="obseravcion" placeholder="observacion">
                                                                                                                <label for="obseravacion">Ingrese la obervación</label>
                                                                                                            </div>
                                                                                                        </div>
                                                                                                    </form>
                                                                                                </div>
                                                                                            </div>
                                                                                        </div>
                                                                                    </section>
                                                                                </div>
                                                                                <div class="modal-footer">
                                                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                                                                    <a href="./cambiosEstadoOp/reprosesoOP.php?id=<?= $op["IDOP"] ?>" class="btn btn-warning mb-2">Reproseso</a>
                                                                                </div>
                                                                            </div>
                                                                            <script>
                                                                                function openReprosesoModal(idop) {
                                                                                    // Construye el ID del modal específico basado en el ID de la OP
                                                                                    var modalId = "reproseso-" + idop;
                                                                                    // Abre el modal correspondiente
                                                                                    $("#" + modalId).modal("show");
                                                                                }
                                                                            </script>
                                                                        </div>
                                                                    </div>
                                                                <?php endif  ?>
                                                            </td>
                                                            <td>
                                                                <?php if ($op["OPESTADO"] != 3) : ?>
                                                                    <button type="button" class="btn btn-success mb-2" onclick="openPausarModal(<?= $op["IDOP"] ?>)">Pausar</button>
                                                                    <div class="modal fade" id="pausar-<?= $op["IDOP"] ?>" tabindex="-1" style="display: none;" aria-modal="true" role="dialog">
                                                                        <div class="modal-dialog modal-dialog-centered">
                                                                            <div class="modal-content">
                                                                                <div class="modal-header">
                                                                                    <h5 class="modal-title">Pausar OP</h5>
                                                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                                                </div>
                                                                                <div class="modal-body">
                                                                                    <p>Esta usted de acuerdo de pausar la OP <?= $op["IDOP"] ?> del cliente <?= $op["OPCLIENTE"] ?></p>
                                                                                    <section class="section">
                                                                                        <div class="row">
                                                                                            <div class="">
                                                                                                <?php if ($error) : ?>
                                                                                                    <p class="text_danger">
                                                                                                        <?= $error ?>
                                                                                                    </p>
                                                                                                <?php endif ?>
                                                                                                <div class="card-body">
                                                                                                    <form class="row g-3" method="post" action="">
                                                                                                        <div class="col-md-6">
                                                                                                            <div class="form-floating">
                                                                                                                <input type="text" class="form-control" id="observacio" name="obervacione" placeholder="observacion">
                                                                                                                <label for="observacion">Registre la observacion</label>
                                                                                                            </div>
                                                                                                        </div>
                                                                                                    </form>
                                                                                                </div>
                                                                                            </div>
                                                                                        </div>
                                                                                    </section>
                                                                                </div>
                                                                                <div class="modal-footer">
                                                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                                                                    <a href="./cambiosEstadoOp/pausarOp.php?id=<?= $op["IDOP"] ?>" class="btn btn-success mb-2">Pausar</a>
                                                                                </div>
                                                                            </div>
                                                                            <script>
                                                                                function openPausarModal(idop) {
                                                                                    // Construye el ID del modal específico basado en el ID de la OP
                                                                                    var modalId = "pausar-" + idop;
                                                                                    // Abre el modal correspondiente
                                                                                    $("#" + modalId).modal("show");
                                                                                }
                                                                            </script>
                                                                        </div>
                                                                    </div>
                                                                <?php else : ?>
                                                                    <button type="button" class="btn btn-primary mb-2" onclick="openActivarModal(<?= $op["IDOP"] ?>)">Activar</button>
                                                                    <div class="modal fade" id="activar-<?= $op["IDOP"] ?>" tabindex="-1" style="display: none;" aria-modal="true" role="dialog">
                                                                        <div class="modal-dialog modal-dialog-centered">
                                                                            <div class="modal-content">
                                                                                <div class="modal-header">
                                                                                    <h5 class="modal-title">Activar OP</h5>
                                                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                                                </div>
                                                                                <div class="modal-body">
                                                                                    <p>Esta usted seguro de activar la OP <?= $op["IDOP"] ?> del cliente <?= $op["OPCLIENTE"] ?></p>
                                                                                    <section class="section">
                                                                                        <div class="row">
                                                                                            <div class="">
                                                                                                <?php if ($error) : ?>
                                                                                                    <p class="text_danger">
                                                                                                        <?= $error ?>
                                                                                                    </p>
                                                                                                <?php endif ?>
                                                                                                <div class="card-body">
                                                                                                    <form class="row g-3" method="post" action="">
                                                                                                        <div class="col-md-6">
                                                                                                            <div class="form-floating">
                                                                                                                <input type="text" class="form-control" id="observaciones" name="observaciones" placeholder="observaciones">
                                                                                                                <label for="observaciones">Registre la obervacion</label>
                                                                                                            </div>
                                                                                                        </div>
                                                                                                    </form>
                                                                                                </div>
                                                                                            </div>
                                                                                        </div>
                                                                                    </section>
                                                                                </div>
                                                                                <div class="modal-footer">
                                                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                                                                    <a href="./cambiosEstadoOp/activarOp.php?id=<?= $op["IDOP"] ?>" class="btn btn-primary mb-2">Activar</a>
                                                                                </div>
                                                                            </div>
                                                                            <script>
                                                                                function openActivarModal(idop) {
                                                                                    // Construye el ID del modal específico basado en el ID de la OP
                                                                                    var modalId = "activar-" + idop;
                                                                                    // Abre el modal correspondiente
                                                                                    $("#" + modalId).modal("show");
                                                                                }
                                                                            </script>
                                                                        </div>
                                                                    </div>
                                                                <?php endif ?>
                                                            </td>
                                                            <td>
                                                                <button type="button" class="btn btn-danger mb-2" onclick="openAnularModal(<?= $op["IDOP"] ?>)">Anular</button>
                                                                <div class="modal fade" id="anular-<?= $op["IDOP"] ?>" tabindex="-1" style="display: none;" aria-modal="true" role="dialog">
                                                                    <div class="modal-dialog modal-dialog-centered">
                                                                        <div class="modal-content">
                                                                            <div class="modal-header">
                                                                                <h5 class="modal-title">Anular Op</h5>
                                                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                                            </div>
                                                                            <div class="modal-body">
                                                                                <p>Esta usted seguro que quiere anular la siguiente OP <?= $op["IDOP"] ?> del cliente <?= $op["OPCLIENTE"] ?></p>
                                                                                <section class="section">
                                                                                    <div class="row">
                                                                                        <div class="">
                                                                                            <?php if ($error) : ?>
                                                                                                <p class="text_danger">
                                                                                                    <?= $error ?>
                                                                                                </p>
                                                                                            <?php endif ?>
                                                                                            <div class="card-body">
                                                                                                <form class="row g-3" method="post" action="">
                                                                                                    <div class="col-md-6">
                                                                                                        <div class="form-floating">
                                                                                                            <input type="text" class="form-control" id="observacion" name="obsevacion" placeholder="obervacion">
                                                                                                            <label for="obssevacio">Registre la Obervacion</label>
                                                                                                        </div>
                                                                                                    </div>
                                                                                                </form>
                                                                                            </div>
                                                                                        </div>
                                                                                    </div>
                                                                                </section>
                                                                            </div>
                                                                            <div class="modal-footer">
                                                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                                                                                <a href="./cambiosEstadoOp/anularOP.php?id=<?= $op["IDOP"] ?>" class="btn btn-primary">Anular</a>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    <script>
                                                                        function openAnularModal(idop) {
                                                                            // Construye el ID del modal específico basado en el ID de la OP
                                                                            var modalId = "anular-" + idop;
                                                                            // Abre el modal correspondiente
                                                                            $("#" + modalId).modal("show");
                                                                        }
                                                                    </script>
                                                                </div>
                                                            </td>
                                                        </tr>
                                                    <?php endforeach ?>
                                                </tbody>
                                            </table>

                                        </div>
                                    </div>
                                </div>
                            </div>
                        </section>
                    </div>
                    <div class="tab-pane fade" id="finalizado" role="tabpanel" aria-labelledby="finalizado-tab">
                        <section class="section">
                            <div class="row">
                                <div class="col-lg-12">
                                    <div class="card">
                                        <div class="card-body">
                                            <div class="card-header">
                                                <h5 class="card-title">Op's Finalizadas</h5>
                                            </div>
                                            <table class="table datatable">
                                                <thead>
                                                    <tr>
                                                        <th>Op</th>
                                                        <th>Cliente</th>
                                                        <th>Detalle</th>
                                                        <th>Diseñador</th>
                                                        <th>Vendedor</th>
                                                        <th>Hora de Registro</th>
                                                        <th>Hora de Notificacion</th>
                                                        <th>Dirección del Local</th>
                                                        <th>Persona de Contacto</th>
                                                        <th>Telefono de Contacto</th>
                                                        <th>Reproseso</th>
                                                        <th>Estado</th>
                                                    </tr>
                                                <tbody>
                                                    <?php foreach ($opfinalizada as $opfinalizada) : ?>
                                                        <tr>
                                                            <td><?= $opfinalizada["IDOP"] ?></td>
                                                            <td><?= $opfinalizada["OPCLIENTE"] ?></td>
                                                            <td><?= $opfinalizada["OPDETALLE"] ?></td>
                                                            <td><?= $opfinalizada["CEDULA_NOMBRES"] . " " . $opfinalizada["CEDULA_APELLIDOS"] ?></td>
                                                            <td><?= $opfinalizada["VENDEDOR_NOMBRES"] . " " . $opfinalizada["VENDEDOR_APELLIDOS"] ?></td>
                                                            <td><?= $opfinalizada["OPREGISTRO"] ?></td>
                                                            <td><?= $opfinalizada["OPNOTIFICACIONCORREO"] ?></td>
                                                            <td><?= $opfinalizada["OPDIRECCIONLOCAL"] ?></td>
                                                            <td><?= $opfinalizada["OPPERESONACONTACTO"] ?></td>
                                                            <td><?= $opfinalizada["TELEFONO"] ?></td>
                                                            <td>
                                                                <?php
                                                                $reproseso = $opfinalizada["OPREPROSESO"];
                                                                switch ($reproseso) {
                                                                    case 0:
                                                                        echo " NO ES UN REPROSESO";
                                                                        break;
                                                                    case 1:
                                                                        echo "ES UN REPROSESO";
                                                                        break;
                                                                }
                                                                ?>
                                                            </td>
                                                            <td>
                                                                <?php
                                                                $estado = $opfinalizada["OPESTADO"];
                                                                switch ($estado) {
                                                                    case 1:
                                                                        echo "OP CREADA";
                                                                        break;
                                                                    case 2:
                                                                        echo "OP EN PRODUCCIÓN";
                                                                        break;
                                                                    case 3:
                                                                        echo "OP EN PAUSA";
                                                                        break;
                                                                    case 4:
                                                                        echo "OP ANULADA";
                                                                        break;
                                                                    case 5:
                                                                        echo "OP FINALIZADA";
                                                                        break;
                                                                    case 6:
                                                                        echo "";
                                                                        break;
                                                                }
                                                                ?>
                                                            </td>
                                                        </tr>
                                                    <?php endforeach ?>
                                                </tbody>
                                                </thead>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </section>
                    </div>
                    <div class="tab-pane fade" id="anulado" role="tabpanel" aria-labelledby="anulado-tab">
                        <section class="section">
                            <div class="row">
                                <div class="col-lg-12">
                                    <div class="card">
                                        <div class="card-body">
                                            <div class="card-header">
                                                <h5 class="card-title">Op's Anulados</h5>
                                            </div>
                                            <table class="table datatable">
                                                <thead>
                                                    <tr>
                                                        <th>Op</th>
                                                        <th>Cliente</th>
                                                        <th>Detalle</th>
                                                        <th>Diseñador</th>
                                                        <th>Vendedor</th>
                                                        <th>Hora de Registro</th>
                                                        <th>Hora de Notificacion</th>
                                                        <th>Dirección del Local</th>
                                                        <th>Persona de Contacto</th>
                                                        <th>Telefono de Contacto</th>
                                                        <th>Reproseso</th>
                                                        <th>Estado</th>
                                                    </tr>
                                                <tbody>
                                                    <?php foreach ($opanulada as $opanulada) : ?>
                                                        <tr>
                                                            <td><?= $opanulada["IDOP"] ?></td>
                                                            <td><?= $opanulada["OPCLIENTE"] ?></td>
                                                            <td><?= $opanulada["OPDETALLE"] ?></td>
                                                            <td><?= $opanulada["CEDULA_NOMBRES"] . " " . $opanulada["CEDULA_APELLIDOS"] ?></td>
                                                            <td><?= $opanulada["VENDEDOR_NOMBRES"] . " " . $opanulada["VENDEDOR_APELLIDOS"] ?></td>
                                                            <td><?= $opanulada["OPREGISTRO"] ?></td>
                                                            <td><?= $opanulada["OPNOTIFICACIONCORREO"] ?></td>
                                                            <td><?= $opanulada["OPDIRECCIONLOCAL"] ?></td>
                                                            <td><?= $opanulada["OPPERESONACONTACTO"] ?></td>
                                                            <td><?= $opanulada["TELEFONO"] ?></td>
                                                            <td>
                                                                <?php
                                                                $reproseso = $opanulada["OPREPROSESO"];
                                                                switch ($reproseso) {
                                                                    case 0:
                                                                        echo " NO ES UN REPROSESO";
                                                                        break;
                                                                    case 1:
                                                                        echo "ES UN REPROSESO";
                                                                        break;
                                                                }
                                                                ?>
                                                            </td>
                                                            <td>
                                                                <?php
                                                                $estado = $opanulada["OPESTADO"];
                                                                switch ($estado) {
                                                                    case 1:
                                                                        echo "OP CREADA";
                                                                        break;
                                                                    case 2:
                                                                        echo "OP EN PRODUCCIÓN";
                                                                        break;
                                                                    case 3:
                                                                        echo "OP EN PAUSA";
                                                                        break;
                                                                    case 4:
                                                                        echo "OP ANULADA";
                                                                        break;
                                                                    case 5:
                                                                        echo "OP FINALIZADA";
                                                                        break;
                                                                    case 6:
                                                                        echo "";
                                                                        break;
                                                                }
                                                                ?>
                                                            </td>
                                                        </tr>
                                                    <?php endforeach ?>
                                                </tbody>
                                                </thead>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </section>
                    </div>
                    <div class="tab-pane fade" id="op" role="tabpanel" aria-labelledby="op-tab">
                        <section class="section">
                            <div class="row">
                                <div class="col-lg-12">
                                    <div class="card">
                                        <div class="card-body">
                                            <div class="card-header">
                                                <h5 class="card-title">OP total</h5>
                                            </div>
                                            <table class="table datatable">
                                                <thead>
                                                    <tr>
                                                        <th>Op</th>
                                                        <th>Cliente</th>
                                                        <th>Detalle</th>
                                                        <th>Diseñador</th>
                                                        <th>Vendedor</th>
                                                        <th>Hora de Registro</th>
                                                        <th>Hora de Notificacion</th>
                                                        <th>Dirección del Local</th>
                                                        <th>Persona de Contacto</th>
                                                        <th>Telefono de Contacto</th>
                                                        <th>Reproseso</th>
                                                        <th>Estado</th>
                                                    </tr>
                                                <tbody>
                                                    <?php foreach ($optotal as $optotal) : ?>
                                                        <tr>
                                                            <td><?= $optotal["IDOP"] ?></td>
                                                            <td><?= $optotal["OPCLIENTE"] ?></td>
                                                            <td><?= $optotal["OPDETALLE"] ?></td>
                                                            <td><?= $optotal["CEDULA_NOMBRES"] . " " . $optotal["CEDULA_APELLIDOS"] ?></td>
                                                            <td><?= $optotal["VENDEDOR_NOMBRES"] . " " . $optotal["VENDEDOR_APELLIDOS"] ?></td>
                                                            <td><?= $optotal["OPREGISTRO"] ?></td>
                                                            <td><?= $optotal["OPNOTIFICACIONCORREO"] ?></td>
                                                            <td><?= $optotal["OPDIRECCIONLOCAL"] ?></td>
                                                            <td><?= $optotal["OPPERESONACONTACTO"] ?></td>
                                                            <td><?= $optotal["TELEFONO"] ?></td>
                                                            <td>
                                                                <?php
                                                                $reproseso = $optotal["OPREPROSESO"];
                                                                switch ($reproseso) {
                                                                    case 0:
                                                                        echo " NO ES UN REPROSESO";
                                                                        break;
                                                                    case 1:
                                                                        echo "ES UN REPROSESO";
                                                                        break;
                                                                }
                                                                ?>
                                                            </td>
                                                            <td>
                                                                <?php
                                                                $estado = $optotal["OPESTADO"];
                                                                switch ($estado) {
                                                                    case 1:
                                                                        echo "OP CREADA";
                                                                        break;
                                                                    case 2:
                                                                        echo "OP EN PRODUCCIÓN";
                                                                        break;
                                                                    case 3:
                                                                        echo "OP EN PAUSA";
                                                                        break;
                                                                    case 4:
                                                                        echo "OP ANULADA";
                                                                        break;
                                                                    case 5:
                                                                        echo "OP FINALIZADA";
                                                                        break;
                                                                    case 6:
                                                                        echo "";
                                                                        break;
                                                                }
                                                                ?>
                                                            </td>
                                                        </tr>
                                                    <?php endforeach ?>
                                                </tbody>
                                                </thead>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </section>
                    </div>
                </div>

            </div>
        </div>
    </div>
</section>
<?php require "./partials/footer.php"; ?>