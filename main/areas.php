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
$id = isset($_GET["id"]) ? $_GET["id"] : null; 
$areaEditar = null;

if ($_SESSION["user"]["ROL"] && $_SESSION["user"]["ROL"] == 1) {
    // Verificamos el método que usa el formulario con un if
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        // Validamos que no se manden datos vacíos
        if (empty($_POST["area"])) {
            $error = "POR FAVOR RELLENA TODOS LOS CAMPOS";
        } else {
            // Verificamos si ya existe un registro para el área actual
            $existingStatement = $conn->prepare("SELECT IDAREA FROM AREAS WHERE IDAREA = :id");
            $existingStatement->execute([":id" => $id]);
            $existingArea = $existingStatement->fetch(PDO::FETCH_ASSOC);
        
            if ($existingArea) {
                // Si existe, actualizamos el registro existente
                $statement = $conn->prepare("UPDATE AREAS SET AREDETALLE = :area WHERE IDAREA = :id");
                $statement->execute([
                    ":id" => $id,
                    ":area" => $_POST["area"],
                ]);

                // Registramos el movimiento en el kardex
                registrarEnKardex($_SESSION["user"]["ID_USER"], "EDITO", 'AREAS', $_POST["area"]);

            } else {
                // Si no existe, insertamos un nuevo registro
                $statement = $conn->prepare("INSERT INTO AREAS (AREDETALLE) VALUES (:area)");
        
                $statement->execute([
                    ":area" => $_POST["area"],
                ]);
                // Registramos el movimiento en el kardex
                registrarEnKardex($_SESSION["user"]["ID_USER"], "CREO", 'AREAS', $_POST["area"]);
            }
        
            // Redirigimos a areas.php
            header("Location: areas.php");
            return;
        }
    }

    // Llamamos las áreas de la base de datos
    $areas = $conn->query("SELECT * FROM AREAS");

    // Obtenemos la información del área a editar
    $statement = $conn->prepare("SELECT * FROM AREAS WHERE IDAREA = :id");
    $statement->bindParam(":id", $id);
    $statement->execute();
    $areaEditar = $statement->fetch(PDO::FETCH_ASSOC);

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
            <?php if (empty($id)) : ?>
                <!-- Código para agregar una nueva área -->
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Nueva Área</h5>

                        <!-- si hay un error mandar un danger -->
                        <?php if ($error): ?> 
                            <p class="text-danger">
                                <?= $error ?>
                            </p>
                        <?php endif ?>
                        <form class="row g-3" method="POST" action="areas.php">
                            <div class="col-md-12">
                                <div class="form-floating mb-3">
                                    <input type="text" class="form-control" id="area" name="area" placeholder="Área">
                                    <label for="area">Área</label>
                                </div>
                            </div>
                            <div class="text-center">
                                <button type="submit" class="btn btn-primary">Submit</button>
                                <button type="reset" class="btn btn-secondary">Reset</button>
                            </div>
                        </form>
                    </div>
                </div>
            <?php else : ?>
                <!-- Código para editar un área existente -->
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Editar Área</h5>

                        <!-- si hay un error mandar un danger -->
                        <?php if ($error): ?> 
                            <p class="text-danger">
                                <?= $error ?>
                            </p>
                        <?php endif ?>
                        <form class="row g-3" method="POST" action="areas.php?id=<?= $id ?>">
                            <div class="col-md-12">
                                <div class="form-floating mb-3">
                                    <input type="text" class="form-control" id="area" name="area" placeholder="Área" value="<?= $areaEditar["AREDETALLE"] ?>">
                                    <label for="area">Área</label>
                                </div>
                            </div>
                            <div class="text-center">
                                <button type="submit" class="btn btn-primary">Actualizar</button>
                                <button type="reset" class="btn btn-secondary">Reset</button>
                            </div>
                        </form>
                    </div>
                </div>
            <?php endif ?>

            <section class="section">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title">Áreas</h5>
                                <!-- si el array asociativo $areas no tiene nada dentro, entonces imprimir el siguiente div -->
                                <?php if ($areas->rowCount() == 0): ?>
                                    <div class= "col-md-4 mx-auto mb-3">
                                        <div class= "card card-body text-center">
                                            <p>No hay Áreas aún.</p>
                                        </div>
                                    </div>
                                <?php else: ?>
                                    <!-- Table with stripped rows -->
                                    <table class="table datatable">
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>ÁREA</th>
                                                <th></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($areas as $area): ?>
                                                <tr>
                                                    <th><?= $area["IDAREA"] ?></th>
                                                    <th><?= $area["AREDETALLE"] ?></th>
                                                    <td>
                                                        <a href="areas.php?id=<?= $area["IDAREA"] ?>" class="btn btn-secondary mb-2">Editar</a>
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
