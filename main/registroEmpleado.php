<?php
require "../sql/database.php";
require "./partials/session_handler.php"; 


// Si la sesión no existe, redirigir al login.php y dejar de ejecutar el resto
if (!isset($_SESSION["user"])) {
    header("Location: ../login-form/login.php");
    return;
}

// declaramos la variable error
$error = null;

// Validar si el usuario es un empleado
if ($_SESSION["user"]["usu_rol"] == 6 || $_SESSION["user"]["usu_rol"] == 1) {
    // Obtener la cédula del empleado
    $empleado = $_SESSION["user"]["cedula"];

    // Obtener el área de trabajo del empleado actual consultando la tabla personas
    $area_trabajo_query = $conn->prepare("SELECT per_areaTrabajo FROM personas WHERE cedula = :cedula");
    $area_trabajo_query->execute([':cedula' => $empleado]);
    $area_trabajo_result = $area_trabajo_query->fetch(PDO::FETCH_ASSOC);
    $area_trabajo_empleado = $area_trabajo_result['per_areaTrabajo'];

    // Verificar si ya hay un registro activo para el diseñador actual
    $registroQuery = $conn->prepare("SELECT *
    FROM registro
    JOIN registro_empleado ON registro.reg_id = registro_empleado.reg_id
    JOIN registro_empleado_actividades AS Re ON registro.reg_id = Re.reg_id
    WHERE registro.reg_cedula = :empleado
     AND registro_empleado.reg_fechaFin IS NULL
    LIMIT 1");
    $registroQuery->execute(array(':empleado' => $empleado));

    if ($registroQuery->rowCount() > 0) {
        header("Location: registroEmpleadoFin.php");
        return;
    } else {
        // Procesar el formulario cuando se envíe
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            // Validamos que no se manden datos vacios
            if (empty($_POST["op_id"]) || empty($_POST["pla_id"])) {
                $error = 'POR FAVOR SELECCIONAR OP Y PLANO.';
            } else {
                // Obtener los datos del formulario
                $op_id = $_POST["op_id"];
                $pla_id = $_POST["pla_id"];
                // Obtener la cédula del empleado de la sesión
                $reg_cedula = $empleado;
                // Insertar los datos en la tabla de registro
                $insertRegistroQuery = $conn->prepare("INSERT INTO registro (pro_id, reg_fecha, reg_cedula, op_id, pla_id) 
                                                    SELECT pro.pro_id, CURRENT_TIMESTAMP, :reg_cedula, :op_id, :pla_id
                                                    FROM produccion pro
                                                    INNER JOIN planos p ON pro.pla_id = p.pla_id
                                                    WHERE p.pla_id = :pla_id LIMIT 1");
                $insertRegistroQuery->bindParam(':pla_id', $pla_id);
                $insertRegistroQuery->bindParam(':reg_cedula', $reg_cedula);
                $insertRegistroQuery->bindParam(':op_id', $op_id);
                $insertRegistroQuery->bindParam(':pla_id', $pla_id);
                $insertRegistroQuery->execute();

                // Obtener el ID del registro insertado
                $reg_id = $conn->lastInsertId();

                // Insertar datos en la tabla de registro_empleado
                $insertRegistroEmpleadoQuery = $conn->prepare("INSERT INTO registro_empleado (reg_id, reg_logistica, reg_areaTrabajo) 
                                                           VALUES (:reg_id, 0, :area_trabajo_empleado)");
                $insertRegistroEmpleadoQuery->bindParam(':reg_id', $reg_id);
                $insertRegistroEmpleadoQuery->bindParam(':area_trabajo_empleado', $area_trabajo_empleado);
                $insertRegistroEmpleadoQuery->execute();

                if (!empty($_POST["actividades"])) {
                    $actividades = $_POST["actividades"];
                    $insertRegistroEmpleadoActividadesQuery = $conn->prepare("INSERT INTO registro_empleado_actividades (reg_id, reg_detalle) 
                                                                             VALUES (:reg_id, :actividad)");
                    $insertRegistroEmpleadoActividadesQuery->bindParam(':reg_id', $reg_id);
                    $insertRegistroEmpleadoActividadesQuery->bindParam(':actividad', $actividad);
                    foreach ($actividades as $actividad) {
                        $insertRegistroEmpleadoActividadesQuery->execute();
                    }
                }

                // Insertar otra actividad si se proporcionó
                if (!empty($_POST["otra_actividad"])) {
                    $otra_actividad = $_POST["otra_actividad"];
                    $insertOtraActividadQuery = $conn->prepare("INSERT INTO registro_empleado_actividades (reg_id, reg_detalle) 
                                                                VALUES (:reg_id, :otra_actividad)");
                    $insertOtraActividadQuery->bindParam(':reg_id', $reg_id);
                    $insertOtraActividadQuery->bindParam(':otra_actividad', $otra_actividad);
                    $insertOtraActividadQuery->execute();
                }

                // Redirigir o mostrar un mensaje de éxito
                header("Location: registroEmpleado.php");
                exit();
            }
        }
    }
} else {
    // Redirigir a la página principal o a donde desees si el usuario no tiene permisos adecuados
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
                    <h5 class="card-title">NUEVO REGISTRO DE EMPLEADO</h5>

                    <!-- Si hay un error, mostrarlo -->
                    <?php if ($error) : ?>
                        <p class="text-danger">
                            <?= $error ?>
                        </p>
                    <?php endif ?>

                    <form class="row g-3" method="POST" action="registroEmpleado.php">
                        <div class="col-md-6">
                            <div class="form-floating mb-3">
                                <select class="form-select" id="op_id" name="op_id" required>
                                    <option selected disabled value="">SELECCIONA LA ORDEN DE PRODUCCIÓN</option>
                                    <?php foreach ($ops as $op) : ?>
                                        <option value="<?= $op["op_id"] ?>"><?= $op["op_id"] ?></option>
                                    <?php endforeach ?>
                                </select>
                                <label for="op_id">ORDEN DE PRODUCCIÓN</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-floating mb-3">
                                <select class="form-select" id="pla_id" name="pla_id" required>
                                    <option selected disabled value="">SELECCIONA EL PLANO</option>
                                </select>
                                <label for="pla_id">PLANO</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-floating mb-3">
                                <input class="form-control" id="reg_areaTrabajo" name="reg_areaTrabajo" value="<?= $area_trabajo_empleado ?>" readonly>
                                <label for="reg_areaTrabajo">ÁREA DE TRABAJO</label>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <h5 class="card-title">ACTIVIDADES</h5>
                            <!-- Checkbox para las actividades -->
                            <?php foreach ($actividades as $actividad) : ?>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="<?= strtolower(str_replace(" ", "_", $actividad)) ?>" name="actividades[]" value="<?= $actividad ?>">
                                    <label class="form-check-label" for="<?= strtolower(str_replace(" ", "_", $actividad)) ?>">
                                        <?= $actividad ?>
                                    </label>
                                </div>
                            <?php endforeach; ?>

                            <!-- Campo para ingresar otra actividad -->
                            <div class="form-floating mb-3 mt-3">
                                <input type="text" class="form-control" id="otra_actividad" name="otra_actividad">
                                <label for="otra_actividad">Otra Actividad</label>
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
    // Obtener el área de trabajo del empleado
    var areaTrabajoEmpleado = "<?php echo $area_trabajo_empleado; ?>";

    // Escucha el cambio en la selección de la orden de producción
    document.getElementById('op_id').addEventListener('change', function() {
        var opId = this.value; // Obtén el valor seleccionado de la orden de producción

        // Realiza una petición AJAX para obtener los planos basados en la orden de producción seleccionada
        var xhr = new XMLHttpRequest();
        xhr.open('POST', 'validaciones/obtener_planos.php'); // Ruta al archivo PHP que maneja la solicitud AJAX
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        xhr.onload = function() {
            if (xhr.status === 200) {
                var planos = JSON.parse(xhr.responseText); // Parsea la respuesta JSON
                // Elimina todos los elementos de opción actuales del select de planos
                var selectPlano = document.getElementById('pla_id');
                selectPlano.innerHTML = ''; // Limpia el select
                // Crea opciones para cada plano devuelto por la consulta AJAX
                planos.forEach(function(plano) {
                    var option = document.createElement('option');
                    option.value = plano.pla_id;
                    option.text = plano.pla_numero;
                    selectPlano.appendChild(option);
                });
            } else {
                console.error('Error en la petición AJAX');
            }
        };
        // Envía el ID de la orden de producción y el área de trabajo al servidor
        xhr.send('op_id=' + opId + '&area_trabajo=' + areaTrabajoEmpleado);
    });
</script>