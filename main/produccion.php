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

    if ($_SESSION["user"]["usu_rol"] && $_SESSION["user"]["usu_rol"] == 1) {
        
        // Llamamos las áreas de la base de datos
        $produccionRegistros = $conn->query("SELECT op.op_id, planos.pla_numero, produccion.*
        FROM op
        INNER JOIN planos ON op.op_id = planos.op_id
        INNER JOIN produccion ON planos.pla_id = produccion.pla_id;
        ");
        $produccionRegistro = $produccionRegistros->fetch(PDO::FETCH_ASSOC);

        // Verificamos si se encontró un registro
        if ($produccionRegistro) {
            // Obtenemos el ID de producción
            $idProduccion = $produccionRegistro["pro_id"];
            
            // Consultamos las áreas asociadas a la producción
            $areasAsociadasStatement = $conn->prepare("SELECT * FROM pro_areas where pro_id = :idProduccion");
            $areasAsociadasStatement->execute([":idProduccion" => $idProduccion]);
            
            // Obtenemos las áreas asociadas
            $areasAsociadas = $areasAsociadasStatement->fetchAll(PDO::FETCH_ASSOC);
        } else {
            // Si no se encontró ningún registro de producción, asignamos un array vacío
            $areasAsociadas = [];
        }
        // Verificamos el método que usa el formulario con un if
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            
            
            // Obtener la información de la op y sus planos
            $opInfoStatement = $conn->prepare("SELECT * FROM op WHERE op_id = :idop");
            $opInfoStatement->bindParam(":idop", $_POST["idop"]);
            $opInfoStatement->execute();
            $opInfo = $opInfoStatement->fetch(PDO::FETCH_ASSOC);
            

            // Obtener los planos asociados a la op
            $opPlanosStatement = $conn->prepare("SELECT * FROM planos WHERE op_id = :idop");
            $opPlanosStatement->bindParam(":idop", $_POST["idop"]);
            $opPlanosStatement->execute();
            $opPlanos = $opPlanosStatement->fetchAll(PDO::FETCH_ASSOC);
            
            if (isset($_POST["idplano"])) {
                // Verificar si ya existe un registro con el mismo pla_id
                $existingRecordStatement = $conn->prepare("SELECT COUNT(*) AS count FROM produccion WHERE pla_id = :idplano");
                $existingRecordStatement->execute([":idplano" => $_POST["idplano"]]);
                $existingRecord = $existingRecordStatement->fetch(PDO::FETCH_ASSOC);
                
                if ($existingRecord["count"] > 0) {
                    $error = "EL REGISTRO YA EXISTE PARA ESTE PLANO" . $_POST["idplano"] . " DE LA OP " . $_POST["op_id"] . " PROPORCIONADA. EDITELO O REVISE LA INFORMACION";
                } else {
                    // Insertar datos de producción en la tabla produccion
                    $insertStatement = $conn->prepare("INSERT INTO produccion (pla_id, pro_fecha) VALUES (:idplano, :proobservaciones, CURRENT_TIMESTAMP)");
                    $insertStatement->execute([
                        ":idplano" => $_POST["idplano"],
                    ]);
                    // Registramos el movimiento en el kardex
                    $lastInsertId = $conn->lastInsertId();
                    registrarEnKardex($_SESSION["user"]["cedula"], "CREÓ", 'PRODUCCIÓN', $lastInsertId);
                    
                    // Obtenemos la cantidad de áreas de trabajo seleccionadas
                    $areasSeleccionadas = isset($_POST["areatrabajo"]) ? $_POST["areatrabajo"] : [];
                    if (!empty($areasSeleccionadas)) {
                        foreach ($areasSeleccionadas as $area) {
                            // Insertar áreas de trabajo seleccionadas en la tabla pro_areas
                            
                            $insertStatement = $conn->prepare("INSERT INTO pro_areas (pro_id, proAre_detalle) VALUES (:idproduccion, :aredetalle)");
                            $insertStatement->execute([
                                ":idproduccion" => $lastInsertId,
                                ":aredetalle" => $area
                            ]);
                        }
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
                <!-- Código para buscar op por op_id -->
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">BUSCAR OP POR NÚMERO</h5>

                        <!-- si hay un error mandar un danger -->
                        <?php if ($error): ?> 
                            <p class="text-danger">
                                <?= $error ?>
                            </p>
                        <?php endif ?>
                        <form class="row g-3" method="POST" action="produccion.php">
                            <div class="col-md-12">
                                <div class="form-floating mb-3">
                                    <input type="text" class="form-control" id="idop" name="idop" placeholder="op_id">
                                    <label for="idop">NÚMERO DE OP</label>
                                </div>
                            </div>
                            <div class="text-center">
                                <button type="submit" class="btn btn-primary">BUSCAR</button>
                                <button type="reset" class="btn btn-secondary">LIMPIAR</button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Mostrar información de la op y sus planos -->
                <?php if ($opInfo): ?>

                    <?php if ($opPlanos): ?>
                        <section class="section">
                            <div class="row">
                                <div class="col-lg-12">
                                    <div class="card">
                                        <div class="card-body">
                                            <h5 class="card-title">DATOS DE LA OP</h5>
                                            <p>NÚMERO DE OP: <?= $opInfo["op_id"] ?></p>
                                            <p>CLIENTE: <?= $opInfo["OPCLIENTE"] ?></p>
                                            <hr>
                                            <h5 class="card-title">PLANOS DE LA OP</h5>
                                            <!-- si el array asociativo $opPlanos no tiene nada dentro, entonces imprimir el siguiente div -->
                                            <?php if (empty($opPlanos)): ?>
                                                <div class="col-md-4 mx-auto mb-3">
                                                    <div class="card card-body text-center">
                                                        <p>NO HAY PLANOS ASOCIADOS A ESTA OP.</p>
                                                    </div>
                                                </div>
                                            <?php else: ?>
                                                <!-- Formulario para ingresar datos de producción -->
                                                <form class="row g-3" method="POST" action="produccion.php">
                                                    <input type="hidden" value="<?= $opInfo["op_id"]?>"  name="op_id">
                                                    <input type="hidden" name="idproduccion" value="<?= $produccionRegistro["pro_id"] ?>">
                                                    <div class="col-md-6">
                                                        <div class="form-floating mb-3">
                                                            <select class="form-select" id="idplano" name="idplano">
                                                                <?php foreach ($opPlanos as $opPlano): ?>
                                                                    <option value="<?= $opPlano["pla_id"] ?>"><?= $opPlano["pla_numero"] ?></option>
                                                                <?php endforeach ?>
                                                            </select>
                                                            <label for="idplano">SELECCIONAR PLANOS</label>
                                                        </div>
                                                    </div>

                                                    <h5 class="card-title">VINCULAR ÁREAS</h5>

                                                    <div class="col-md-12">
                                                        <div class="form-floating mb-3">
                                                            <?php
                                                            // Definir las áreas de trabajo
                                                            $areas = array(
                                                                "Carpintería",
                                                                "ACM",
                                                                "Pintura",
                                                                "Acrílicos",
                                                                "Máquinas",
                                                                "Metal Mecánica"
                                                            );
                                                            foreach ($areas as $index => $area) {
                                                                if ($area != "Diseño Gráfico") {
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
                                                        <button type="submit" class="btn btn-primary">GUARDAR</button>
                                                        <button type="reset" class="btn btn-secondary">LIMPIAR CAMPOS</button>
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
                                        <h5 class="card-title">REGISTROS DE PRODUCCIÓN</h5>
                                        <table class="table datatable">
                                            <thead>
                                                <tr>
                                                    <th scope="col">#</th>
                                                    <th scope="col">NÚMERO DE OP</th>
                                                    <th scope="col">NÚMERO DE PLANO</th>
                                                    <th scope="col">FECHA</th>
                                                    <th scope="col">ÁREAS ASOCIADAS</th>
                                                    <th></th>
                                                    <th></th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                            <?php foreach ($produccionRegistros as $registro): ?>
                                                <tr>
                                                    <th scope="row"><?= $registro["pro_id"] ?></th>
                                                    <td><?= $registro["op_id"] ?></td>
                                                    <td><?= $registro["pla_numero"] ?></td>
                                                    <td><?= $registro["pro_fecha"] ?></td>
                                                    <td>
                                                        <?php
                                                        // Consultamos las áreas asociadas para el registro actual
                                                        $idProduccion = $registro["pro_id"];
                                                        $areasAsociadasStatement = $conn->prepare("SELECT proAre_detalle FROM pro_areas WHERE pro_id = :idProduccion");
                                                        $areasAsociadasStatement->execute([":idProduccion" => $idProduccion]);
                                                        $areasAsociadas = $areasAsociadasStatement->fetchAll(PDO::FETCH_ASSOC);

                                                        // Mostramos las áreas asociadas
                                                        foreach ($areasAsociadas as $area) {
                                                            switch ($area["proAre_detalle"]) {
                                                                case 1:
                                                                    echo "Carpintería<br>";
                                                                    break;
                                                                case 2:
                                                                    echo "ACM<br>";
                                                                    break;
                                                                case 3:
                                                                    echo "Pintura<br>";
                                                                    break;
                                                                case 4:
                                                                    echo "Acrílicos<br>";
                                                                    break;
                                                                case 5:
                                                                    echo "Máquinas<br>";
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
                                                        <a href="produccionEdit.php?id=<?= $registro["pro_id"] ?>" class="btn btn-secondary mb-2">EDITAR</a>
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
