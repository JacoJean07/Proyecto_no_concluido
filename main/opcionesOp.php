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
    //VERIFICAMOS EL METODO QUE SE USA CON EL IF
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        //VALIDAMOS QUE LOS DATOS NO ESTEN VACIOS
        if (empty($_POST["idop"])) {
            $error = "POR FAVOR DEBE RELLENAR EL CAMPO DE LA OP";
        } else {
            //OBTENER LA INFOREMACION DE LA OP
            $opInfoStatement = $conn->prepare("SELECT * FROM OP WHERE IDOP = :idop AND OPNOTIFICACIONCORREO ");
            $opInfoStatement->bindParam(":idop", $_POST['idop']);
            $opInfoStatement->execute();
            $opInfo = $opInfoStatement->fetch(PDO::FETCH_ASSOC);
        }
    }
}
?>
<?php require "./partials/header.php"; ?>
<?php require "./partials/dashboard.php"; ?>
<section class="section">
    <div class="row">
        <div class="">
            <div class="card">
                <h5 class="card-title">Litas de lo tipos de Op</h5>
                <ul class="nav nav-tabs" id="myTab" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="estado-tab" data-bs-toggle="tab" data-bs-target="#estado" type="button" role="tab" aria-controls="estado" aria-selected="true">Cambio de los etados de las OP</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="finalizada-tab" data-bs-toggle="tab" data-bs-target="#finalizado" type="button" role="tab" aria-controls="finalizado" aria-selected="false" tabindex="-1">Op's Finalizadas</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="anulado-tab" data-bs-toggle="tab" data-bs-target="#anulado" type="button" role="tab" aria-controls="anulado" aria-selected="false" tabindex="-2">Op's Anuladas</button>
                    </li>
                </ul>
                <div class="tab-content pt-2" id="myTabContent">
                    <div class="tab-pane fade show active" id="estado" role="tabpanel" aria-labelledby="estado-tab">
                        <section class="section">
                            <div class="row">
                                <div class="col-lg-12">
                                    <div class="card">
                                        <div class="card-body">
                                            <div class="card-header">
                                                <h5 class="card-title">cambios Op</h5>
                                            </div>
                                            <table class="table datatable">
                                                <thead>
                                                    <tr>
                                                        <th>Op</th>
                                                    </tr>
                                                </thead>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </section>
                    </div>
                    <div class="tab-pane fade" id="finalizado" role="tabpanel" aria-labelledby="finalizado-tab">
                        <section class="section">
                            <div class="row">
                                <div class="col-lg-12">
                                    <div class="card">
                                        <div class="card-body">
                                            <div class="card-header">
                                                <h5 class="card-title">Op's Finalizadas</h5>
                                            </div>
                                            <table>

                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </section>
                    </div>
                    <div class="tab-pane fade" id="anulado" role="tabpanel" aria-labelledby="anulado-tab">
                        <section class="section">
                            <div class="row">
                                <div class="col-lg-12">
                                    <div class="card">
                                        <div class="card-body">
                                            <div class="card-header">
                                                <h5 class="card-title">Op's Anulados</h5>
                                            </div>
                                            <table>

                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </section>
                    </div>
                </div>

            </div>
        </div>
    </div>
</section>
<?php require "./partials/footer.php"; ?>