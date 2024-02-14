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
    // Llamamos los contactos de la base de datos y especificamos que sean los que tengan el usu_id de la función session_start
    $usuarios = $conn->query("SELECT * FROM USUARIOS");
    $personas = $conn->query("SELECT * FROM PERSONAS");

    // Verificamos el método que usa el formulario con un if
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        // Validamos que no se manden datos vacíos
        if (empty($_POST["cedula"]) || empty($_POST["usuario"]) || empty($_POST["password"]) || empty($_POST["rol"])) {
            $error = "POR FAVOR RELLENA TODOS LOS CAMPOS";
        } elseif (!preg_match('/^(?=.*[A-Z])(?=.*[0-9])(?=.*[!@#$%^&*()-_+=])[A-Za-z0-9!@#$%^&*()-_+=]{6,}$/', $_POST["password"])) {
            $error = "La contraseña debe tener al menos 6 caracteres y contener al menos una letra mayúscula, un número y un carácter especial.";
        } else {
            // Verificamos si ya existe un registro para el usuario actual
            $existingStatement = $conn->prepare("SELECT ID_USER FROM USUARIOS WHERE CEDULA = :cedula");
            $existingStatement->execute([":cedula" => $_POST['cedula']]);
            $existingUsuario = $existingStatement->fetch(PDO::FETCH_ASSOC);
        
            if ($existingUsuario) {
                // Si existe, actualizamos el registro existente
                $statement = $conn->prepare("UPDATE USUARIOS SET
                    USER = :usuario,
                    ROL = :rol
                    WHERE ID_USER = :id");
        
                $statement->execute([
                    ":id" => $existingUsuario["ID_USER"],
                    ":usuario" => $_POST["usuario"],
                    ":rol" => $_POST["rol"],
                ]);
                // Registramos el movimiento en el kardex
                registrarEnKardex($_SESSION["user"]["ID_USER"], $_SESSION["user"]["USER"], "EDITO", 'USUARIOS', $_POST["usuario"]);
            } else {
                // Si no existe, insertamos un nuevo registro
                $statement = $conn->prepare("INSERT INTO USUARIOS (CEDULA, USER, PASSWORD, ROL, REGISTRO) 
                                              VALUES (:cedula, :usuario, :password, :rol, CURRENT_TIMESTAMP)");
        
                $statement->execute([
                    ":cedula" => $_POST["cedula"],
                    ":usuario" => $_POST["usuario"],
                    ":password" => password_hash($_POST["password"], PASSWORD_BCRYPT),
                    ":rol" => $_POST["rol"],
                ]);
                // Registramos el movimiento en el kardex
                registrarEnKardex($_SESSION["user"]["ID_USER"], $_SESSION["user"]["USER"], "CREO", 'USUARIOS', $_POST["usuario"]);
            }
        
        

            // Redirigimos a home.php
            header("Location: usuarios.php");
            return;
        }
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
            <?php if (empty($id)) : ?>
            <div class="card accordion" id="accordionExample">
                <div class="card-body accordion-item">
                    <h5 class="card-title accordion-header" id="headingOne">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
                            Nuevo Usuario
                        </button>
                    </h5>

                    <div id="collapseOne" class="accordion-collapse collapse" aria-labelledby="headingOne" data-bs-parent="#accordionExample">
                        <div class="accordion-body">
                            <!-- si hay un error mandar un danger -->
                            <?php if ($error): ?> 
                                <p class="text-danger">
                                    <?= $error ?>
                                </p>
                            <?php endif ?>
                            <form class="row g-3" method="POST" action="usuarios.php">
                            <div class="col-md-6">
                                <div class="form-floating mb-3">
                                    <input type="text" class="form-control" id="cedula" name="cedula" placeholder="Buscar por Cedula" list="cedulaList" oninput="buscarPorCedula()">
                                    <label for="cedula">Cedula</label>
                                    <datalist id="cedulaList">
                                        <?php foreach ($personas as $persona): ?>
                                        <option value="<?= $persona["CEDULA"]?>">
                                        <?php endforeach ?>
                                    </datalist>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-floating">
                                    <input type="text" class="form-control" id="nombre" name="nombre" placeholder="Trabajador" readonly>
                                    <label for="nombre">Trabajador</label>
                                </div>
                            </div>




                            <div class="col-md-6">
                                <div class="form-floating">
                                <input type="text" class="form-control" id="usuario" name="usuario" placeholder="usuario">
                                <label for="usuario">usuario</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-floating d-flex">
                                <input type="password" class="form-control" id="password" name="password" placeholder="password">
                                <label for="password">Contraseña</label>
                                <button id="show_password" class="btn btn-primary" type="button" onclick="mostrarPassword()"> <span class="fa fa-eye-slash icon"></span> </button>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-floating mb-3">
                                <select class="form-select" id="rol" aria-label="State" name="rol">
                                    <option value="1">Administrador</option>
                                    <option value="2">Empleado</option>
                                    <option value="3">Diseño Grafico</option>
                                </select>
                                <label for="rol">Rol de Usuario</label>
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
            <?php else : ?>
                <?php 
                    $statement = $conn->prepare("SELECT U.*, P.* 
                                                FROM USUARIOS U
                                                INNER JOIN PERSONAS P ON U.CEDULA = P.CEDULA
                                                WHERE U.ID_USER = :id");

                    $statement->bindParam(":id", $id);
                    $statement->execute();
                    $usuarioEditar = $statement->fetch(PDO::FETCH_ASSOC);

                ?>
                <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Editar Usuario</h5>

                    <!-- si hay un error mandar un danger -->
                    <?php if ($error): ?> 
                        <p class="text-danger">
                            <?= $error ?>
                        </p>
                    <?php endif ?>
                    <form class="row g-3" method="POST" action="usuarios.php">
                    <?php
                    $nombreTrabajador = isset($usuarioEditar['PERNOMBRES']) ? $usuarioEditar['PERNOMBRES'] : '';
                    $nombreTrabajador .= isset($usuarioEditar['PERAPELLIDOS']) ? ' ' . $usuarioEditar['PERAPELLIDOS'] : '';
                    ?>
                    <div class="col-md-6">
                        <div class="form-floating mb-3">
                            <input value="<?= $usuarioEditar['CEDULA'] ?>" type="text" class="form-control" id="cedula" name="cedula" placeholder="Buscar por Cedula" list="cedulaList" oninput="buscarPorCedula()" readonly>
                            <label for="cedula">Cedula</label>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-floating">
                            <input value="<?= $nombreTrabajador ?>" id="trabajadorInfo" id="trabajadorInfo" type="text" class="form-control" id="nombre" name="nombre" placeholder="Trabajador" readonly>
                            <label for="nombre">Trabajador</label>
                        </div>
                    </div>




                    <div class="col-md-6">
                        <div class="form-floating">
                        <input value="<?= $usuarioEditar['USER'] ?>" type="text" class="form-control" id="usuario" name="usuario" placeholder="usuario">
                        <label for="usuario">Usuario</label>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-floating mb-3">
                            <select class="form-select" id="rol" aria-label="State" name="rol">
                                <option value="1" <?= ($usuarioEditar['ROL'] == 1) ? 'selected' : '' ?>>Administrador</option>
                                <option value="2" <?= ($usuarioEditar['ROL'] == 2) ? 'selected' : '' ?>>Empleado</option>
                                <option value="3" <?= ($usuarioEditar['ROL'] == 3) ? 'selected' : '' ?>>Diseño Grafico</option>
                            </select>
                            <label for="rol">Rol de Usuario</label>
                        </div>
                    </div>

                    <div class="text-center">
                        <button type="submit" class="btn btn-primary">Actualizar</button>
                        <button type="reset" class="btn btn-secondary">Limpiar</button>
                    </div>
                    </form>

                </div>
            </div>
            <?php endif ?>
            <section class="section">
                <div class="row">
                    <div class="col-lg-12">

                    <div class="card">
                        <div class="card-body">
                        <h5 class="card-title">Usuarios</h5>
                        <!-- si el array asociativo $teachers no tiene nada dentro, entonces imprimir el siguiente div -->
                        <?php if ($usuarios->rowCount() == 0): ?>
                            <div class= "col-md-4 mx-auto mb-3">
                                <div class= "card card-body text-center">
                                    <p>No hay Usuarios Aun.</p>
                                </div>
                            </div>
                        <?php else: ?>
                        <!-- Table with stripped rows -->
                        <table class="table datatable">
                            <thead>
                            <tr>
                                <th>CEDULA</th>
                                <th>USER</th>
                                <th>ROL</th>
                                <th>REGISTRO</th>
                                <th></th>
                                <th></th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php foreach ($usuarios as $usu): ?>
                                <tr>
                                <th><?= $usu["CEDULA"]?></th>
                                <td><?= $usu["USER"]?></td>
                                <td><?= $usu["ROL"]?></td>
                                <td><?= $usu["REGISTRO"]?></td>
                                <td>
                                    <a href="usuarios.php?id=<?= $usu["ID_USER"] ?>" class="btn btn-secondary mb-2">Editar</a>
                                </td>
                                <td>
                                    <a href="cambiar_contrasena.php?id=<?= $usu["CEDULA"] ?>" class="btn btn-danger mb-2">Cambiar Contraseña</a>
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
