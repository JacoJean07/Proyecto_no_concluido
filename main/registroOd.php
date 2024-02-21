<?php 
require "../sql/database.php";
session_start();

// Si la sesión no existe, redirigir al login.php y dejar de ejecutar el resto
if (!isset($_SESSION["user"])) {
    header("Location: ../login-form/login.php");
    return;
}
//validacion para el usuario tipo diseniador 
if ($_SESSION["user"]["ROL"] == 3) {
    // Obtener el diseñador de la sesión activa
    $diseniador = $_SESSION["user"]["CEDULA"];

    // Buscar productos existentes
    $productosQuery = $conn->prepare("SELECT PRODUCTO, MARCA, CAMPANIA FROM ORDENDISENIO WHERE ESTADO = 2");
    $productosQuery->execute();
    $productos = $productosQuery->fetchAll(PDO::FETCH_ASSOC);

    // Verificar si ya hay un registro activo para el diseñador actual
    $registroQuery = $conn->prepare("SELECT * FROM REGISTROS WHERE DISENIADOR = :diseniador AND HORA_FINAL IS NULL LIMIT 1");
    $registroQuery->execute(array(':diseniador' => $diseniador));

    if ($registroQuery->rowCount() > 0) {
        header("Location: registroOdFinal.php");
        return;
    } else {
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            // Validamos que no se manden datos vacíos
            if (empty($_POST["producto"])) {
                $error = "POR FAVOR SELECCIONA UN PRODUCTO";
            } else {
                // Insertamos un nuevo registro
                $statement = $conn->prepare("INSERT INTO REGISTROS (PRODUCTO, DISENIADOR, HORA_INICIO, HORA_FINAL) 
                                            VALUES (:producto, :diseniador, CURRENT_TIMESTAMP, NULL)");
        
                $statement->execute([
                    ":producto" => $_POST["producto"],
                    ":diseniador" => $diseniador
                ]);
        
                // Redirigimos a la página principal o a donde desees
                header("Location: registroOd.php");
                return;
            }
        }
    }
} else {
    // Redirigimos a la página principal o a donde desees
    header("Location: pages-error-404.html");
    return;
}

// Declaramos la variable error que nos ayudará a mostrar errores, etc.
$error = null;


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
                                <select class="form-select" id="producto" name="producto" required>
                                    <option selected disabled value="">Selecciona un producto</option>
                                    <?php foreach ($productos as $producto): ?>
                                        <option value="<?= $producto["PRODUCTO"] ?>" data-marca="<?= $producto["MARCA"] ?>" data-compania="<?= $producto["CAMPANIA"] ?>"><?= $producto["PRODUCTO"] ?></option>
                                    <?php endforeach ?>
                                </select>
                                <label for="producto">Producto</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-floating mb-3">
                                <input class="form-control" id="marca" name="marca" placeholder="marca" required readonly></input>
                                <label for="marca">Marca</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-floating mb-3">
                                <input class="form-control" id="compania" name="compania" placeholder="compania" required readonly></input>
                                <label for="compania">Compañía</label>
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

<script>
    document.getElementById('producto').addEventListener('change', function() {
        var producto = this.value;
        var marca = this.options[this.selectedIndex].getAttribute('data-marca');
        var compania = this.options[this.selectedIndex].getAttribute('data-compania');
        
        document.getElementById('marca').value = marca;
        document.getElementById('compania').value = compania;
    });
</script>
