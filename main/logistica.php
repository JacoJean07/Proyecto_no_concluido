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
$state = "REGISTRO SIN FINALIZAR";
$id = isset($_GET["id"]) ? $_GET["id"] : null;
$logisticaEdiatar = null;
if(($_SESSION["user"]["ROL"]) && ($_SESSION["user"]["ROL"] == 1)){
    $logistica = $conn->query("SELECT LOGI.*, 
                                CEDULA.PERNOMBRES AS CEDULA_NOMBRES, CEDULA.PERAPELLIDOS AS CEDULA_APELLDIOS,
                                PLANOS.PLANNUMERO  AS IDOP  
                           FROM LOGISTICA AS LOGI
                           JOIN PERSONAS AS CEDULA ON LOGI.LOGCEDULA = CEDULA.CEDULA 
                           LEFT JOIN PLANOS   ON LOGI.IDPLANO= PLANOS.IDPLANO
                           WHERE LOGI.LOGESTADO ='REGISTRO SIN FINALIZAR'");
   // $logi = $logistica->fetch(PDO::FETCH_ASSOC);
    //OBTENER LOS DATOS DE LA OP
    $op=$conn->query("SELECT*FROM OP");
    //LLAMR LOS DATOS DELA ABSE4D E DATOS Y ESPECIFICAR QUE SEAN LOS QUE SE SOLICTA
    if($_SERVER["REQUEST_METHOD"] == "POST"){
        if(empty($_POST["op"])||empty($_POST["area"])||empty($_POST["observacion"])||empty($_POST["plano"])){
            
        }else{

        }
    }
}else{
    header("Location:./index.php");
}
?>
<?php require "./partials/header.php"; ?>
<?php require "./partials/dashboard.php"; ?>
<!-- Agrega el script jQuery y el script AJAX aquí -->
<script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
<script>
    $(document).ready(function(){
        var timeout;

        $('#op').on('input', function(){
            var opValue = $(this).val();

            // Cancela la solicitud anterior si aún no se ha completado
            clearTimeout(timeout);

            // Espera 500ms después de que el usuario haya dejado de escribir
            timeout = setTimeout(function(){
                // Realizar la solicitud AJAX
                $.ajax({
                    type: 'POST',
                    url: 'buscar_planos.php',
                    data: { op: opValue },
                    success: function(response){
                        // Actualizar las opciones del select
                        $('#plano').html(response);
                    }
                });
            }, 500);
        });
    });
</script>

<section class="section">
    <div class="row">
        <div class="">
            <?php  if(empty($id)) : ?>
                <div class="card accordion" id="accordionExample">
                    <div class="card-body accordion-item">
                        <h5 class="card-title accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
                            Registro de datos de la Logistica
                             </button>
                         </h5>
                         <?php if ($error) : ?>
                             <p class="text_danger">
                            <?=$error ?>
                           </p>
                          <?php endif ?>
                            <div id="collapseOne" class="accordion-collapse collapse" aria-labelledby="headingOne" dat-bs-parent="#accordionExample">
                            <div class="acordion-body">
                               <form class="row g-3" method="post" action="logistica.php">
                                     <div class="col-md-6">
                                        <div class="form-floating ">
                                            <input type="text" class="form-control" id="op" name="op" placeholder="Buscar por  Op" list="opList" oninput="buscarPorOp()">
                                            <label for="op">Ingrese la Op</label>
                                            <datalist id="opList">
                                                <?php foreach($op as$op) : ?>
                                                    <option value="<?= $op["IDOP"] ?>">
                                                <?php endforeach ?>
                                            </datalist>
                                        </div>
                                     </div>
                                     <div class="col-md-6">
                                        <div class="form-floating">
                                            <input type="text" class="form-control" id="cliente" name="cliente" placeholder="Cliente" readonly>
                                            <label for="cliente">Cliente</label>
                                        </div>
                                     </div>
                                     <div class="col-mb-6">
                                        <div class="form-floating">
                                            <select class="form-select" id="plano" aria-label="Stat e">
                                                <option selected>Selecione el numero de plano</option>
                                            </select>
                                        </div>
                                     </div>
                                    <div class="col-mb-6">
                                        <div class="form-floating ">
                                            <select class="form-select" id="area" aria-label="Stat e">
                                                <option selected>Are de Trabajo</option>
                                                <option value="1">Carpinteria</option>
                                                <option value="2">ACM</option>
                                                <option value="3">Pictura</Picture></option>
                                                <option value="4">Acrilicos</option>
                                                <option value="5">Maquinas</option>
                                                <option value="6">Impresiones</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-floating ">
                                            <input type="text" class="form-control" id="observaciones" name="observaciones" placeholder="observacione">
                                            <label for="observaciones">Registre la Observacion</label>
                                        </div>
                                    </div>
                                    <div class="text-center">
                                       <button type="submit" class="btn btn-primary">Guardar</button>
                                      <button type="reset" class="btn btn-secondary">Reset</button>
                                   </div>
                                </form>
                            </div>
                        </div>
                    </div>    
                </div>
            <?php else : ?>
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Editar rRegistro de Logistica</h5>

                        <?php if ($error) : ?>
                            <p class="text-danger">
                                <?= $error ?>
                            </p>
                        <?php endif ?>
                        <form class="row g-3" method="POST" action="logistica.php">

                        </form>
                    </div>
                </div>
            <?php endif ?>

            <section class="section">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="card">
                            <div class="card-body">
                                <div class="card-header"><h5 class="card-title"> Registro's sin cerrar del dia</h5> </div>
                                <h5 class="col-md-4 mx-auto mb-3"></h5>

                                <?php if (empty($logistica)) : ?>
                                    <div class="col-md-4 mx-auto mb-3">
                                        <div class="card card-body text-center">
                                            <p> No hay un Registro de Logistica</p>
                                        </div>
                                    </div>
                                <?php else : ?>
                                    <!-- Table with stripped rows -->
                                    <table class="table datatable">
                                        <thead>
                                            <tr>
                                                <th>Registro</th>
                                                <th>OP</th>
                                                <th>Are de Trabajo</th>
                                                <th>Hora de Regsitro</th>
                                                <th>Hora de Finalizacion</th>
                                                <th>Observaciones</th>
                                                <th>Persona del Registro</th>
                                                <th>Estado</th>
                                                <th></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach($logistica as $logistica) : ?>
                                                <tr>
                                                    <td><?= $logistica["IDLOGISTICA"] ?></td>
                                                   <td><?= $logistica["IDOP"] ?></td>
                                                   <td><?= $logistica["LOGAREATRABAJO"] ?></td>
                                                   <td><?= $logistica["LOGHORAINCIO"] ?></td>
                                                   <td>
                                                        <?php if($logistica["LOGHORAFINAL"] == "0000-00-00 00:00:00") : ?>
                                                          <a href="./finalizar/cambioLogistica.php?id=<?= $logistica["IDLOGISTICA"] ?>" class="btn btn-primary mb-2">Finalizar</a>
                                                         <?php endif ?>
                                                   </td>
                                                        </td>
                                                    <td><?= $logistica["LOGOBSERVACIONES"] ?></td>
                                                    <td><?= $logistica["CEDULA_NOMBRES"] ."" .$logistica["CEDULA_APELLDIOS"] ?></td>
                                                    <td><?= $logistica["LOGESTADO"] ?></td>
                                                    <td>
                                                    <a href="logistica.php?id=<?=$logistica["IDLOGISTICA"] ?>" class="btn btn-secondary mb-2">Editar</a>
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