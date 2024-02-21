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

// Verificamos el método que usa el formulario con un if
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validamos que no se manden datos vacíos
    if (empty($_POST["producto"]) || empty($_POST["diseniador"]) || empty($_POST["hora_inicio"]) || empty($_POST["hora_final"])) {
        $error = "POR FAVOR RELLENA TODOS LOS CAMPOS";
    } else {
        // Insertamos un nuevo registro
        $statement = $conn->prepare("INSERT INTO REGISTROS (PRODUCTO, DISENIADOR, HORA_INICIO, HORA_FINAL, OBSERVACIONES) 
                                      VALUES (:producto, :diseniador, :hora_inicio, :hora_final, :observaciones)");

        $statement->execute([
            ":producto" => $_POST["producto"],
            ":diseniador" => $_POST["diseniador"],
            ":hora_inicio" => $_POST["hora_inicio"],
            ":hora_final" => $_POST["hora_final"],
            ":observaciones" => $_POST["observaciones"]
        ]);

        // Redirigimos a la página principal o a donde desees
        header("Location: index.php");
        return;
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
                    <h5 class="card-title">Nuevo Registro de Diseño</h5>

                    <!-- si hay un error mandar un danger -->
                    <?php if ($error): ?>
                        <p class="text-danger">
                            <?= $error ?>
                        </p>
                    <?php endif ?>
                    <form class="row g-3" method="POST" action="registroOd.php">
                        <div class="col-md-6">
                            <div class="form-floating mb-3">
                                <input type="text" class="form-control" id="producto" name="producto" placeholder="Producto" autocomplete="producto" required>
                                <label for="producto">Producto</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-floating mb-3">
                                <input type="text" class="form-control" id="diseniador" name="diseniador" placeholder="Diseñador" autocomplete="diseniador" required>
                                <label for="diseniador">Diseñador</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-floating mb-3">
                                <input type="datetime-local" class="form-control" id="hora_inicio" name="hora_inicio" placeholder="Hora de Inicio" autocomplete="hora_inicio" required>
                                <label for="hora_inicio">Hora de Inicio</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-floating mb-3">
                                <input type="datetime-local" class="form-control" id="hora_final" name="hora_final" placeholder="Hora Final" autocomplete="hora_final" required>
                                <label for="hora_final">Hora Final</label>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="form-floating mb-3">
                                <textarea class="form-control" id="observaciones" name="observaciones" placeholder="Observaciones"></textarea>
                                <label for="observaciones">Observaciones</label>
                            </div>
                        </div>
                        <div class="text-center">
                            <button type="submit" class="btn btn-primary">Guardar</button>
                            <button type="reset" class="btn btn-secondary">Limpiar</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>

<?php require "./partials/footer.php"; ?>
