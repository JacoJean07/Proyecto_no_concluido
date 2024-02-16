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
if($_SESSION["user"]["ROL"] && $_SESSION["user"]["ROL"] ==1){
    //VERIFICAMOS EL METODO QUE SE USA CON EL IF
    if($_SERVER["REQUEST_METHOD"] =="POST"){
        //VALIDAMOS QUE LOS DATOS NO ESTEN VACIOS
        if(empty($_POST["idop"])){
            $error = "POR FAVOR DEBE RELLENAR EL CAMPO DE LA OP";
        }else{
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
            <!-- Código para buscar OP por IDOP -->
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Bucar Op por el numero de la Op</h5>
                    <?php if ($error) : ?>
                        <p class="text-danger">
                            <?= $error ?>
                        </p>
                    <?php endif ?>
                    <form class="row g-3" method="POST" action="opcionesOp.php">
                        <div class="col-md-12">
                            <div class="form-floating mb-3">
                                <input type="number" class="form-control" id="idop" name="idop" placeholder="IDOP">
                                <label for="idop">Número de OP</label>
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
            <?php if($opInfo): ?>
                <section class="section">
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="card">
                                <div class="card-body">
                                    <h5 class="card-title">Datos de la OP</h5>
                                    <p>Número de OP:  <?= $opInfo["IDOP"] ?></p>
                                    <p>Cliente: <?= $opInfo["OPCLIENTE"] ?></p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <section class="section">
                        <div class="row">
                            <div class="card">
                                <ul class="nav nav-tabs" id="myTab" role="tablist">
                                    <li class="na-item" role ="presentation">
                                        <button class="na-link active" id="" data-bs-toggle="tab" data-bs-target=""></button>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </section>
                </section>
            <?php endif ?>
        </div>
    </div>
</section>
<?php require "./partials/footer.php"; ?>