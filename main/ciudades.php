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
$id = isset($_GET["id"]) ? $_GET["id"] : null; 
$usuarioEditar = null;

if ($_SESSION["user"]["ROL"] && $_SESSION["user"]["ROL"] == 1) {
    // Verificamos el método que usa el formulario con un if
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        // Validamos que no se manden datos vacíos
        if (empty($_POST["ciudad"])) {
            $error = "POR FAVOR RELLENA TODOS LOS CAMPOS";
        } else {
            // Verificamos si ya existe un registro para la ciudad actual
            $existingStatement = $conn->prepare("SELECT IDLUGAR FROM LUGARPRODUCCION WHERE CIUDAD = :ciudad");
            $existingStatement->execute([":ciudad" => $_POST['ciudad']]);
            $existingLugar = $existingStatement->fetch(PDO::FETCH_ASSOC);
        
            if ($existingLugar) {
                // Si existe, actualizamos el registro existente
                $statement = $conn->prepare("UPDATE LUGARPRODUCCION SET
                    CIUDAD = :ciudad,
                    USER_ID = :user_id
                    WHERE IDLUGAR = :id");
        
                $statement->execute([
                    ":id" => $existingLugar["IDLUGAR"],
                    ":ciudad" => $_POST["ciudad"],
                    ":user_id" => $_SESSION["user"]["ID_USER"],
                ]);
            } else {
                // No deberías llegar a este caso en la edición, pero por si acaso
                $error = "No se encontró la ciudad a editar.";
            }
        
            // Redirigimos a ciudades.php
            header("Location: ciudades.php");
            return;
        }
    }

    // Llamamos los lugares de producción de la base de datos
    $ciudades = $conn->query("SELECT * FROM LUGARPRODUCCION");
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
                <!-- Código para agregar una nueva ciudad -->
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Nuevo Lugar de Producción</h5>

                        <!-- si hay un error mandar un danger -->
                        <?php if ($error): ?> 
                            <p class="text-danger">
                                <?= $error ?>
                            </p>
                        <?php endif ?>
                        <form class="row g-3" method="POST" action="ciudades.php">
                            <div class="col-md-12">
                                <div class="form-floating mb-3">
                                    <input type="text" class="form-control" id="ciudad" name="ciudad" placeholder="Ciudad">
                                    <label for="ciudad">Ciudad</label>
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
                    <!-- Código para editar una ciudad existente -->
                    <?php 
                        $statement = $conn->prepare("SELECT * FROM LUGARPRODUCCION WHERE IDLUGAR = :id");
                        $statement->bindParam(":id", $id);
                        $statement->execute();
                        $ciudadEditar = $statement->fetch(PDO::FETCH_ASSOC);
                    ?>
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">Editar Ciudad de Produccion</h5>

                            <!-- si hay un error mandar un danger -->
                            <?php if ($error): ?> 
                                <p class="text-danger">
                                    <?= $error ?>
                                </p>
                            <?php endif ?>
                            <form class="row g-3" method="POST" action="ciudades.php">
                                <div class="col-md-12">
                                    <div class="form-floating mb-3">
                                        <!-- Cambiado a "ciudad_id" para evitar conflicto con $_GET["id"] -->
                                        <input type="hidden" name="ciudad_id" value="<?= $ciudadEditar["IDLUGAR"] ?>">
                                        <input value="<?= $ciudadEditar["CIUDAD"] ?>" type="text" class="form-control" id="ciudad" name="ciudad" placeholder="Ciudad">
                                        <label for="ciudad">Ciudad</label>
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
                                <h5 class="card-title">Ciudades de Produccion</h5>
                                <!-- si el array asociativo $ciudades no tiene nada dentro, entonces imprimir el siguiente div -->
                                <?php if ($ciudades->rowCount() == 0): ?>
                                    <div class= "col-md-4 mx-auto mb-3">
                                        <div class= "card card-body text-center">
                                            <p>No hay Ciudades de Produccion Aun.</p>
                                        </div>
                                    </div>
                                <?php else: ?>
                                    <!-- Table with stripped rows -->
                                    <table class="table datatable">
                                        <thead>
                                            <tr>
                                                <th>CIUDAD</th>
                                                <th></th>
                                                <th></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($ciudades as $ciudad): ?>
                                                <tr>
                                                    <th><?= $ciudad["CIUDAD"]?></th>
                                                    <td>
                                                        <a href="ciudades.php?id=<?= $ciudad["IDLUGAR"] ?>" class="btn btn-secondary mb-2">Editar</a>
                                                    </td>
                                                    <td>
                                                        <a href="#" class="btn btn-danger mb-2">Eliminar</a>
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
