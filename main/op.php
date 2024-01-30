<?php
require  "../sql/database2.php";
session_start();
//si la sesion no existe, mandar al login.php y dejar de ejecutar el resto; se puede hacer un required para ahorra codigo
if (!isset($_SESSION["user"])) {
    header("Location: ../login-form/login.php");
    return;
  }
  //declaramos la variable error que nos ayudara a mostrar errores, etc.
$error = null;
$state = 1;

if(($_SESSION["user"]["ROL"])&&($_SESSION["user"]["ROL"]==1)){
    //llamr los contactos de la base de datos y especificar que sean los que tengan la op_id de la funcion seccion_start
    $op=$conn->query("SELECT*FROM OP");
    //VERFIFICAMOS EL METODOD QUE SE USA EL FORM CON UN IF 
    if($_SERVER["RQUEST_METHOD"]=="POST"){
        //VALIDFAMOS QUE NO SE MANDEN DATOS VASIOS
        if(empty($_POST["cedula"])){

        }else{

        }

    }
    

}else{
    header("Location:./index.php");
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
                    <h5 class="card_title">Nuevo Registro de la OP</h5>
                    <?php if($error): ?>
                        <p class="text_danger">
                            <?=$error?>
                        </p>
                    <!--si hay un error mandar un danger -->

                    <?php  endif?>
                    <form class="row g-3"method="POST" action="op.php">
                        <div class="col-md-6">
                            <div class="form-floating">
                                
                        </div>
                    </form>

                </div>

            </div>
        </div>
    </div>
</section>