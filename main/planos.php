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
$idop = isset($_GET["idop"]) ? $_GET["idop"] : null; 
$opInfo = null;
$opPlanos = null;

if ($_SESSION["user"]["ROL"] && $_SESSION["user"]["ROL"] == 1) {
    // Verificamos el método que usa el formulario con un if
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        // Validamos que no se manden datos vacíos
        if (empty($_POST["idop"])) {
            $error = "POR FAVOR RELLENA TODOS LOS CAMPOS";
        } else {
            // Obtener la información de la OP y sus planos
            $opInfoStatement = $conn->prepare("SELECT * FROM OP WHERE IDOP = :idop AND OPNOTIFICACIONCORREO != 'OP CREADA'");
            $opInfoStatement->bindParam(":idop", $_POST["idop"]);
            $opInfoStatement->execute();
            $opInfo = $opInfoStatement->fetch(PDO::FETCH_ASSOC);

            // Obtener los planos asociados a la OP
            $opPlanosStatement = $conn->prepare("SELECT * FROM PLANOS WHERE IDOP = :idop");
            $opPlanosStatement->bindParam(":idop", $_POST["idop"]);
            $opPlanosStatement->execute();
            $opPlanos = $opPlanosStatement->fetchAll(PDO::FETCH_ASSOC);
        }
    }
}

?>

<?php require "./partials/header.php"; ?>
<?php require "./partials/dashboard.php"; ?>
<section class="section">
    <div class="row">
        <div class="">
            <!-- Código para buscar OP por IDOP -->
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Buscar OP por IDOP</h5>

                    <!-- si hay un error mandar un danger -->
                    <?php if ($error): ?> 
                        <p class="text-danger">
                            <?= $error ?>
                        </p>
                    <?php endif ?>
                    <form class="row g-3" method="POST" action="planos.php">
                        <div class="col-md-12">
                            <div class="form-floating mb-3">
                                <input type="text" class="form-control" id="idop" name="idop" placeholder="IDOP">
                                <label for="idop">IDOP</label>
                            </div>
                        </div>
                        <div class="text-center">
                            <button type="submit" class="btn btn-primary">Buscar</button>
                            <button type="reset" class="btn btn-secondary">Limpiar</button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Mostrar información de la OP y sus planos -->
            <?php if ($opInfo): ?>
                <section class="section">
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="card">
                                <div class="card-body">
                                    <h5 class="card-title">Datos de la OP</h5>
                                    <p>IDOP: <?= $opInfo["IDOP"] ?></p>
                                    <p>Cliente: <?= $opInfo["OPCLIENTE"] ?></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <?php if ($opPlanos): ?>
                    <section class="section">
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="card">
                                    <div class="card-body">
                                        <h5 class="card-title">Planos de la OP</h5>
                                        <!-- si el array asociativo $opPlanos no tiene nada dentro, entonces imprimir el siguiente div -->
                                        <?php if (empty($opPlanos)): ?>
                                            <div class="col-md-4 mx-auto mb-3">
                                                <div class="card card-body text-center">
                                                    <p>Busque una OP</p>
                                                </div>
                                            </div>
                                        <?php else: ?>
                                            <!-- Table with stripped rows -->
                                            <table class="table datatable">
                                                <thead>
                                                    <tr>
                                                        <th>Número de Plano</th>
                                                        <th>Estado</th>
                                                        <th></th>
                                                        <th></th>
                                                        <th></th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach ($opPlanos as $opPlano): ?>
                                                        <tr>
                                                            <td><?= $opPlano["PLANNUMERO"] ?></td>
                                                            <td>
                                                                <?php
                                                                    if ($opPlano["PLAESTADO"] == 1 ) {
                                                                        echo("Activo");
                                                                    } elseif ($opPlano["PLAESTADO"] == 2 ) {
                                                                        echo("Pausado");
                                                                    } elseif ($opPlano["PLAESTADO"] == 3 ) {
                                                                        echo("Anulado");
                                                                    } 
                                                                ?>
                                                            </td>
                                                            <td>
                                                                <?php if($opPlano["PLAESTADO"] == 1 ) : ?>
                                                                    <a href="#" class="btn btn-primary mb-2">Pausar</a>
                                                                <?php elseif($opPlano["PLAESTADO"] == 2 ) : ?>
                                                                    <a href="#" class="btn btn-success mb-2">Activar</a>
                                                                <?php else : ?>
                                                                <?php endif ?>
                                                            </td>
                                                            <td>
                                                                <?php if($opPlano["PLANOTIFICACION"] == 0 ) : ?>
                                                                    <a href="./validaciones/notiPlano.php?id=<?= $opPlano["IDPLANO"] ?>" class="btn btn-warning mb-2">Notificar problema</a>
                                                                <?php else : ?>
                                                                <?php endif ?>
                                                            </td>
                                                            <td>
                                                                <?php if($opPlano["PLAESTADO"] !== 3 ) : ?>
                                                                    <a href="#" class="btn btn-danger mb-2">Anular</a>
                                                                <?php elseif($opPlano["PLAESTADO"] == 3 ) : ?>
                                                                    <a href="#" class="btn btn-success mb-2">Reanudar</a>
                                                                <?php else : ?>
                                                                <?php endif ?>
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
                <?php endif ?>
            <?php endif ?>
        </div>
    </div>
</section>

<?php require "./partials/footer.php"; ?>
