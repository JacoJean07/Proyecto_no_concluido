<?php
require "../sql/database.php";

session_start();

// Si la sesión no existe, redirigir al login.php y dejar de ejecutar el resto
if (!isset($_SESSION["user"])) {
    header("Location: ../login-form/login.php");
    return;
}

// Declaramos la variable error que nos ayudará a mostrar errores, etc.
$error = null;
$state = 1;
$id = isset($_GET["id"]) ? $_GET["id"] : null; 
$personaEditar = null;

if (($_SESSION["user"]["ROL"]) && ($_SESSION["user"]["ROL"] == 1)) {
    // Llamar los contactos de la base de datos y especificar que sean los que tengan el persona_id de la función session_start
    $personas = $conn->query("SELECT * FROM personas WHERE PERESTADO = 0");

} else {
    header("Location: ./index.php");
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
                                <h5 class="card-title">Trabajadores</h5>
                                <!-- si el array asociativo $teachers no tiene nada dentro, entonces imprimir el siguiente div -->
                                <?php if ($personas->rowCount() == 0): ?>
                                    <div class= "col-md-4 mx-auto mb-3">
                                        <div class= "card card-body text-center">
                                            <p>No hay Trabajadores Eliminados Aun.</p>
                                        </div>
                                    </div>
                                <?php else: ?>
                                <!-- Table with stripped rows -->
                                <table class="table datatable">
                                    <thead>
                                    <tr>
                                        <th>Apellidos</th>
                                        <th>Nombres</th>
                                        <th>Cedula</th>
                                        <th>Edad</th>
                                        <th>Area de Trabajo</th>
                                        <th></th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <?php foreach ($personas as $persona): ?>
                                        <tr>
                                        <th><?= $persona["PERAPELLIDOS"]?></th>
                                        <td><?= $persona["PERNOMBRES"]?></td>
                                        <td><?= $persona["CEDULA"]?></td>
                                        <td>
                                            <?php
                                            // Calcular la edad a partir de la fecha de nacimiento
                                            $birthdate = new DateTime($persona["PERFECHANACIMIENTO"]);
                                            $today = new DateTime();
                                            $age = $today->diff($birthdate)->y;
                                            echo $age;
                                            ?>
                                        </td>
                                        <td><?= $persona["PERAREATRABAJO"]?></td>
                                        <td>
                                            <a href="./restaurar/personas.php?id=<?= $persona["CEDULA"] ?>" class="btn btn-danger mb-2">Restaurar</a>
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
