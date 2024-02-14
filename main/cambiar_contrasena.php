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
$usuarioEditar = null;

if ($_SESSION["user"]["ROL"] && $_SESSION["user"]["ROL"] == 1) {
    // Verificamos el método que usa el formulario con un if
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        // Validamos que no se manden datos vacíos
        if (empty($_POST["password"]) || empty($_POST["confirm_password"])) {
            $error = "POR FAVOR RELLENA TODOS LOS CAMPOS";
        } elseif ($_POST["password"] !== $_POST["confirm_password"]) {
            $error = "Las contraseñas no coinciden.";
        } elseif (!preg_match('/^(?=.*[A-Z])(?=.*[0-9])(?=.*[!@#$%^&*()-_+=])[A-Za-z0-9!@#$%^&*()-_+=]{6,}$/', $_POST["password"])) {
            $error = "La contraseña debe tener al menos 6 caracteres y contener al menos una letra mayúscula, un número y un carácter especial.";
        } else {
            // Actualizamos la contraseña del usuario
            $statement = $conn->prepare("UPDATE USUARIOS SET PASSWORD = :password WHERE ID_USER = :id");
            $statement->execute([
                ":id" => $id,
                ":password" => password_hash($_POST["password"], PASSWORD_BCRYPT),
            ]);
            // Registramos el movimiento en el kardex
            registrarEnKardex($_SESSION["user"]["ID_USER"], $_SESSION["user"]["USER"], "CAMBIO DE CONTRASEÑA", 'USUARIOS', "CONTRASEÑA<br>C.I.: " . $id );

            // Redirigimos a usuarios.php
            header("Location: usuarios.php");
            return;
        }
    }

    // Obtenemos la información del usuario para mostrarla en el formulario
    $statement = $conn->prepare("SELECT * FROM USUARIOS WHERE CEDULA = :id");
    $statement->bindParam(":id", $id);
    $statement->execute();
    $usuarioEditar = $statement->fetch(PDO::FETCH_ASSOC);

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
                    <h5 class="card-title">Cambiar Contraseña</h5>

                    <!-- si hay un error mandar un danger -->
                    <?php if ($error): ?> 
                        <p class="text-danger">
                            <?= $error ?>
                        </p>
                    <?php endif ?>
                    <form class="row g-3" method="POST" action="cambiar_contrasena.php?id=<?= $id ?>">
                        <div class="col-md-8">
                            <div class="form-floating">
                                <input type="text" class="form-control" id="usuario" name="usuario" value="<?= $usuarioEditar['USER'] ?>" readonly>
                                <label for="usuario">Usuario</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-floating d-flex">
                                <input type="password" class="form-control" id="password" name="password" placeholder="Nueva Contraseña">
                                <label for="password">Nueva Contraseña</label>
                                <button id="show_password" class="btn btn-primary" type="button" onclick="mostrarPassword()"> <span class="fa fa-eye-slash icon"></span> </button>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-floating d-flex">
                                <input type="password" class="form-control" id="confirm_password" name="confirm_password" placeholder="Confirmar Contraseña">
                                <label for="confirm_password">Confirmar Contraseña</label>
                            </div>
                        </div>
                        <div class="text-center">
                            <button type="submit" class="btn btn-primary">Actualizar Contraseña</button>
                            <a href="usuarios.php" class="btn btn-secondary">Cancelar</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>

<?php require "./partials/footer.php"; ?>
