<?php 
require "../sql/database.php";
require "./partials/kardex.php";

session_start();
// validacion de la sesion
if (!isset($_SESSION["user"])) {
    header("Location: ../login-form/login.php");
    return;
}
// declaramos variables
$error = null;
$state = 2;

$id = isset($_GET["id"]) ? $_GET["id"] : null;
$odEditar = null;
if (($_SESSION["user"]["ROL"]) && ($_SESSION["user"]["ROL"] == 1)) {
    // llamar a las ordenes de disenio
    $od = $conn->query("SELECT * FROM ORDENDISENIO");

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        
        // VALIDACIONES DE INPUTS
        if (empty($_POST["campania"]) || empty($_POST["marca"]) || empty($_POST["producto"]) || empty($_POST["fechaEntrega"])) {
            $error = "POR FAVOR LLENAR TODOS LOS CAMPOS.";
        } elseif (!preg_march('/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/', $_POST["campania"] || $_POST["marca"] || $_POST["producto"])) {
            $error = "Ingrese un texto valido.";
        }
    }
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
                        <h5 class="card-title accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
                                NUEVA OP
                            </button>
                        </h5>

                        <?php if ($error) : ?>
                            <p>
                                <?= $error ?>
                            </p>
                        <?php endif ?>
                        <div id="collapseOne" class="accordion-collapse collapse" aria-labelledby="headingOne" data-bs-parent="#accordionExample">
                            <div class="accordion-body">
                                <form class="row g-3" method="POST" action="od.php">
                                    <div class="col-md-6">
                                        <div class="form-floating mb-3">
                                            <input type="text" class="form-control" id="campania" name="campania" placeholder="Buscar por nombre" list="campaniaList" autocomplete="campania" required>
                                            <label for="campania">Ingrese la campaña</label>
                                            <datalist id="">

                                            </datalist>
                                        </div>
                                    </div>
                                </form>

                            </div>

                        </div>
                    </div>
                </div>
            <?php endif ?>
        </div>
    </div>
</section>