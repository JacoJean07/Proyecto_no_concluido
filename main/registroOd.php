<?php 
require "../sql/database.php";
session_start();

// Si la sesión no existe, redirigir al login.php y dejar de ejecutar el resto
if (!isset($_SESSION["user"])) {
    header("Location: ../login-form/login.php");
    return;
}
//validacion para el usuario tipo diseniador 
if ($_SESSION["user"]["usu_rol"] == 3) {
    // Obtener el diseñador de la sesión activa
    $diseniador = $_SESSION["user"]["cedula"];

    // Buscar od_productos existentes
    $od_productosQuery = $conn->prepare("SELECT od_detalle, od_cliente FROM orden_disenio WHERE od_estado = 'PROPUESTA'");
    $od_productosQuery->execute();
    $od_productos = $od_productosQuery->fetchAll(PDO::FETCH_ASSOC);

    // Verificar si ya hay un registro activo para el diseñador actual
    $registroQuery = $conn->prepare("SELECT * FROM registros_disenio WHERE rd_diseniador = :diseniador AND rd_hora_fin IS NULL LIMIT 1");
    $registroQuery->execute(array(':diseniador' => $diseniador));

    if ($registroQuery->rowCount() > 0) {
        header("Location: registroOdFinal.php");
        return;
    } else {
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            // Validamos que no se manden datos vacíos
            if (empty($_POST["od_detalle"])) {
                $error = "POR FAVOR SELECCIONA UN PRODUCTO.";
            } else {
                // Obtener el od_id correspondiente al od_detalle seleccionado
                $od_detalle = $_POST["od_detalle"];
                $od_id_query = $conn->prepare("SELECT od_id FROM orden_disenio WHERE od_detalle = :od_detalle");
                $od_id_query->bindParam(":od_detalle", $od_detalle);
                $od_id_query->execute();
                $od_id_result = $od_id_query->fetch(PDO::FETCH_ASSOC);
                $od_id = $od_id_result['od_id'];
    
                // Insertamos un nuevo registro
                $statement = $conn->prepare("INSERT INTO registros_disenio (od_id, rd_diseniador, rd_hora_ini, rd_hora_fin) 
                                            VALUES (:od_id, :diseniador, CURRENT_TIMESTAMP, NULL)");
    
                $statement->execute([
                    ":od_id" => $od_id,
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
                    <h5 class="card-title">NUEVO REGISTRO DE DISEÑO</h5>

                    <!-- si hay un error mandar un danger -->
                    <?php if ($error): ?>
                        <p class="text-danger">
                            <?= $error ?>
                        </p>
                    <?php endif ?>
                    <form class="row g-3" method="POST" action="registroOd.php">
                        <div class="col-md-6">
                            <div class="form-floating mb-3">
                                <select class="form-select" id="od_detalle" name="od_detalle" required>
                                    <option selected disabled value="">SELECCIONA EL PRODUCTO</option>
                                    <?php foreach ($od_productos as $od_detalle): ?>
                                        <option value="<?= $od_detalle["od_detalle"] ?>" data-od_cliente="<?= $od_detalle["od_cliente"] ?>"><?= $od_detalle["od_detalle"] ?></option>
                                    <?php endforeach ?>
                                </select>
                                <label for="od_detalle">PRODUCTO</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-floating mb-3">
                                <input class="form-control" id="od_cliente" name="od_cliente" placeholder="od_cliente" required readonly></input>
                                <label for="od_cliente">CLIENTE</label>
                            </div>
                        </div>
                        <div class="text-center">
                            <button type="submit" class="btn btn-primary">GUARDAR</button>
                            <button type="reset" class="btn btn-secondary">LIMPIAR</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>

<?php require "./partials/footer.php"; ?>

<script>
    document.getElementById('od_detalle').addEventListener('change', function() {
        var od_detalle = this.value;
        var od_cliente = this.options[this.selectedIndex].getAttribute('data-od_cliente');
        var compania = this.options[this.selectedIndex].getAttribute('data-compania');
        
        document.getElementById('od_cliente').value = od_cliente;
        document.getElementById('compania').value = compania;
    });
</script>
