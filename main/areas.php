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
    $produccionRegistros = $conn->query("SELECT * FROM PRODUCCION");
    $areasAsociadas = $conn->query("SELECT * FROM AREAS where IDPRODUCION = {$produccionRegistros['IDPRODUCION']}");
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
                registrarEnKardex($_SESSION["user"]["ID_USER"], $_SESSION["user"]["USER"], "EDITO", 'AREAS', $_POST["area"]);

            } else {
                // Si no existe, insertamos un nuevo registro
                $statement = $conn->prepare("INSERT INTO AREAS (AREDETALLE) VALUES (:area)");
        
                $statement->execute([
                    ":area" => $_POST["area"],
                ]);
                // Registramos el movimiento en el kardex
                registrarEnKardex($_SESSION["user"]["ID_USER"], $_SESSION["user"]["USER"], "CREO", 'AREAS', $_POST["area"]);
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
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <h2>Registros de Producción</h2>
                <table class="table">
                    <thead>
                        <tr>
                            <th scope="col">#</th>
                            <th scope="col">ID Plano</th>
                            <th scope="col">Observaciones</th>
                            <th scope="col">Fecha</th>
                            <th scope="col">Áreas Asociadas</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($produccionRegistros as $registro): ?>
                            <tr>
                                <th scope="row"><?= $registro["IDPRODUCION"] ?></th>
                                <td><?= $registro["IDPLANO"] ?></td>
                                <td><?= $registro["PROOBSERVACIONES"] ?></td>
                                <td><?= $registro["PROFECHA"] ?></td>
                                <td>
                                    <?php foreach($areasAsociadas as $area) : ?>  
                                        <?php 
                                            if ($area["AREDETALLE"] == 1  ) {
                                                echo("Carpinteria");
                                            } elseif ($area["AREDETALLE"] == 2  ) {
                                                echo("ACM");
                                            } 
                                        ?>
                                        "",
                                                            "",
                                                            "Pintura",
                                                            "Acrilicos y Acabados",
                                                            "Maquinas",
                                                            "Impresiones"
                                    <?php endforeach ?>    
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</section>

<?php require "./partials/footer.php"; ?>
