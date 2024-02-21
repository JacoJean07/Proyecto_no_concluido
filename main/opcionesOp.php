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
                        <button class="nav-link active" id="estado-tab" data-bs-toggle="tab" data-bs-target="#estado" type="button" role="tab" aria-controls="estado" aria-selected="true">Cambio de los etados de las OP</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="finalizada-tab" data-bs-toggle="tab" data-bs-target="#finalizado" type="button" role="tab" aria-controls="finalizado" aria-selected="false" tabindex="-1">Op's Finalizadas</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="anulado-tab" data-bs-toggle="tab" data-bs-target="#anulado" type="button" role="tab" aria-controls="anulado" aria-selected="false" tabindex="-2">Op's Anuladas</button>
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
                                                                <?php
                                                                $reprosoo = $op["OPREPROSESO"];
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
                                                            <td>
                                                                <?php if ($op["OPESTADO"] != 3) : ?>
                                                                    <button type="button" class="btn btn-success mb-2" data-bs-toggle="modal" data-bs-target="#pausar">Pausar</button>
                                                                    <div class="modal fade" id="pausar" tabindex="-1" style="display: none;" aria-modal="true" role="dialog">
                                                                        <div class="modal-dialog modal-dialog-centered">
                                                                            <div class="modal-content">
                                                                                <div class="modal-header">
                                                                                    <h5 class="modal-title">Pausar OP</h5>
                                                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                                                </div>
                                                                                <div class="modal-body">
                                                                                    "pausar op."
                                                                                </div>
                                                                                <div class="modal-footer">
                                                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                                                                    <a href="./cambiosEstadoOp/pausarOp.php?id=<?= $op["IDOP"] ?>" class="btn btn-success mb-2">Pausar</a>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                <?php else : ?>
                                                                    <button type="button" class="btn btn-primary mb-2" data-bs-toggle="modal" data-bs-target="#activar">Activar</button>
                                                                    <div class="modal fade" id="anular-<?= $op["IDOP"] ?>" tabindex="-1" style="display: none;" aria-modal="true" role="dialog">
                                                                        <div class="modal-dialog modal-dialog-centered">
                                                                            <div class="modal-content">
                                                                                <div class="modal-header">
                                                                                    <h5 class="modal-title">Activar OP</h5>
                                                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                                                </div>
                                                                                <div class="modal-body">
                                                                                    <p>activarOp</p>
                                                                                </div>
                                                                                <div class="modal-footer">
                                                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                                                                    <a href="./cambiosEstadoOp/activarOp.php?id=<?= $op["IDOP"] ?>" class="btn btn-primary mb-2">Activar</a>
                                                                                </div>

                                                                            </div>
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
                                                                                "kjsadfkasjdfasjalsfpdklafjsdfjosdtivgdfj"
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
                                            <table>
                                                <thead>
                                                    <th>Op</th>
                                                    <th>Cliente</th>
                                                    <th>Diseñador</th>
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
                                            <table>
                                                <thead>
                                                    <th>Op</th>
                                                    <th>Cliente</th>
                                                    <th>Diseñador</th>
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