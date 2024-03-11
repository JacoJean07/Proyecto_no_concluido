<?php
require "../sql/database.php";
require "./partials/kardex.php";
require "./partials/session_handler.php"; 



// Redirect to login page if the session does not exist
if (!isset($_SESSION["user"])) {
    header("Location: ../login-form/login.php");
    exit();
}

// Initialize variables
$error = null;
$state = 1;
$id = isset($_GET["id"]) ? $_GET["id"] : null;
$personaEditar = null;

if (($_SESSION["user"]["usu_rol"]) && ($_SESSION["user"]["usu_rol"] == 1)) {
    // Fetch personas from the database
    $statement = $conn->prepare("SELECT * FROM personas WHERE per_estado = :estado");
    $statement->bindValue(":estado", $state);
    $statement->execute();
    $personas = $statement->fetchAll(PDO::FETCH_ASSOC);

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        // Validate form data
        $cedula = $_POST["cedula"];
        $nombres = $_POST["nombres"];
        $apellidos = $_POST["apellidos"];
        $nacimiento = $_POST["nacimiento"];
        $areatrabajo = $_POST["areatrabajo"];
        $correo = $_POST["correo"];

        if (empty($cedula) || empty($nombres) || empty($apellidos) || empty($nacimiento) || empty($areatrabajo)) {
            $error = "POR FAVOR RELLENA TODOS LOS CAMPOS";
        } elseif (!preg_match('/^[0-9]{10}$/', $cedula)) {
            $error = "LA CÉDULA DEBE TENER 10 DÍGIOS NUMÉRICOS.";
        } elseif (!preg_match('/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/', $nombres)) {
            $error = "NOMBRES INVÁLIDOS.";
        } elseif (!preg_match('/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/', $apellidos)) {
            $error = "APELLIDOS INVÁLIDOS.";
        } elseif (empty($nacimiento)) {
            $error = "LA FECHA DE NACIMIENTO ES OBLIGATORIA.";
        } elseif (empty($areatrabajo)) {
            $error = "EL ÁREA DE TRABAJO ES OBLIGATORIA.";
        } else {
            try {
                // Check if the cedula already exists (except for the editing ID)
                $existingStatement = $conn->prepare("SELECT COUNT(*) FROM personas WHERE cedula = :cedula AND cedula != :id");
                $existingStatement->bindValue(":cedula", $cedula);
                $existingStatement->bindValue(":id", $id);
                $existingStatement->execute();
                $count = $existingStatement->fetchColumn();

                if ($count > 0) {
                    $error = "YA EXISTE UN TRABAJADOR CON ESTA CÉDULA.";
                } else {
                    // Prepare the SQL statement based on whether it's an update or insert operation
                    if ($id) {
                        $statement = $conn->prepare("UPDATE personas SET cedula = :cedula, per_nombres = :nombres, per_apellidos = :apellidos, per_fechaNacimiento = :nacimiento, per_areaTrabajo = :areatrabajo, per_correo = :correo WHERE cedula = :id");
                        $statement->bindValue(":id", $id);
                    } else {
                        $statement = $conn->prepare("INSERT INTO personas (cedula, per_nombres, per_apellidos, per_fechaNacimiento, per_estado, per_areaTrabajo, per_correo) VALUES (:cedula, :nombres, :apellidos, :nacimiento, :estado, :areatrabajo, :correo)");
                        $statement->bindValue(":estado", $state);
                    }

                    // Bind the values and execute the statement
                    $statement->bindValue(":cedula", $cedula);
                    $statement->bindValue(":nombres", $nombres);
                    $statement->bindValue(":apellidos", $apellidos);
                    $statement->bindValue(":nacimiento", $nacimiento);
                    $statement->bindValue(":areatrabajo", $areatrabajo);
                    $statement->bindValue(":correo", $correo);
                    $statement->execute();

                    // Register the movement in the kardex
                    registrarEnKardex($_SESSION["user"]["cedula"], $id ? "EDITÓ" : "CREÓ", 'personas', $cedula);

                    // Redirect to personas.php
                    header("Location: personas.php");
                    exit();
                }
            } catch (PDOException $e) {
                // Handle database errors
                $error = "Error: " . $e->getMessage();
            }
        }
    }

    // Get the information of the person to edit
    if ($id) {
        $statement = $conn->prepare("SELECT * FROM personas WHERE cedula = :id");
        $statement->bindValue(":id", $id);
        $statement->execute();
        $personaEditar = $statement->fetch(PDO::FETCH_ASSOC);
    }
} else {
    header("Location: ./index.php");
    exit();
}
?>

<?php require "./partials/header.php"; ?>
<?php require "./partials/dashboard.php"; ?>
<section class="section">
    <div class="row">
        <div class="">
            <div class="card accordion" id="accordionExample">
                <div class="card-body accordion-item">
                    <?php if ($id) : ?>
                        <h5 class="card-title">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
                                EDITAR EMPLEADO
                            </button>
                        </h5>
                    <?php else : ?>
                        <h5 class="card-title accordion-header" id="headingOne">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
                                NUEVO EMPLEADO
                            </button>
                        </h5>
                    <?php endif ?>

                    <div id="collapseOne" class="accordion-collapse collapse" aria-labelledby="headingOne" data-bs-parent="#accordionExample">
                        <div class="accordion-body">
                            <!-- si hay un error mandar un danger -->
                            <?php if ($error) : ?>
                                <div class="alert alert-danger" role="alert">
                                    <?= $error ?>
                                </div>
                            <?php endif ?>
                            <form class="row g-3" method="POST" action="personas.php<?= $id ? "?id=$id" : "" ?>">
                                <div class="col-md-6">
                                    <div class="form-floating">
                                        <input type="number" class="form-control" id="cedula" name="cedula" placeholder="Cedula" value="<?= $personaEditar ? $personaEditar["cedula"] : "" ?>" autocomplete="cedula" required>
                                        <label for="cedula">CÉDULA</label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-floating">
                                        <input type="text" class="form-control" id="nombres" name="nombres" placeholder="Nombres" value="<?= $personaEditar ? $personaEditar["per_nombres"] : "" ?>" autocomplete="nombres" required>
                                        <label for="nombres">NOMBRES</label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-floating">
                                        <input type="text" class="form-control" id="apellidos" name="apellidos" placeholder="Apellidos" value="<?= $personaEditar ? $personaEditar["per_apellidos"] : "" ?>" autocomplete="apellidos" required>
                                        <label for="apellidos">APELLIDOS</label>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="form-floating">
                                        <input type="date" class="form-control" placeholder="Nacimiento" id="nacimiento" name="nacimiento" value="<?= $personaEditar ? $personaEditar["per_fechaNacimiento"] : "" ?>" autocomplete="nacimiento" required>
                                        <label for="nacimiento">FECHA DE NACIMIENTO</label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-floating mb-3">
                                        <select class="form-select" id="areatrabajo" aria-label="State" name="areatrabajo">
                                            <option value="CARPINTERÍA" <?= ($personaEditar && $personaEditar["per_areaTrabajo"] == "CARPINTERÍA") ? "selected" : "" ?>>CARPINTERÍA</option>
                                            <option value="ACM" <?= ($personaEditar && $personaEditar["per_areaTrabajo"] == "ACM") ? "selected" : "" ?>>ACM</option>
                                            <option value="PINTURA" <?= ($personaEditar && $personaEditar["per_areaTrabajo"] == "PINTURA") ? "selected" : "" ?>>PINTURA</option>
                                            <option value="ACRÍLICOS Y ACABADOS" <?= ($personaEditar && $personaEditar["per_areaTrabajo"] == "ACRÍLICOS Y ACABADOS") ? "selected" : "" ?>>ACRÍLICOS Y ACABADOS</option>
                                            <option value="MÁQUINAS" <?= ($personaEditar && $personaEditar["per_areaTrabajo"] == "MÁQUINAS") ? "selected" : "" ?>>MÁQUINAS</option>
                                            <option value="METALMECÁNICA" <?= ($personaEditar && $personaEditar["per_areaTrabajo"] == "METALMECÁNICA") ? "selected" : "" ?>>METALMECÁNICA</option>
                                            <option value="DEP PRODUCCIÓN" <?= ($personaEditar && $personaEditar["per_areaTrabajo"] == "DEP PRODUCCIÓN") ? "selected" : "" ?>>DEPARTAMNETO DE PRODUCCIÓN</option>
                                            <option value="DISEÑO" <?= ($personaEditar && $personaEditar["per_areaTrabajo"] == "DISEÑO") ? "selected" : "" ?>>DISEÑO</option>
                                            <option value="COMERCIAL" <?= ($personaEditar && $personaEditar["per_areaTrabajo"] == "COMERCIAL") ? "selected" : "" ?>>COMERCIAL</option>
                                            <option value="TICS" <?= ($personaEditar && $personaEditar["per_areaTrabajo"] == "TICS") ? "selected" : "" ?>>TICS</option>
                                        </select>
                                        <label for="areatrabajo">ÁREA DE TRABAJO</label>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="form-floating">
                                        <input type="email" class="form-control" id="correo" name="correo" placeholder="Correo" value="<?= $personaEditar ? $personaEditar["per_correo"] : "" ?>" autocomplete="correo">
                                        <label for="correo">CORREO ELECTRÓNICO</label>
                                    </div>
                                </div>
                                <div class="text-center">
                                    <button type="submit" class="btn btn-primary"><?= $id ? "ACTUALIZAR" : "GUARDAR" ?></button>
                                    <button type="reset" class="btn btn-secondary">LIMPIAR</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <section class="section">
                <div class="row">
                    <div class="col-lg-12">

                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title">EMPLEADOS</h5>
                                <!-- si el array asociativo $teachers no tiene nada dentro, entonces imprimir el siguiente div -->
                                <?php if ($personas->rowCount() == 0) : ?>
                                    <div class="col-md-4 mx-auto mb-3">
                                        <div class="card card-body text-center">
                                            <p>NO HAY EMPLEADOS AÚN.</p>
                                        </div>
                                    </div>
                                <?php else : ?>
                                    <!-- Table with stripped rows -->
                                    <table class="table datatable">
                                        <thead>
                                            <tr>
                                                <th>APELLIDOS</th>
                                                <th>NOMBRES</th>
                                                <th>CÉDULA</th>
                                                <th>EDAD</th>
                                                <th>ÁREA DE TRABAJO</th>
                                                <th>CORREO ELECTRÓNICO</th>
                                                <th></th>
                                                <th></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($personas as $persona) : ?>
                                                <tr>
                                                    <th><?= $persona["per_apellidos"] ?></th>
                                                    <td><?= $persona["per_nombres"] ?></td>
                                                    <td><?= $persona["cedula"] ?></td>
                                                    <td>
                                                        <?php
                                                        // Calcular la edad a partir de la fecha de nacimiento
                                                        $birthdate = new DateTime($persona["per_fechaNacimiento"]);
                                                        $today = new DateTime();
                                                        $age = $today->diff($birthdate)->y;
                                                        echo $age;
                                                        ?>
                                                    </td>
                                                    <td><?= $persona["per_areaTrabajo"] ?></td>
                                                    <td><?= $persona["per_correo"] ?></td>
                                                    <td>
                                                        <a href="personas.php?id=<?= $persona["cedula"] ?>" class="btn btn-secondary mb-2">ACTUALIZAR</a>
                                                    </td>
                                                    <td>
                                                        <a href="./delete/personas.php?id=<?= $persona["cedula"] ?>" class="btn btn-danger mb-2">ELIMINAR</a>
                                                    </td>
                                                </tr>
                                            <?php endforeach ?>

                                        </tbody>
                                    </table>
                                <?php endif ?>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </div>
</section>

<?php require "./partials/footer.php"; ?>