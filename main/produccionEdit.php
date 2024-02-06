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

// Verificar si se ha enviado un ID de producción para editar
if (isset($_GET["id"])) {
    // Obtener el ID de producción desde el parámetro GET
    $idProduccion = $_GET["id"];

    // Consultar los datos de producción correspondientes al ID proporcionado
    $produccionStatement = $conn->prepare("SELECT * FROM PRODUCCION WHERE IDPRODUCION = :idproduccion");
    $produccionStatement->execute([":idproduccion" => $idProduccion]);
    $produccionData = $produccionStatement->fetch(PDO::FETCH_ASSOC);

    // Si no se encuentra ningún registro de producción, mostrar un mensaje de error
    if (!$produccionData) {
        $error = "No se encontró ningún registro de producción con el ID proporcionado.";
    }
}

// Verificar si se ha enviado un formulario para actualizar los datos de producción
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["idproduccion"])) {
    // Obtener los datos del formulario
    $idPlano = $_POST["idplano"];
    $observaciones = $_POST["proobservaciones"];

    // Actualizar los datos del registro de producción
    $updateStatement = $conn->prepare("UPDATE PRODUCCION SET IDPLANO = :idplano, PROOBSERVACIONES = :observaciones WHERE IDPRODUCION = :idproduccion");
    $updateStatement->execute([
        ":idplano" => $idPlano,
        ":observaciones" => $observaciones,
        ":idproduccion" => $_POST["idproduccion"]
    ]);

    // Redirigir a la página de producción
    header("Location: produccion.php");
    exit();
}
?>

<?php require "./partials/header.php"; ?>
<?php require "./partials/dashboard.php"; ?>

<section class="section">
    <div class="row">
        <div class="">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Editar Registro de Producción</h5>

                    <!-- Mostrar mensaje de error si existe -->
                    <?php if ($error): ?>
                        <p class="text-danger"><?= $error ?></p>
                    <?php endif; ?>

                    <!-- Mostrar el formulario de edición -->
                    <form class="row g-3" method="POST" action="produccionEdit.php">
                        <input type="hidden" name="idproduccion" value="<?= $produccionData["IDPRODUCION"] ?>">
                        <div class="col-md-6">
                            <div class="form-floating mb-3">
                                <select class="form-select" id="idplano" name="idplano">
                                    <!-- Aquí puedes llenar las opciones del select con los datos necesarios -->
                                </select>
                                <label for="idplano">Seleccionar Plano</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-floating mb-3">
                                <input type="text" class="form-control" id="proobservaciones" name="proobservaciones" placeholder="Observaciones" value="<?= $produccionData["PROOBSERVACIONES"] ?>">
                                <label for="proobservaciones">Observaciones</label>
                            </div>
                        </div>
                        <div class="text-center">
                            <button type="submit" class="btn btn-primary">Actualizar</button>
                            <a href="produccion.php" class="btn btn-secondary">Cancelar</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>

<?php require "./partials/footer.php"; ?>
