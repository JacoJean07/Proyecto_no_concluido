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
$query = "SELECT * FROM ORDENDISENIO";

// Si hay un estado filtrado, agregarlo a la consulta
if ($estado_filter !== null) {
    $query .= " WHERE ESTADO = :estado";
}

// Preparar y ejecutar la consulta
$ordenes_disenio = $conn->prepare($query);

// Si hay un estado filtrado, bindear el parámetro y ejecutar la consulta
if ($estado_filter !== null) {
    $ordenes_disenio->bindParam(':estado', $estado_filter, PDO::PARAM_INT);
}

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
                                    <!-- Botón para exportar a Excel con ícono desde la carpeta exel y estilizado con Bootstrap -->
                                    <a href="./reporte_exel/exel_op.php" class="btn btn-success btn-xs">
                                                        <img src="../exel/exel_icon.png" alt="Icono Excel" class="me-1" style="width: 25px; height: 25px;">
                                                        Exportar a Excel
                                                    </a>
                                </div>

                                <!-- Filtro de estado -->
                                <div class="row mt-3">
                                    <div class="col-md-4">
                                        <form method="GET">
                                            <div class="form-group">
                                                <select class="form-control" name="estado" id="estado">
                                                    <option selected disabled value="">Selecciona un estado</option>
                                                    <option value="1" <?php if ($estado_filter === 1) echo 'selected'; ?>>Aprobada</option>
                                                    <option value="2" <?php if ($estado_filter === 2) echo 'selected'; ?>>En Diseño</option>
                                                    <option value="3" <?php if ($estado_filter === 3) echo 'selected'; ?>>Desaprobada</option>
                                                    <option value="4" <?php if ($estado_filter === 4) echo 'selected'; ?>>Revisando</option>
                                                </select>
                                            </div>
                                            <button type="submit" class="btn btn-primary m-2">Filtrar</button>
                                            <a class="btn btn-primary" href="./historialOd.php">Ver todos los registros</a>
                                        </form>
                                    </div>
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
                                                        <a href="detallesOd.php?id=<?= $orden["PRODUCTO"] ?>" class="btn btn-primary mb-2">Ver Registros</a>
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
