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
$state = 1;
$id = isset($_GET["id"]) ? $_GET["id"] : null; 
$personaEditar = null;

if (($_SESSION["user"]["ROL"]) && ($_SESSION["user"]["ROL"] == 1)) {
    // Llamar los contactos de la base de datos y especificar que sean los que tengan el persona_id de la función session_start
    $personas = $conn->query("SELECT * FROM personas WHERE PERESTADO = 1");

    // Verificamos el método que usa el formulario con un if
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        // Validamos que no se manden datos vacíos
        if (empty($_POST["cedula"]) || empty($_POST["nombres"]) || empty($_POST["apellidos"]) || empty($_POST["nacimiento"]) || empty($_POST["areatrabajo"])) {
            $error = "POR FAVOR RELLENA TODOS LOS CAMPOS";
        } elseif (!preg_match('/^[0-9]{10}$/', $_POST["cedula"])) {
            $error = "La cédula debe contener 10 dígitos numéricos.";
        } elseif (!preg_match('/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/', $_POST["nombres"])) {
            $error = "Nombres inválidos.";
        } elseif (!preg_match('/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/', $_POST["apellidos"])) {
            $error = "Apellidos inválidos.";
        } elseif (empty($_POST["nacimiento"])) {
            $error = "La fecha de nacimiento es obligatoria.";
        } elseif (empty($_POST["areatrabajo"])) {
            $error = "El área de trabajo es obligatoria.";
        } elseif (!filter_var($_POST["correo"], FILTER_VALIDATE_EMAIL)) {
            $error = "El formato del correo electrónico no es válido.";
        } else {
            // Verificar si la cédula ya existe (excepto para el ID que estamos editando)
            $existingStatement = $conn->prepare("SELECT COUNT(*) FROM PERSONAS WHERE CEDULA = :cedula AND CEDULA != :id");
            $existingStatement->execute([
                ":cedula" => $_POST['cedula'],
                ":id" => $id,
            ]);
            $count = $existingStatement->fetchColumn();

            if ($count > 0) {
                $error = "Ya existe un trabajador con esta cédula.";
            } else {
                // Sanitizamos los datos para evitar inyecciones SQL
                $cedula = $_POST["cedula"];
                $nombres = $_POST["nombres"];
                $apellidos = $_POST["apellidos"];
                $nacimiento = $_POST["nacimiento"];
                $estado = $state;
                $areatrabajo = $_POST["areatrabajo"];
                $correo = $_POST["correo"];

                if ($id) {
                    // Si hay un ID, estamos editando, por lo que actualizamos el registro existente
                    $statement = $conn->prepare("UPDATE PERSONAS SET CEDULA = :cedula, PERNOMBRES = :nombres, PERAPELLIDOS = :apellidos, PERFECHANACIMIENTO = :nacimiento, PERAREATRABAJO = :areatrabajo, PERCORREO = :correo WHERE CEDULA = :id");
                    $statement->execute([
                        ":id" => $id,
                        ":cedula" => $cedula,
                        ":nombres" => $nombres,
                        ":apellidos" => $apellidos,
                        ":nacimiento" => $nacimiento,
                        ":areatrabajo" => $areatrabajo,
                        ":correo" => $correo,
                    ]);
                    // Registramos el movimiento en el kardex
                    registrarEnKardex($_SESSION["user"]["ID_USER"], $_SESSION["user"]["USER"], "EDITO", 'PERSONAS', $cedula);
                
                
                } else {
                    // Si no hay un ID, estamos insertando un nuevo registro
                    $statement = $conn->prepare("INSERT INTO PERSONAS ( CEDULA, PERNOMBRES, PERAPELLIDOS, PERFECHANACIMIENTO, PERESTADO, PERAREATRABAJO, PERCORREO) VALUES (:cedula, :nombres, :apellidos, :nacimiento, :estado, :areatrabajo, :correo)");
                    
                    // Ejecutamos
                    $statement->execute([
                        ":cedula" => $cedula,
                        ":nombres" => $nombres,
                        ":apellidos" => $apellidos,
                        ":nacimiento" => $nacimiento,
                        ":areatrabajo" => $areatrabajo,
                        ":estado" => $estado,
                        ":correo" => $correo,
                    ]);
                    // Registramos el movimiento en el kardex
                    registrarEnKardex($_SESSION["user"]["ID_USER"], $_SESSION["user"]["USER"], "CREO", 'PERSONAS', $_POST["cedula"]);
                }

                // Redirigimos a personas.php
                header("Location: personas.php");
                return;
            }
        }
    }

    // Obtenemos la información de la persona a editar
    if ($id) {
        $statement = $conn->prepare("SELECT * FROM PERSONAS WHERE CEDULA = :id");
        $statement->bindParam(":id", $id);
        $statement->execute();
        $personaEditar = $statement->fetch(PDO::FETCH_ASSOC);
    }

} else {
    header("Location: ./index.php");
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
                    <?php if ($id): ?>
                        <h5 class="card-title">Editar Trabajador</h5>
                    <?php else: ?>
                        <h5 class="card-title">Nuevo Trabajador</h5>
                    <?php endif ?>

                    <!-- si hay un error mandar un danger -->
                    <?php if ($error): ?> 
                        <p class="text-danger">
                            <?= $error ?>
                        </p>
                    <?php endif ?>
                    <form class="row g-3" method="POST" action="personas.php<?= $id ? "?id=$id" : "" ?>">
                        <div class="col-md-6">
                            <div class="form-floating">
                                <input type="text" class="form-control" id="cedula" name="cedula" placeholder="Cedula" value="<?= $personaEditar ? $personaEditar["CEDULA"] : "" ?>">
                                <label for="cedula">Cedula</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-floating">
                                <input type="text" class="form-control" id="nombres" name="nombres" placeholder="Nombres" value="<?= $personaEditar ? $personaEditar["PERNOMBRES"] : "" ?>">
                                <label for="nombres">Nombres</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-floating">
                                <input type="text" class="form-control" id="apellidos" name="apellidos" placeholder="Apellidos" value="<?= $personaEditar ? $personaEditar["PERAPELLIDOS"] : "" ?>">
                                <label for="apellidos">Apellidos</label>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="form-floating">
                                <input type="date" class="form-control" placeholder="Nacimiento" id="nacimiento" name="nacimiento" value="<?= $personaEditar ? $personaEditar["PERFECHANACIMIENTO"] : "" ?>"></input>
                                <label for="nacimiento">Fecha de Nacimiento</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-floating mb-3">
                                <select class="form-select" id="areatrabajo" aria-label="State" name="areatrabajo">
                                    <option value="Carpinteria" <?= ($personaEditar && $personaEditar["PERAREATRABAJO"] == "Carpinteria") ? "selected" : "" ?>>Carpinteria</option>
                                    <option value="ACM" <?= ($personaEditar && $personaEditar["PERAREATRABAJO"] == "ACM") ? "selected" : "" ?>>ACM</option>
                                    <option value="Pintura" <?= ($personaEditar && $personaEditar["PERAREATRABAJO"] == "Pintura") ? "selected" : "" ?>>Pintura</option>
                                    <option value="Acrilicos y Acabados" <?= ($personaEditar && $personaEditar["PERAREATRABAJO"] == "Acrilicos y Acabados") ? "selected" : "" ?>>Acrilicos y Acabados</option>
                                    <option value="Maquinas" <?= ($personaEditar && $personaEditar["PERAREATRABAJO"] == "Maquinas") ? "selected" : "" ?>>Maquinas</option>
                                    <option value="Impresiones" <?= ($personaEditar && $personaEditar["PERAREATRABAJO"] == "Impresiones") ? "selected" : "" ?>>Impresiones</option>
                                    <option value="Diseno Grafico" <?= ($personaEditar && $personaEditar["PERAREATRABAJO"] == "Diseno Grafico") ? "selected" : "" ?>>Diseno Grafico</option>
                                </select>
                                <label for="areatrabajo">Area de Trabajo</label>
                            </div>
                        </div>
                        <div class="col-6">
                        <div class="form-floating">
                                <input type="text" class="form-control" id="correo" name="correo" placeholder="Correo" value="<?= $personaEditar ? $personaEditar["PERCORREO"] : "" ?>">
                                <label for="correo">Correo Electronico</label>
                            </div>
                        </div>
                        <div class="text-center">
                            <button type="submit" class="btn btn-primary"><?= $id ? "Actualizar" : "Submit" ?></button>
                            <button type="reset" class="btn btn-secondary">Reset</button>
                        </div>
                    </form>

                </div>
            </div>

            <section class="section">
                <div class="row">
                    <div class="col-lg-12">

                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title">Trabajadores</h5>
                                <!-- si el array asociativo $teachers no tiene nada dentro, entonces imprimir el siguiente div -->
                                <?php if ($personas->rowCount() == 0): ?>
                                    <div class= "col-md-4 mx-auto mb-3">
                                        <div class= "card card-body text-center">
                                            <p>No hay Trabajadores Aun.</p>
                                        </div>
                                    </div>
                                <?php else: ?>
                                <!-- Table with stripped rows -->
                                <table class="table datatable">
                                    <thead>
                                    <tr>
                                        <th>Apellidos</th>
                                        <th>Nombres</th>
                                        <th>Cedula</th>
                                        <th>Edad</th>
                                        <th>Area de Trabajo</th>
                                        <th>Correo Electronico</th>
                                        <th></th>
                                        <th></th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <?php foreach ($personas as $persona): ?>
                                        <tr>
                                        <th><?= $persona["PERAPELLIDOS"]?></th>
                                        <td><?= $persona["PERNOMBRES"]?></td>
                                        <td><?= $persona["CEDULA"]?></td>
                                        <td>
                                            <?php
                                            // Calcular la edad a partir de la fecha de nacimiento
                                            $birthdate = new DateTime($persona["PERFECHANACIMIENTO"]);
                                            $today = new DateTime();
                                            $age = $today->diff($birthdate)->y;
                                            echo $age;
                                            ?>
                                        </td>
                                        <td><?= $persona["PERAREATRABAJO"]?></td>
                                        <td><?= $persona["PERCORREO"]?></td>
                                        <td>
                                            <a href="personas.php?id=<?= $persona["CEDULA"] ?>" class="btn btn-secondary mb-2">Editar</a>
                                        </td>
                                        <td>
                                            <a href="./delete/personas.php?id=<?= $persona["CEDULA"] ?>" class="btn btn-danger mb-2">Eliminar</a>
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
