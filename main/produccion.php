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
$idop = isset($_GET["idop"]) ? $_GET["idop"] : null; 
$opInfo = null;
$opPlanos = null;

if ($_SESSION["user"]["ROL"] && $_SESSION["user"]["ROL"] == 1) {
    
    // Llamamos las áreas de la base de datos
    $produccionRegistros = $conn->query("SELECT OP.IDOP, PLANOS.PLANNUMERO, PRODUCCION.*
    FROM OP
    INNER JOIN PLANOS ON OP.IDOP = PLANOS.IDOP
    INNER JOIN PRODUCCION ON PLANOS.IDPLANO = PRODUCCION.IDPLANO;
    ");
    $produccionRegistro = $produccionRegistros->fetch(PDO::FETCH_ASSOC);

    // Verificamos si se encontró un registro
    if ($produccionRegistro) {
        // Obtenemos el ID de producción
        $idProduccion = $produccionRegistro["IDPRODUCION"];
        
        // Consultamos las áreas asociadas a la producción
        $areasAsociadasStatement = $conn->prepare("SELECT * FROM AREAS where IDPRODUCION = :idProduccion");
        $areasAsociadasStatement->execute([":idProduccion" => $idProduccion]);
        
        // Obtenemos las áreas asociadas
        $areasAsociadas = $areasAsociadasStatement->fetchAll(PDO::FETCH_ASSOC);
    } else {
        // Si no se encontró ningún registro de producción, asignamos un array vacío
        $areasAsociadas = [];
    }
    // Verificamos el método que usa el formulario con un if
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        // Validamos que no se manden datos vacíos
        if (empty($_POST["idop"] )) {
            $error = "POR FAVOR RELLENA TODOS LOS CAMPOS";
        } else {
            // Obtener la información de la OP y sus planos
            $opInfoStatement = $conn->prepare("SELECT * FROM OP WHERE IDOP = :idop");
            $opInfoStatement->bindParam(":idop", $_POST["idop"]);
            $opInfoStatement->execute();
            $opInfo = $opInfoStatement->fetch(PDO::FETCH_ASSOC);
            

            // Obtener los planos asociados a la OP
            $opPlanosStatement = $conn->prepare("SELECT * FROM PLANOS WHERE IDOP = :idop");
            $opPlanosStatement->bindParam(":idop", $_POST["idop"]);
            $opPlanosStatement->execute();
            $opPlanos = $opPlanosStatement->fetchAll(PDO::FETCH_ASSOC);
            
            if (isset($_POST["idplano"]) && isset($_POST["proobservaciones"])) {
                // Insertar datos de producción en la tabla PRODUCCION
                $insertStatement = $conn->prepare("INSERT INTO PRODUCCION (IDPLANO, PROOBSERVACIONES, PROFECHA) VALUES (:idplano, :proobservaciones, CURRENT_TIMESTAMP)");
                $insertStatement->execute([
                    ":idplano" => $_POST["idplano"],
                    ":proobservaciones" => $_POST["proobservaciones"]
                ]);

            }

             if (isset($_POST["idplano"]) && isset($_POST["proobservaciones"])) {
            if (isset($_POST["idproduccion"])) {
                // Actualizar datos de producción en la tabla PRODUCCION
                $updateStatement = $conn->prepare("UPDATE PRODUCCION SET IDPLANO = :idplano, PROOBSERVACIONES = :proobservaciones WHERE IDPRODUCION = :idproduccion");
                $updateStatement->execute([
                    ":idplano" => $_POST["idplano"],
                    ":proobservaciones" => $_POST["proobservaciones"],
                    ":idproduccion" => $_POST["idproduccion"]
                ]);
            } else {
                // Insertar datos de producción en la tabla PRODUCCION
                $insertStatement = $conn->prepare("INSERT INTO PRODUCCION (IDPLANO, PROOBSERVACIONES, PROFECHA) VALUES (:idplano, :proobservaciones, CURRENT_TIMESTAMP)");
                $insertStatement->execute([
                    ":idplano" => $_POST["idplano"],
                    ":proobservaciones" => $_POST["proobservaciones"]
                ]);
            }
        }

            // Registramos el movimiento en el kardex
            $lastInsertId = $conn->lastInsertId();
            registrarEnKardex($_SESSION["user"]["ID_USER"], $_SESSION["user"]["USER"], "CREO", 'PRODUCCION', $lastInsertId);

            // Obtenemos la cantidad de áreas de trabajo seleccionadas
            $areasSeleccionadas = isset($_POST["areatrabajo"]) ? $_POST["areatrabajo"] : [];
            if (!empty($areasSeleccionadas)) {
                foreach ($areasSeleccionadas as $area) {
                    // Insertar áreas de trabajo seleccionadas en la tabla AREAS
                    $insertStatement = $conn->prepare("INSERT INTO AREAS (IDPRODUCION, AREDETALLE) VALUES (:idproduccion, :aredetalle)");
                    $insertStatement->execute([
                        ":idproduccion" => $lastInsertId,
                        ":aredetalle" => $area
                    ]);
                }
            }
        }
    }
}

?>

<?php require "./partials/header.php"; ?>
<?php require "./partials/dashboard.php"; ?>
<section class="section">
    <div class="row">
        <div class="">
            <!-- Código para buscar OP por IDOP -->
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Buscar OP por IDOP</h5>

                    <!-- si hay un error mandar un danger -->
                    <?php if ($error): ?> 
                        <p class="text-danger">
                            <?= $error ?>
                        </p>
                    <?php endif ?>
                    <form class="row g-3" method="POST" action="produccion.php">
                        <div class="col-md-12">
                            <div class="form-floating mb-3">
                                <input type="text" class="form-control" id="idop" name="idop" placeholder="IDOP">
                                <label for="idop">IDOP</label>
                            </div>
                        </div>
                        <div class="text-center">
                            <button type="submit" class="btn btn-primary">Buscar</button>
                            <button type="reset" class="btn btn-secondary">Reset</button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Mostrar información de la OP y sus planos -->
            <?php if ($opInfo): ?>

                <?php if ($opPlanos): ?>
                    <section class="section">
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="card">
                                    <div class="card-body">
                                        <h5 class="card-title">Datos de la OP</h5>
                                        <p>IDOP: <?= $opInfo["IDOP"] ?></p>
                                        <p>Cliente: <?= $opInfo["OPCLIENTE"] ?></p>
                                        <hr>
                                        <h5 class="card-title">Planos de la OP</h5>
                                        <!-- si el array asociativo $opPlanos no tiene nada dentro, entonces imprimir el siguiente div -->
                                        <?php if (empty($opPlanos)): ?>
                                            <div class="col-md-4 mx-auto mb-3">
                                                <div class="card card-body text-center">
                                                    <p>No hay planos asociados a esta OP.</p>
                                                </div>
                                            </div>
                                        <?php else: ?>
                                            <!-- Formulario para ingresar datos de producción -->
                                            <form class="row g-3" method="POST" action="produccion.php">
                                                <input type="hidden" name="idproduccion" value="<?= $produccionRegistro["IDPRODUCION"] ?>">
                                                <div class="col-md-6">
                                                    <div class="form-floating mb-3">
                                                        <select class="form-select" id="idplano" name="idplano">
                                                            <?php foreach ($opPlanos as $opPlano): ?>
                                                                <option value="<?= $opPlano["IDPLANO"] ?>"><?= $opPlano["PLANNUMERO"] ?></option>
                                                            <?php endforeach ?>
                                                        </select>
                                                        <label for="idplano">Seleccionar Plano</label>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-floating mb-3">
                                                        <input type="text" class="form-control" id="proobservaciones" name="proobservaciones" placeholder="Observaciones">
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
                                                        foreach ($areas as $index => $area) {
                                                            if ($area != "Diseno Grafico") {
                                                                echo "<div class='form-check'>";
                                                                echo "<input class='form-check-input' type='checkbox' name='areatrabajo[]' value='" . ($index + 1) . "' id='areatrabajo" . ($index + 1) . "'>";
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
                                        <?php endif ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </section>
                <?php endif ?>
            <?php endif ?>
                
            <div class="card">
                <div class="card-body">
                    <section class="section">
                        <div class="container">
                            <div class="row">
                                <div class="col-md-12">
                                    <h2>Registros de Producción</h2>
                                    <table class="table datatable">
                                        <thead>
                                            <tr>
                                                <th scope="col">#</th>
                                                <th scope="col">ID OP</th>
                                                <th scope="col">ID Plano</th>
                                                <th scope="col">Observaciones</th>
                                                <th scope="col">Fecha</th>
                                                <th scope="col">Áreas Asociadas</th>
                                                <th></th>
                                                <th></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                        <?php foreach ($produccionRegistros as $registro): ?>
                                            <tr>
                                                <th scope="row"><?= $registro["IDPRODUCION"] ?></th>
                                                <td><?= $registro["IDOP"] ?></td>
                                                <td><?= $registro["IDPLANO"] ?></td>
                                                <td><?= $registro["PROOBSERVACIONES"] ?></td>
                                                <td><?= $registro["PROFECHA"] ?></td>
                                                <td>
                                                    <?php
                                                    // Consultamos las áreas asociadas para el registro actual
                                                    $idProduccion = $registro["IDPRODUCION"];
                                                    $areasAsociadasStatement = $conn->prepare("SELECT AREDETALLE FROM AREAS WHERE IDPRODUCION = :idProduccion");
                                                    $areasAsociadasStatement->execute([":idProduccion" => $idProduccion]);
                                                    $areasAsociadas = $areasAsociadasStatement->fetchAll(PDO::FETCH_ASSOC);

                                                    // Mostramos las áreas asociadas
                                                    foreach ($areasAsociadas as $area) {
                                                        switch ($area["AREDETALLE"]) {
                                                            case 1:
                                                                echo "Carpinteria<br>";
                                                                break;
                                                            case 2:
                                                                echo "ACM<br>";
                                                                break;
                                                            case 3:
                                                                echo "Pintura<br>";
                                                                break;
                                                            case 4:
                                                                echo "Acrilicos y Acabados<br>";
                                                                break;
                                                            case 5:
                                                                echo "Maquinas<br>";
                                                                break;
                                                            case 6:
                                                                echo "Impresiones<br>";
                                                                break;
                                                            default:
                                                                echo "Área no especificada<br>";
                                                                break;
                                                        }
                                                    }
                                                    ?>
                                                </td>
                                                <td>
                                                    <a href="produccionEdit.php?id=<?= $registro["IDPRODUCION"] ?>" class="btn btn-secondary mb-2">Editar</a>
                                                </td>
                                                <td></td>
                                            </tr>
                                        <?php endforeach; ?>

                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </section>
                </div>
            </div>
        </div>
    </div>
</section>

<?php require "./partials/footer.php"; ?>
