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
$ordenEditar = null;

if ($_SESSION["user"]["ROL"] && $_SESSION["user"]["ROL"] == 1) {
    // Verificamos el método que usa el formulario con un if
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        // Validamos que no se manden datos vacíos
        if (empty($_POST["responsable"]) || empty($_POST["producto"]) || empty($_POST["campania"]) || empty($_POST["marca"]) || empty($_POST["fecha_entrega"])) {
            $error = "POR FAVOR RELLENA TODOS LOS CAMPOS";
        } else {
            // Verificamos si ya existe una orden de diseño para el producto actual
            $existingStatement = $conn->prepare("SELECT PRODUCTO FROM ORDENDISENIO WHERE PRODUCTO = :producto");
            $existingStatement->execute([":producto" => $_POST["producto"]]);
            $existingOrden = $existingStatement->fetch(PDO::FETCH_ASSOC);

            if ($existingOrden) {
                // Si existe, actualizamos la orden existente
                $statement = $conn->prepare("UPDATE ORDENDISENIO SET RESPONSABLE_CEDULA = :responsable, CAMPANIA = :campania, MARCA = :marca, FECHAENTREGA = :fecha_entrega, ESTADO = :estado WHERE PRODUCTO = :producto");
                $statement->execute([
                    ":producto" => $_POST["producto"],
                    ":responsable" => $_POST["responsable"],
                    ":campania" => $_POST["campania"],
                    ":marca" => $_POST["marca"],
                    ":fecha_entrega" => $_POST["fecha_entrega"],
                    ":estado" => $_POST["estado"]
                ]);

                // Registramos el movimiento en el kardex
                registrarEnKardex($_SESSION["user"]["ID_USER"], $_SESSION["user"]["USER"], "EDITÓ", 'ORDENES DE DISEÑO', $_POST["producto"]);
            } else {
                // Si no existe, insertamos una nueva orden
                $statement = $conn->prepare("INSERT INTO ORDENDISENIO (RESPONSABLE_CEDULA, PRODUCTO, CAMPANIA, MARCA, FECHAENTREGA, ESTADO) 
                                            VALUES (:responsable, :producto, :campania, :marca, :fecha_entrega, :estado)");

                $statement->execute([
                    ":responsable" => $_POST["responsable"],
                    ":producto" => $_POST["producto"],
                    ":campania" => $_POST["campania"],
                    ":marca" => $_POST["marca"],
                    ":fecha_entrega" => $_POST["fecha_entrega"],
                    ":estado" => $_POST["estado"]
                ]);

                // Registramos el movimiento en el kardex
                registrarEnKardex($_SESSION["user"]["ID_USER"], $_SESSION["user"]["USER"], "CREÓ", 'ORDENES DE DISEÑO', $_POST["producto"]);
            }

            // Redirigimos a od.php
            header("Location: od.php");
            return;
        }
    }

    // Llamamos las órdenes de diseño de la base de datos
    $ordenes = $conn->query("SELECT * FROM ORDENDISENIO");

    // Obtenemos la información de la orden a editar
    if (!empty($id)) {
        $statement = $conn->prepare("SELECT * FROM ORDENDISENIO WHERE PRODUCTO = :id");
        $statement->bindParam(":id", $id);
        $statement->execute();
        $ordenEditar = $statement->fetch(PDO::FETCH_ASSOC);
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
                <!-- Código para agregar una nueva orden de diseño -->
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Nueva Orden de Diseño</h5>

                        <!-- si hay un error mandar un danger -->
                        <?php if ($error): ?>
                            <p class="text-danger">
                                <?= $error ?>
                            </p>
                        <?php endif ?>
                        <form class="row g-3" method="POST" action="od.php">
                            <div class="col-md-6">
                                <div class="form-floating mb-3">
                                    <input type="text" class="form-control" id="responsable" name="responsable" placeholder="Responsable" autocomplete="responsable" required>
                                    <label for="responsable">Responsable</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-floating mb-3">
                                    <input type="text" class="form-control" id="producto" name="producto" placeholder="Producto" autocomplete="producto" required>
                                    <label for="producto">Producto</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-floating mb-3">
                                    <input type="text" class="form-control" id="campania" name="campania" placeholder="Campaña" autocomplete="campania" required>
                                    <label for="campania">Campaña</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-floating mb-3">
                                    <input type="text" class="form-control" id="marca" name="marca" placeholder="Marca" autocomplete="marca" required>
                                    <label for="marca">Marca</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-floating mb-3">
                                    <input type="datetime-local" class="form-control" id="fecha_entrega" name="fecha_entrega" placeholder="Fecha de Entrega" autocomplete="fecha_entrega" required>
                                    <label for="fecha_entrega">Fecha de Entrega</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-floating mb-3">
                                    <select class="form-select" id="estado" name="estado" required>
                                        <option value="2" selected>Desaprobada</option>
                                        <option value="1">Aprobada</option>
                                    </select>
                                    <label for="estado">Estado</label>
                                </div>
                            </div>
                            <div class="text-center">
                                <button type="submit" class="btn btn-primary">Guardar</button>
                                <button type="reset" class="btn btn-secondary">Limpiar</button>
                            </div>
                        </form>
                    </div>
                </div>
            <?php else : ?>
                <!-- Código para editar una orden de diseño existente -->
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Editar Orden de Diseño</h5>

                        <!-- si hay un error mandar un danger -->
                        <?php if ($error): ?>
                            <p class="text-danger">
                                <?= $error ?>
                            </p>
                        <?php endif ?>
                        <form class="row g-3" method="POST" action="od.php?id=<?= $id ?>">
                            <div class="col-md-6">
                                <div class="form-floating mb-3">
                                    <input type="text" class="form-control" id="responsable" name="responsable" placeholder="Responsable" autocomplete="responsable" value="<?= $ordenEditar["RESPONSABLE_CEDULA"] ?>">
                                    <label for="responsable">Responsable</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-floating mb-3">
                                    <input type="text" class="form-control" id="producto" name="producto" placeholder="Producto" autocomplete="producto" value="<?= $ordenEditar["PRODUCTO"] ?>">
                                    <label for="producto">Producto</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-floating mb-3">
                                    <input type="text" class="form-control" id="campania" name="campania" placeholder="Campaña" autocomplete="campania" value="<?= $ordenEditar["CAMPANIA"] ?>">
                                    <label for="campania">Campaña</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-floating mb-3">
                                    <input type="text" class="form-control" id="marca" name="marca" placeholder="Marca" autocomplete="marca" value="<?= $ordenEditar["MARCA"] ?>">
                                    <label for="marca">Marca</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-floating mb-3">
                                    <input type="datetime-local" class="form-control" id="fecha_entrega" name="fecha_entrega" placeholder="Fecha de Entrega" autocomplete="fecha_entrega" value="<?= date('Y-m-d\TH:i', strtotime($ordenEditar["FECHAENTREGA"])) ?>">
                                    <label for="fecha_entrega">Fecha de Entrega</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-floating mb-3">
                                    <select class="form-select" id="estado" name="estado">
                                        <option value="2" <?php if ($ordenEditar["ESTADO"] == 2) echo "selected" ?>>Desaprobada</option>
                                        <option value="1" <?php if ($ordenEditar["ESTADO"] == 1) echo "selected" ?>>Aprobada</option>
                                    </select>
                                    <label for="estado">Estado</label>
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
                                <h5 class="card-title">Órdenes de Diseño</h5>
                                <!-- si el array asociativo $ordenes no tiene nada dentro, entonces imprimir el siguiente div -->
                                <?php if ($ordenes->rowCount() == 0): ?>
                                    <div class= "col-md-4 mx-auto mb-3">
                                        <div class= "card card-body text-center">
                                            <p>No hay Órdenes de Diseño aún.</p>
                                        </div>
                                    </div>
                                <?php else: ?>
                                    <!-- Table with stripped rows -->
                                    <table class="table datatable">
                                        <thead>
                                            <tr>
                                                <th>RESPONSABLE</th>
                                                <th>PRODUCTO</th>
                                                <th>CAMPAÑA</th>
                                                <th>MARCA</th>
                                                <th>FECHA DE ENTREGA</th>
                                                <th>ESTADO</th>
                                                <th></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($ordenes as $orden): ?>
                                                <tr>
                                                    <td><?= $orden["RESPONSABLE_CEDULA"] ?></td>
                                                    <td><?= $orden["PRODUCTO"] ?></td>
                                                    <td><?= $orden["CAMPANIA"] ?></td>
                                                    <td><?= $orden["MARCA"] ?></td>
                                                    <td><?= date('d-m-Y H:i', strtotime($orden["FECHAENTREGA"])) ?></td>
                                                    <td><?= $orden["ESTADO"] == 1 ? "Aprobada" : "Desaprobada" ?></td>
                                                    <td>
                                                        <a href="od.php?id=<?= $orden["PRODUCTO"] ?>" class="btn btn-secondary mb-2">Editar</a>
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
<!-- hola buenas -->
<?php require "./partials/footer.php"; ?>
