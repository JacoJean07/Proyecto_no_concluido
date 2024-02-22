<?php
require "../sql/database.php";
require "./partials/kardex.php";

session_start();

// Si la sesión no existe, redirigir al login.php y dejar de ejecutar el resto
if (!isset($_SESSION["user"])) {
    header("Location: ../login-form/login.php");
    return;
}
if (($_SESSION["user"]["ROL"] != 2)) {
    header("Location: ../index.php");
    return;
}

// Obtener el ID de la orden de diseño desde la URL
$id_orden_disenio = $_GET["id"];

// Obtener la información de la orden de diseño
$statement_od = $conn->prepare("SELECT OD.*, P.PERNOMBRES, P.PERAPELLIDOS 
                                    FROM ORDENDISENIO OD 
                                    JOIN PERSONAS P ON OD.RESPONSABLE_CEDULA = P.CEDULA
                                    WHERE PRODUCTO = :id");
$statement_od->execute([":id" => $id_orden_disenio]);
$orden_disenio = $statement_od->fetch(PDO::FETCH_ASSOC);

// Obtener los registros asociados a la orden de diseño
$registros = $conn->prepare("SELECT R.*, O.CAMPANIA, O.MARCA, P.PERNOMBRES, P.PERAPELLIDOS
                                FROM REGISTROS R 
                                JOIN ORDENDISENIO O ON R.PRODUCTO = O.PRODUCTO 
                                JOIN PERSONAS P ON R.DISENIADOR = P.CEDULA
                                WHERE R.PRODUCTO = :id
                                ORDER BY R.ID DESC");
$registros->execute([":id" => $id_orden_disenio]);

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
                                <!-- Mostrar información de la orden de diseño -->
                                <div class="card-header">
                                    <h5 class="card-tittle">Detalles de la Orden de Diseño</h5>
                                </div>
                                <div class="card-body">
                                    <p><strong>Producto:</strong> <?php echo $orden_disenio["PRODUCTO"]; ?></p>
                                    <p><strong>Responsable:</strong> <?php echo $orden_disenio["PERNOMBRES"] . " ". $orden_disenio["PERAPELLIDOS"]; ?></p>
                                    <p><strong>Campania:</strong> <?php echo $orden_disenio["CAMPANIA"]; ?></p>
                                    <p><strong>Marca:</strong> <?php echo $orden_disenio["MARCA"]; ?></p>
                                    <p><strong>Fecha de Entrega:</strong> <?php echo $orden_disenio["FECHAENTREGA"]; ?></p>
                                    <p><strong>Estado:</strong> <?php echo ($orden_disenio["ESTADO"] == 1) ? 'Aprobada' : (($orden_disenio["ESTADO"] == 2) ? 'En Diseño' : (($orden_disenio["ESTADO"] == 3) ? 'Desaprobada' : 'Revisando')); ?></p>
                                </div>

                                <!-- Tabla con los registros asociados a la orden de diseño -->
                                <div class="card-header">
                                    <h5 class="card-tittle">Registros de la Orden de Diseño</h5>
                                </div>
                                <div class="card-body">
                                    <table class="table datatable">
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>Diseñador</th>
                                                <th>Hora de Inicio</th>
                                                <th>Hora Final</th>
                                                <th>Observaciones</th>
                                                <th>Rol</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php $contador = $registros->rowCount(); ?>
                                            <?php foreach ($registros as $registro) : ?>
                                                <tr>
                                                    <td><?= $contador-- ?></td>
                                                    <td><?php echo $registro["PERNOMBRES"] . " " . $registro["PERAPELLIDOS"]; ?></td>
                                                    <td><?php echo $registro["HORA_INICIO"]; ?></td>
                                                    <td><?php echo $registro["HORA_FINAL"]; ?></td>
                                                    <td><?php echo $registro["OBSERVACIONES"]; ?></td>
                                                    <td><?php echo ($registro["DISENIADOR"] == $orden_disenio["RESPONSABLE_CEDULA"]) ? 'Responsable' : 'Colaborador'; ?></td>
                                                </tr>
                                            <?php endforeach ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </div>
</section>

<?php require "./partials/footer.php"; ?>
