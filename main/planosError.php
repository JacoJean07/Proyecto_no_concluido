<?php
require "../sql/database.php";
require "./partials/kardex.php";

session_start();

// Si la sesión no existe, redirigir al login.php y dejar de ejecutar el resto
if (!isset($_SESSION["user"])) {
    header("Location: ../login-form/login.php");
    return;
}

// Obtener todos los planos con PLANOTIFICACION = 1 y la información de la OP asociada
$opPlanosStatement = $conn->query("SELECT P.*, O.OPCLIENTE, O.OPDETALLE FROM PLANOS P JOIN OP O ON P.IDOP = O.IDOP WHERE P.PLANOTIFICACION = 1");
$opPlanos = $opPlanosStatement->fetchAll(PDO::FETCH_ASSOC);
?>

<?php require "./partials/header.php"; ?>
<?php require "./partials/dashboard.php"; ?>
<section class="section">
    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Planos con Notificación</h5>
                    
                    <?php if (empty($opPlanos)): ?>
                        <div class="col-md-4 mx-auto mb-3">
                            <div class="card card-body text-center">
                                <p>No hay planos con notificación</p>
                            </div>
                        </div>
                    <?php else: ?>
                        <!-- Table with stripped rows -->
                        <table class="table datatable">
                            <thead>
                                <tr>
                                    <th>Número de Plano</th>
                                    <th>OP</th>
                                    <th>Cliente</th>
                                    <th>Detalle de la OP</th>
                                    <th>Estado</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($opPlanos as $opPlano): ?>
                                    <tr>
                                        <td><?= $opPlano["PLANNUMERO"] ?></td>
                                        <td><?= $opPlano["IDOP"] ?></td>
                                        <td><?= $opPlano["OPCLIENTE"] ?></td>
                                        <td><?= $opPlano["OPDETALLE"] ?></td>
                                        <td>
                                            <?php
                                                if ($opPlano["PLAESTADO"] == 1 ) {
                                                    echo("Activo");
                                                } elseif ($opPlano["PLAESTADO"] == 2 ) {
                                                    echo("Pausado");
                                                } elseif ($opPlano["PLAESTADO"] == 3 ) {
                                                    echo("Anulado");
                                                } 
                                            ?>
                                        </td>
                                        <td>
                                            <?php if($opPlano["PLANOTIFICACION"] == 1 ) : ?>
                                                <a href="./validaciones/notiPlanoError.php?id=<?= $opPlano["IDPLANO"] ?>" class="btn btn-success mb-2">Quitar notificación</a>
                                            <?php endif ?>
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

<?php require "./partials/footer.php"; ?>
