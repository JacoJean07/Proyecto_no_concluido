<?php
require "../sql/database.php";
require "./partials/kardex.php";

session_start();

// Si la sesión no existe, redirigir al login.php y dejar de ejecutar el resto
if (!isset($_SESSION["user"])) {
    header("Location: ../login-form/login.php");
    return;
}

// Declaramos la variable $registros
$registros = null;

//BUSCAMOS EL DATO DEL USER PARA QUE SE IDENTIFIQUE
$usuario = $_SESSION["user"]["CEDULA"];

// Validamos los perfiles
if ($_SESSION["user"]["ROL"] == 2) {
    // Si el rol es 2 (Diseñador ADMIN), seleccionamos los registros donde el diseñador es el usuario actual, con información adicional de ORDENDISENIO
    $registros = $conn->prepare("SELECT R.*, O.CAMPANIA, O.MARCA, P.PERNOMBRES, P.PERAPELLIDOS 
                                    FROM REGISTROS R 
                                    JOIN ORDENDISENIO O ON R.PRODUCTO = O.PRODUCTO 
                                    JOIN PERSONAS P ON R.DISENIADOR = P.CEDULA
                                    ORDER BY ID DESC");
    $registros->execute();
} elseif ($_SESSION["user"]["ROL"] == 3) {
    // Si el rol es 3 (Diseñador), seleccionamos los registros donde el diseñador es el usuario actual, con información adicional de ORDENDISENIO
    $registros = $conn->prepare("SELECT (@row_number:=@row_number + 1) AS num, R.*, O.CAMPANIA, O.MARCA 
                                    FROM REGISTROS R 
                                    JOIN ORDENDISENIO O ON R.PRODUCTO = O.PRODUCTO 
                                    WHERE DISENIADOR = :usuario
                                    ORDER BY ID DESC");
    $registros->bindParam(":usuario", $usuario);
    $registros->execute();
} else {
    // Si el rol no es ninguno de los anteriores, redirigimos al usuario a la página de inicio
    header("Location:./index.php");
    return;
}

?>


<?php require "./partials/header.php"; ?>
<?php require "./partials/dashboard.php"; ?>

<section class="section">
    <div class="row">
        <div class="">
            <?php if (($_SESSION["user"]["ROL"]) && ($_SESSION["user"]["ROL"] == 3)) : ?>
                <section class="section">
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="card">
                                <div class="card-body">
                                    <div class="card-header">
                                        <h5 class="card-tittle">MIS REGISTROS</h5>
                                    </div>
                                    <h5 class="col-md-4 mx-auto mb-3"></h5>

                                    <?php if ($registros->rowCount() == 0) : ?>
                                        <div class="col-md-4 mx-auto mb-3">
                                            <div class="card card-body text-center">
                                                <p>No hay registros aún</p>
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
                                                    <th>HORA INICIO</th>
                                                    <th>HORA FINAL</th>
                                                    <th>OBSERVACIONES</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php $contador = $registros->rowCount(); ?>
                                                <?php foreach ($registros as $registros) : ?>
                                                    <tr>
                                                        <td><?= $contador-- ?></td>
                                                        <th><?= $registros["PRODUCTO"] ?></th>
                                                        <th><?= $registros["CAMPANIA"] ?></th>
                                                        <th><?= $registros["MARCA"] ?></th>
                                                        <td><?= $registros["HORA_INICIO"] ?></td>
                                                        <td><?= $registros["HORA_FINAL"] ?></td>
                                                        <td><?= $registros["OBSERVACIONES"] ?></td>
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
            <?php elseif (($_SESSION["user"]["ROL"]) && ($_SESSION["user"]["ROL"] == 2)) : ?>
                <section class="section">
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="card">
                                <div class="card-body">
                                    <div class="card-header">
                                        <h5 class="card-tittle">REGISTROS</h5>
                                    </div>
                                    <h5 class="col-md-4 mx-auto mb-3"></h5>

                                    <?php if ($registros->rowCount() == 0) : ?>
                                        <div class="col-md-4 mx-auto mb-3">
                                            <div class="card card-body text-center">
                                                <p>No hay registros aún</p>
                                            </div>
                                        </div>
                                    <?php else : ?>
                                        <!-- Table with stripped rows -->
                                        <table class="table datatable">
                                            <thead>
                                                <tr>
                                                    <th>#</th>
                                                    <th>DISEÑADOR</th>
                                                    <th>PRODUCTO</th>
                                                    <th>CAMPAÑA</th>
                                                    <th>MARCA</th>
                                                    <th>HORA INICIO</th>
                                                    <th>HORA FINAL</th>
                                                    <th>OBSERVACIONES</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($registros as $registros) : ?>

                                                    <tr>
                                                        <th><?= $registros["ID"] ?></th>
                                                        <th><?= $registros["PERNOMBRES"] . " " . $registros["PERAPELLIDOS"] ?></th>
                                                        <th><?= $registros["PRODUCTO"] ?></th>
                                                        <th><?= $registros["CAMPANIA"] ?></th>
                                                        <th><?= $registros["MARCA"] ?></th>
                                                        <td><?= $registros["HORA_INICIO"] ?></td>
                                                        <td><?= $registros["HORA_FINAL"] ?></td>
                                                        <td><?= $registros["OBSERVACIONES"] ?></td>
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
            <?php endif ?>
        </div>
    </div>
</section>

<?php require "./partials/footer.php"; ?>