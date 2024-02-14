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
$idproduccion = isset($_GET["id"]) ? $_GET["id"] : null; // Cambié "idop" por "id"
$produccionInfo = null;

// Verificamos si se ha proporcionado un ID de producción válido
if ($idproduccion) {
    // Consultamos la información de producción
    $produccionInfoStatement = $conn->prepare("SELECT * FROM PRODUCCION WHERE IDPRODUCION = :idproduccion");
    $produccionInfoStatement->bindParam(":idproduccion", $idproduccion);
    $produccionInfoStatement->execute();
    $produccionInfo = $produccionInfoStatement->fetch(PDO::FETCH_ASSOC);
}

// Verificamos el método que usa el formulario con un if
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Verificar que se han enviado los datos necesarios
    if (isset($_POST["idproduccion"]) && isset($_POST["idplano"]) && isset($_POST["proobservaciones"]) && isset($_POST["areatrabajo"])) {
        // Obtener datos del formulario
        $idproduccion = $_POST["idproduccion"];
        $idplano = $_POST["idplano"];
        $proobservaciones = $_POST["proobservaciones"];
        $areatrabajo = $_POST["areatrabajo"];

        // Actualizar datos de producción en la tabla PRODUCCION
        $updateStatement = $conn->prepare("UPDATE PRODUCCION SET IDPLANO = :idplano, PROOBSERVACIONES = :proobservaciones WHERE IDPRODUCION = :idproduccion");
        $updateStatement->execute([
            ":idplano" => $idplano,
            ":proobservaciones" => $proobservaciones,
            ":idproduccion" => $idproduccion
        ]);

        // Eliminar las áreas asociadas actuales
        $deleteAreasStatement = $conn->prepare("DELETE FROM AREAS WHERE IDPRODUCION = :idproduccion");
        $deleteAreasStatement->bindParam(":idproduccion", $idproduccion);
        $deleteAreasStatement->execute();

        // Insertar las nuevas áreas asociadas
        foreach ($areatrabajo as $area) {
            $insertAreaStatement = $conn->prepare("INSERT INTO AREAS (IDPRODUCION, AREDETALLE) VALUES (:idproduccion, :areadetalle)");
            $insertAreaStatement->execute([
                ":idproduccion" => $idproduccion,
                ":areadetalle" => $area
            ]);
        }

        // Registramos el movimiento en el kardex
        registrarEnKardex($_SESSION["user"]["ID_USER"], $_SESSION["user"]["USER"], "EDITÓ", 'PRODUCCIÓN', $idproduccion);

        // Redirigir a alguna página de éxito o a donde desees
        header("Location: produccion.php");
        exit(); // Detener la ejecución del script
    } else {
        $error = "Por favor, complete todos los campos requeridos.";
    }
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

                    <!-- si hay un error, mostrar mensaje de error -->
                    <?php if ($error): ?> 
                        <p class="text-danger">
                            <?= $error ?>
                        </p>
                    <?php endif ?>

                    <!-- Formulario para editar registro de producción -->
                    <form class="row g-3" method="POST" action="produccionEdit.php">
                        <input type="hidden" name="idproduccion" value="<?= $produccionInfo["IDPRODUCION"] ?>">
                        <div class="col-md-6">
                            <div class="form-floating mb-3">
                                <input type="text" class="form-control" id="idplano" name="idplano" placeholder="ID Plano" value="<?= $produccionInfo["IDPLANO"] ?>" autocomplete="off">
                                <label for="idplano">ID Plano</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-floating mb-3">
                                <input type="text" class="form-control" id="proobservaciones" name="proobservaciones" placeholder="Observaciones" value="<?= $produccionInfo["PROOBSERVACIONES"] ?>" autocomplete="off">
                                <label for="proobservaciones">Observaciones</label>
                            </div>
                        </div>

                        <h5 class="card-title">Vincular Áreas</h5>

                        <div class="col-md-12">
                            <div class="form-floating mb-3">
                                <?php
                                // Definir las áreas de trabajo
                                $areas = array(
                                    "Carpinteria",
                                    "ACM",
                                    "Pintura",
                                    "Acrilicos",
                                    "Maquinas",
                                    "Metal Mecánica"
                                );
                                // Consultamos las áreas asociadas a la producción actual
                                $areasAsociadas = [];
                                if ($produccionInfo) {
                                    // Consultamos las áreas asociadas a la producción actual
                                    $areasAsociadasStatement = $conn->prepare("SELECT AREDETALLE FROM AREAS WHERE IDPRODUCION = :idproduccion");
                                    $areasAsociadasStatement->execute([":idproduccion" => $produccionInfo["IDPRODUCION"]]);
                                    $areasAsociadasResult = $areasAsociadasStatement->fetchAll(PDO::FETCH_COLUMN);
                                    // Almacenamos las áreas asociadas en el array
                                    $areasAsociadas = array_map('intval', $areasAsociadasResult);
                                }

                                // Ahora, cuando imprimimos los checkboxes, verificamos si el área está asociada y marcamos el checkbox correspondiente
                                foreach ($areas as $index => $area) {
                                    if ($area != "Diseno Grafico") {
                                        echo "<div class='form-check'>";
                                        $checked = in_array($index + 1, $areasAsociadas) ? "checked" : ""; // Verificar si el área está asociada
                                        echo "<input class='form-check-input' type='checkbox' name='areatrabajo[]' value='" . ($index + 1) . "' id='areatrabajo" . ($index + 1) . "' $checked>";
                                        echo "<label class='form-check-label' for='areatrabajo" . ($index + 1) . "'>" . $area . "</label>";
                                        echo "</div>";
                                    }
                                }
                                ?>
                            </div>
                        </div>

                        <div class="text-center">
                            <button type="submit" class="btn btn-primary">Actualizar</button>
                            <button type="reset" class="btn btn-secondary">Limpiar Campos</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>

<?php require "./partials/footer.php"; ?>
