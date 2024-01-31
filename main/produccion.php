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
$produccionEditar = null;

if ($_SESSION["user"]["ROL"] && $_SESSION["user"]["ROL"] == 1) {
    // Obtener opciones para IDPLANO desde la base de datos
    $planos = $conn->query("SELECT IDPLANO, NOMBRE_PLANO FROM PLANOS");

    // Obtener opciones para IDAREA desde la base de datos
    $areas = $conn->query("SELECT IDAREA, AREDETALLE FROM AREAS");

    // Verificamos el método que usa el formulario con un if
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        // Validamos que no se manden datos vacíos
        if (empty($_POST["idplano"]) || empty($_POST["idarea"]) || empty($_POST["observaciones"]) || empty($_POST["porcentaje"]) || empty($_POST["fecha"])) {
            $error = "POR FAVOR RELLENA TODOS LOS CAMPOS";
        } else {
            // Verificamos si ya existe un registro para la producción actual
            $existingStatement = $conn->prepare("SELECT IDPRODUCION FROM PRODUCCION WHERE IDPRODUCION = :id");
            $existingStatement->execute([":id" => $id]);
            $existingProduccion = $existingStatement->fetch(PDO::FETCH_ASSOC);
        
            if ($existingProduccion) {
                // Si existe, actualizamos el registro existente
                $statement = $conn->prepare("UPDATE PRODUCCION SET IDPLANO = :idplano, IDAREA = :idarea, PROOBSERVACIONES = :observaciones, 
                                            PROPORCENTAJE = :porcentaje, PROFECHA = :fecha WHERE IDPRODUCION = :id");
                $statement->execute([
                    ":id" => $id,
                    ":idplano" => $_POST["idplano"],
                    ":idarea" => $_POST["idarea"],
                    ":observaciones" => $_POST["observaciones"],
                    ":porcentaje" => $_POST["porcentaje"],
                    ":fecha" => $_POST["fecha"],
                ]);
                // Registramos el movimiento en el kardex
                registrarEnKardex($_SESSION["user"]["ID_USER"], $_SESSION["user"]["USER"], "EDITO", 'PRODUCCION', $id);
            } else {
                // Si no existe, insertamos un nuevo registro
                $statement = $conn->prepare("INSERT INTO PRODUCCION (IDPLANO, IDAREA, PROOBSERVACIONES, PROPORCENTAJE, PROFECHA) 
                                              VALUES (:idplano, :idarea, :observaciones, :porcentaje, :fecha)");
        
                $statement->execute([
                    ":idplano" => $_POST["idplano"],
                    ":idarea" => $_POST["idarea"],
                    ":observaciones" => $_POST["observaciones"],
                    ":porcentaje" => $_POST["porcentaje"],
                    ":fecha" => $_POST["fecha"],
                ]);
                // Registramos el movimiento en el kardex
                registrarEnKardex($_SESSION["user"]["ID_USER"], $_SESSION["user"]["USER"], "CREO", 'PRODUCCION', $id);
            }
        
            // Redirigimos a produccion.php
            header("Location: produccion.php");
            return;
        }
    }

    // Llamamos las producciones de la base de datos
    $producciones = $conn->query("SELECT * FROM PRODUCCION");

    // Obtenemos la información de la producción a editar
    $statement = $conn->prepare("SELECT * FROM PRODUCCION WHERE IDPRODUCION = :id");
    $statement->bindParam(":id", $id);
    $statement->execute();
    $produccionEditar = $statement->fetch(PDO::FETCH_ASSOC);

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
                <!-- Código para agregar una nueva producción -->
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Registro de Producción</h5>

                        <!-- si hay un error mandar un danger -->
                        <?php if ($error): ?> 
                            <p class="text-danger">
                                <?= $error ?>
                            </p>
                        <?php endif ?>
                        <form class="row g-3" method="POST" action="produccion.php">
                            <div class="col-md-6">
                                <label for="idplano" class="form-label">ID Plano</label>
                                <select class="form-select" id="idplano" name="idplano">
                                    <?php foreach ($planos as $plano): ?>
                                        <option value="<?= $plano["IDPLANO"] ?>"><?= $plano["NOMBRE_PLANO"] ?></option>
                                    <?php endforeach ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="idarea" class="form-label">ID Área</label>
                                <select class="form-select" id="idarea" name="idarea">
                                    <?php foreach ($areas as $area): ?>
                                        <option value="<?= $area["IDAREA"] ?>"><?= $area["AREDETALLE"] ?></option>
                                    <?php endforeach ?>
                                </select>
                            </div>
                            <div class="col-md-12">
                                <div class="form-floating mb-3">
                                    <textarea class="form-control" id="observaciones" name="observaciones" placeholder="Observaciones"></textarea>
                                    <label for="observaciones">Observaciones</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-floating mb-3">
                                    <input type="number" class="form-control" id="porcentaje" name="porcentaje" placeholder="Porcentaje">
                                    <label for="porcentaje">Porcentaje</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-floating mb-3">
                                    <input type="datetime-local" class="form-control" id="fecha" name="fecha">
                                    <label for="fecha">Fecha</label>
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
                <!-- Código para editar una producción existente -->
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Editar Producción</h5>

                        <!-- si hay un error mandar un danger -->
                        <?php if ($error): ?> 
                            <p class="text-danger">
                                <?= $error ?>
                            </p>
                        <?php endif ?>
                        <form class="row g-3" method="POST" action="produccion.php?id=<?= $id ?>">
                            <div class="col-md-6">
                                <label for="idplano" class="form-label">ID Plano</label>
                                <select class="form-select" id="idplano" name="idplano">
                                    <?php foreach ($planos as $plano): ?>
                                        <option value="<?= $plano["IDPLANO"] ?>" <?= ($plano["IDPLANO"] == $produccionEditar["IDPLANO"]) ? "selected" : "" ?>><?= $plano["NOMBRE_PLANO"] ?></option>
                                    <?php endforeach ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="idarea" class="form-label">ID Área</label>
                                <select class="form-select" id="idarea" name="idarea">
                                    <?php foreach ($areas as $area): ?>
                                        <option value="<?= $area["IDAREA"] ?>" <?= ($area["IDAREA"] == $produccionEditar["IDAREA"]) ? "selected" : "" ?>><?= $area["AREDETALLE"] ?></option>
                                    <?php endforeach ?>
                                </select>
                            </div>
                            <div class="col-md-12">
                                <div class="form-floating mb-3">
                                    <textarea class="form-control" id="observaciones" name="observaciones" placeholder="Observaciones"><?= $produccionEditar["PROOBSERVACIONES"] ?></textarea>
                                    <label for="observaciones">Observaciones</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-floating mb-3">
                                    <input type="number" class="form-control" id="porcentaje" name="porcentaje" placeholder="Porcentaje" value="<?= $produccionEditar["PROPORCENTAJE"] ?>">
                                    <label for="porcentaje">Porcentaje</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-floating mb-3">
                                    <input type="datetime-local" class="form-control" id="fecha" name="fecha" value="<?= date("Y-m-d\TH:i", strtotime($produccionEditar["PROFECHA"])) ?>">
                                    <label for="fecha">Fecha</label>
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
                                <h5 class="card-title">Producciones</h5>
                                <!-- si el array asociativo $producciones no tiene nada dentro, entonces imprimir el siguiente div -->
                                <?php if ($producciones->rowCount() == 0): ?>
                                    <div class= "col-md-4 mx-auto mb-3">
                                        <div class= "card card-body text-center">
                                            <p>No hay Producciones aún.</p>
                                        </div>
                                    </div>
                                <?php else: ?>
                                    <!-- Table with stripped rows -->
                                    <table class="table datatable">
                                        <thead>
                                            <tr>
                                                <th>IDPLANO</th>
                                                <th>IDAREA</th>
                                                <th>OBSERVACIONES</th>
                                                <th>PORCENTAJE</th>
                                                <th>FECHA</th>
                                                <th></th>
                                                <th></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($producciones as $produccion): ?>
                                                <tr>
                                                    <th><?= $produccion["IDPLANO"] ?></th>
                                                    <th><?= $produccion["IDAREA"] ?></th>
                                                    <th><?= $produccion["PROOBSERVACIONES"] ?></th>
                                                    <th><?= $produccion["PROPORCENTAJE"] ?></th>
                                                    <th><?= date("d-m-Y H:i", strtotime($produccion["PROFECHA"])) ?></th>
                                                    <td>
                                                        <a href="produccion.php?id=<?= $produccion["IDPRODUCION"] ?>" class="btn btn-secondary mb-2">Editar</a>
                                                    </td>
                                                    <td>
                                                        <a href="delete/produccion.php?id=<?= $produccion["IDPRODUCION"] ?>" class="btn btn-danger mb-2">Eliminar</a>
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
