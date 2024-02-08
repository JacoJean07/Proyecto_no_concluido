<?php
require  "../sql/database.php";
require "./partials/kardex.php";

session_start();
//si la sesion no existe, mandar al login.php y dejar de ejecutar el resto; se puede hacer un required para ahorra codigo
if (!isset($_SESSION["user"])) {
    header("Location: ../login-form/login.php");
    return;
}
//declaramos la variable error que nos ayudara a mostrar errores, etc.
$error = null;

$id = isset($_GET["id"]) ? $_GET["id"] : null;

if (($_SESSION["user"]["ROL"]) && ($_SESSION["user"]["ROL"] == 1)) {
    
} else {
    header("Location:./index.php");
    return;
}   
?>
<?php require "./partials/header.php"; ?>
<?php require "./partials/dashboard.php"; ?>

<section class="section">
    <div class="row">
        <div class="">
            <div class="card accordion" id="accordionExample">
            <h5 class="card-title accordion-header">
                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
                    Actuakizar Registro de Formulario de cambioLogistica
                </button>
            </h5>
            <?php if ($error) : ?>
                <p class="text_danger">
                    <?= $error ?>
                </p>
            
            <?php endif ?>
            </div>
        </div>
    </div>
</section>
<?php require "./partials/footer.php"; ?>