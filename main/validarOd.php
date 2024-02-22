<?php
require "../sql/database.php";
require "./partials/kardex.php";

session_start();

// Si la sesión no existe, redirigir al login.php y dejar de ejecutar el resto
if (!isset($_SESSION["user"])) {
    header("Location: ../login-form/login.php");
    return;
}

// Validamos los perfiles
if ($_SESSION["user"]["ROL"] != 2) {
    // Si el rol no es 2 (Diseñador ADMIN), redirigimos al usuario a la página de inicio
    header("Location:./index.php");
    return;
}

// Obtener el estado del filtro si está presente
$estado_filter = isset($_GET['estado']) ? intval($_GET['estado']) : null;

// Preparar la consulta base
$query = "SELECT * FROM ORDENDISENIO WHERE ESTADO = 4";

// Preparar y ejecutar la consulta
$ordenes_disenio = $conn->prepare($query);

$ordenes_disenio->execute();

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
                                    <h5 class="card-tittle">ORDENES DE DISEÑO</h5>
                                </div>
                                <?php if ($ordenes_disenio->rowCount() == 0) : ?>
                                    <div class="col-md-4 mx-auto mb-3">
                                        <div class="card card-body text-center">
                                            <p>No hay órdenes de diseño</p>
                                        </div>
                                    </div>
                                <?php else : ?>
                                    <!-- Table with stripped rows -->
                                    <table class="table datatable">
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>PRODUCTO</th>
                                                <th>CAMPAÑA</th>
                                                <th>MARCA</th>
                                                <th>FECHA DE ENTREGA</th>
                                                <th>ESTADO</th>
                                                <th></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($ordenes_disenio as $orden) : ?>
                                                <tr>
                                                    <th><?= $orden["ID"] ?></th>
                                                    <th><?= $orden["PRODUCTO"] ?></th>
                                                    <th><?= $orden["CAMPANIA"] ?></th>
                                                    <th><?= $orden["MARCA"] ?></th>
                                                    <th><?= $orden["FECHAENTREGA"] ?></th>
                                                    <td>
                                                        <?php
                                                        switch ($orden["ESTADO"]) {
                                                            case 1:
                                                                echo 'Aprobada';
                                                                break;
                                                            case 2:
                                                                echo 'En Diseño';
                                                                break;
                                                            case 3:
                                                                echo 'Desaprobada';
                                                                break;
                                                            case 4:
                                                                echo 'Revisando';
                                                                break;
                                                            default:
                                                                echo 'Desconocido';
                                                        }
                                                        ?>
                                                    </td>
                                                    <td>
                                                        <a href="validaciones/odAprovar.php?id=<?= $orden["PRODUCTO"] ?>" class="btn btn-primary mb-2">Aprovar OD</a>
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
