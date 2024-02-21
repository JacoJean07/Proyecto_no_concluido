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
//validacion para el usuario tipo diseniador 
if ($_SESSION["user"]["ROL"] == 3) {
    // Obtener el diseñador de la sesión activa
    $diseniador = $_SESSION["user"]["CEDULA"];

    // Consultar el registro actual del diseñador
    $registroQuery = $conn->prepare("SELECT * FROM REGISTROS WHERE DISENIADOR = :diseniador AND HORA_FINAL IS NULL LIMIT 1");
    $registroQuery->execute(array(':diseniador' => $diseniador));
    $registro = $registroQuery->fetch(PDO::FETCH_ASSOC);

    // Verificamos si se encontró el registro actual
    if (!$registro) {
        header("Location: registroOd.php");
        return;
    } else {
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            // Validamos que no se manden datos vacíos
            
            // Insertamos un nuevo registro
            $statement = $conn->prepare("UPDATE REGISTROS SET HORA_FINAL = CURRENT_TIMESTAMP, OBSERVACIONES = :observaciones WHERE ID = :id");

            $statement->execute([
                ":observaciones" => $_POST["observaciones"],
                ":id" => $registro["ID"]
            ]);

            // Redirigimos a la página principal o a donde desees
            header("Location: historialRegistros.php");
            return;
            
        }
    }
} else {
    // Redirigimos a la página principal o a donde desees
    header("Location: pages-error-404.html");
    return;
}
?>

<?php require "./partials/header.php"; ?>
<?php require "./partials/dashboard.php"; ?>
<section class="section">
    <div class="row">
        <div class="">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">REGISTRO ACTUAL</h5>

                    <!-- si hay un error mandar un danger -->
                    <?php if ($error): ?>
                        <p class="text-danger">
                            <?= $error ?>
                        </p>
                    <?php endif ?>
                    <form class="row g-3" method="POST" action="registroOdFinal.php">
                        <div class="col-md-6">
                            <div class="form-floating mb-3">
                                <input value="<?= $registro["PRODUCTO"] ?>" class="form-control" id="PRODUCTO" name="PRODUCTO" placeholder="PRODUCTO" required readonly></input>
                                <label for="PRODUCTO">PRODUCTO</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-floating mb-3">
                                <input value="<?= $registro["HORA_INICIO"] ?>" class="form-control" id="horainicio" name="horainicio" placeholder="horainicio" required readonly></input>
                                <label for="horainicio">HORA INICIO</label>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="form-floating mb-3">
                                <textarea class="form-control" id="observaciones" name="observaciones" placeholder="Observaciones"></textarea>
                                <label for="observaciones">Observaciones (Opcional)</label>
                            </div>
                        </div>
                        <div class="text-center">
                            <button type="submit" class="btn btn-primary">FINALIZAR</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>

<?php require "./partials/footer.php"; ?>
