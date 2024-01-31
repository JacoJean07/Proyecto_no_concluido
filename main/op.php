<?php
require  "../sql/database.php";
session_start();
//si la sesion no existe, mandar al login.php y dejar de ejecutar el resto; se puede hacer un required para ahorra codigo
if (!isset($_SESSION["user"])) {
    header("Location: ../login-form/login.php");
    return;
}
//declaramos la variable error que nos ayudara a mostrar errores, etc.
$error = null;
//$state = 1;
$id = isset($_GET["id"]) ? $_GET["id"] : null;
$opEditar=null;
if (($_SESSION["user"]["ROL"]) && ($_SESSION["user"]["ROL"] == 1)) {
    //llamr los contactos de la base de datos y especificar que sean los que tengan la op_id de la funcion seccion_start
    $op = $conn->query("SELECT*FROM OP");
    // Obtener opciones para IDAREA desde la base de datos
    $lugarproduccion = $conn->query("SELECT IDLUGAR, CIUDAD FROM LUGARPRODUCCION");
    
    $personas=$conn->query("SELECT*FROM PERSONAS");
    //VERFIFICAMOS EL METODOD QUE SE USA EL FORM CON UN IF 
    if ($_SERVER["RQUEST_METHOD"] == "POST") {
        //VALIDFAMOS QUE NO SE MANDEN DATOS VASIOS
        if (empty($_POST["cliente"])||empty($_POST["ciudad"])||empty($_POST["notificacion"])|| empty($_POST["vendedor"])||empty($_POST["direccion"])||empty($_POST["contacto"])||empty($_POST["telefono"])||empty($_POST["observaciones"])||empty($_POST["estado"]) ) {
            $error="POR FAVOR LLENAR TODOS LOS CAMPOS";
        } else {
            //VERIFICAMOS SI YA EXISTE UN REGISTRO PARA  OP ACTUAL
            $existingStament=$conn->prepare("SELECT IDOP FROM OP  WHERE CEDULA=cedula");
            $existingStament->execute([":cedula"=> $_POST['cedula']]);
            $existingDiseniador=$existingStament->fetch(PDO::FETCH_ASSOC);

            if($existingDiseniador){
                //SI EXITE, SE ACTUALIZA LA OP
                $stament =$conn->prepare("UPDATE OP SET
                OPCIUDAD=:ciudad,
                OPDETALLE=:detalle,
                OPNOTIFICACIONCORREO=:notificacion,
                OPVENDEDOR=:vendedor,
                OPDIRECCIONLOCAL=:dirrecion,
                OPPERSONACONTACTO=:contacto,
                TELEFONO=:telefono,
                OPOBSERVACIONES=:observaciones,
                OPESTADO=:estado");
                $stament->execute([
                    ":ciudad"=>$_POST["ciudad"],
                    ":detalle"=>$_POST["detalle"],
                    "notificacion"=>$_POST["notificacion"],
                    ":vendedor"=>$_POST["vendedor"],
                    ":dirrecion"=>$_POST["direccion"],
                    ":contacto"=>$_POST["contacto"],
                    ":telefono"=>$_POST["telefono"],
                    ":observaciones"=>$_POST["observaciones"],
                    ":estado"=>$_POST["estado"]
                ]);

            }else{
                //SINO AY UN REGISTRO ACTUALIZARME
                $stament=$conn->prepare("INSERT INTO OP (CEDULA,IDLUGAR,OPCIUDAD,OPDETALLE,OPREGISTRO,OPNOTIFICACIONCORREO,	OPVENDEDOR,OPDIRECCIONLOCAL,OPPERESONACONTACTO,TELEFONO,OPOBSERAVACIONES,OPESTADO)
                VALUES('1750541730',:idlugar,:ciudad,:detalle,CURRENT_TIMESTAMP,:notificacion,:vendedor,:direccion,:contacto,:observaciones,:estado

                )");
                $stament->execute([
                    ":idlugar"=>$_POST["idLugar"],
                    
                    ":ciudad"=>$_POST["ciudad"],
                    ":detalle"=>$_POST["detalle"],
                    "notificacion"=>$_POST["notificacion"],
                    ":vendedor"=>$_POST["vendedor"],
                    ":dirrecion"=>$_POST["direccion"],
                    ":contacto"=>$_POST["contacto"],
                    ":telefono"=>$_POST["telefono"],
                    ":observaciones"=>$_POST["observaciones"],
                    ":estado"=>$_POST["estado"]
                ]);

            }
            //REDIRIGIREMOS AHOME.PHP
            header("Location: op.php");
            return;
        
        }
    }
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
            <?php if(empty($id)):?>
            <div class="card">
                <div class="card-body">
                        <h5 class="card_title">Nuevo Registro de la OP</h5>
                    <!--si hay un error mandar un danger -->
                    <?php if ($error) : ?>
                        <p class="text_danger">
                            <?= $error ?>
                        </p>
                    <?php endif ?>
                    <form class="row g-3" method="POST" action="op.php">
                        <div class="col-md-6">
                            <div class="form-floating mb-3">
                                <input type="text"class="form-control" id="vendedor" name="vendedor" placeholder="Buscar por nombre" list="nombresList" oninput="buscarPorNombres()">
                                <lavel for="venderor">Vendedor</lavel>
                                <datalist id="nombresList">
                                    <?php foreach($personas as $persona):?>
                                        <option value="<?=$persona["PERNOMBRES"]?>">
                                        <?php endforeach?>
                                </datalist>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-floating">
                                <input type="text" class="form-control"id="cedula" placeholder="Vendeddor"readonly>
                                <label for="cedula"> Cedula</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                                <label for="idLugar" class="form-label">ID Lugar de Produccion</label>
                                <select class="form-select" id="idarea" name="idlugarproduccion">
                                    <?php foreach ($lugarproduccion as $lugarproduccion): ?>
                                        <option value="<?= $lugarproduccion["IDLUGAR"] ?>"><?= $lugarproduccion["CIUDAD"] ?></option>
                                    <?php endforeach ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                            <div class="form-floating">
                                <input type="text"class="form-control" id="cliente" name="cliente" placeholder="Cliente" >
                                <lavel for="cliente">Cliete</lavel>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-floating">
                                <input type="text"class="form-control" id="ciudad" name="ciudad" placeholder="Ciudad">
                                <lavel for="ciudad">Ciudad</lavel>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-floating datetimepicker">
                                <input type="text"class="form-control" id="detalle" name="detalle" placeholder="Detalle" >
                                <lavel for="detalle">Detalles </lavel>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="form-floating datetimepicker">
                                <input type="date"class="form-control" id="notificacion" name="notificacion" placeholder="Notificacion" >
                                <lavel for="notificacion">Notificacion Correo</lavel>
                            </div>
                        </div>
                       
                        <div class="col-md-6">
                            <div class="form-floating">
                                <input type="text"class="form-control" id="direccion" name="direccion" placeholder="Direccion" >
                                <lavel for="direccion">Direccion del Local</lavel>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-floating">
                                <input type="text"class="form-control" id="contacto" name="contacto" placeholder="Contacto" >
                                <lavel for="contacto">Persona de Contacto</lavel>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-floating">
                                <input type="text"class="form-control" id="telefono" name="telefono" placeholder="Telefono" >
                                <lavel for="telefono">Telefono</lavel>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-floating">
                                <input type="text"class="form-control" id="observaciones" name="obseravciones" placeholder="Observaciones">
                                <lavel for="observaciones">Observaciones</lavel>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-floating">
                                <input type="text"class="form-control" id="estado" name="estado" placeholder="Estado" >
                                <lavel for="estado">Estado</lavel>
                            </div>
                        </div>
                        <div class="text-center">
                            <button type="submit" class="btn btn-primary"> "Guardar" ?></button>
                            <button type="reset" class="btn btn-secondary">Reset</button>
                        </div>
                    </form>
                </div>
            </div>
            <?php else:?>
                <?php 
                    $stament=$conn->prepare("SELECT O.*,P.*FROM OP O INNER JOIN PERSONAS P ON O.CEDULA=P.CEDULA WHERE O.IDOP =:id");
                    $stament->bindParam(":id", $id);
                    $stament->execute();
                    $opEditor=$statement->fetch(PDO::FETCH_ASSOC);
                    ?>
                    <div class="card">
                        <div class="card-body">
                           <h5 class="card-title">Editar OP</h5>
                            <!-- si hay un error mandar un danger -->
                            <?php if ($error): ?> 
                             <p class="text-danger">
                                <?= $error ?>
                             </p>
                            <?php endif?>
                            <form class="row g-3" method="POST" action="op.php">
                                <?php 
                                $nombrestrabajador=isset($opEditar['CEDULA'])? $opEditar['CEDULA']:'';
                                ?>
                            <div class="col-md-6">
                            <div class="form-floating mb-3">
                                <input value="<?=$opEditar['PERNOMBRES']?>" type="text"class="form-control" id="vendedor" name="vendedor" placeholder="Buscar por nombre" list="nombresList" oninput="buscarPorNombres()">
                                <lavel for="venderor">Vendedor</lavel>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-floating">
                                <input value="<?=$nombrestrabajador?>" type="text" class="form-control"id="cedula" placeholder="Vendeddor"readonly>
                                <label for="cedula"> Cedula</label>
                            </div>
                        </div>
                        <div  class="col-md-6">
                                <label for="idarea" class="form-label">ID Lugar de Produccion</label>
                                <select class="form-select" id="idarea" name="idlugarproduccion">
                                    <?php foreach ($lugarproduccion as $lugarproduccion): ?>
                                        <option value="<?= $lugarproduccion["IDLUGAR"] ?>"><?= $lugarproduccion["CIUDAD"] ?></option>
                                    <?php endforeach ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                            <div class="form-floating">
                                <input value="<?=$opEditar["OPCLIENTE"]?>" type="text"class="form-control" id="cliente" name="cliente" placeholder="Cliente" >
                                <lavel for="cliente">Cliete</lavel>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-floating">
                                <input value="<?=$opEditar["OPCIUDAD"]?>" type="text"class="form-control" id="ciudad" name="ciudad" placeholder="Ciudad">
                                <lavel for="ciudad">Ciudad</lavel>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-floating datetimepicker">
                                <input value="<?=$opEditar["OPDETALLE"]?>"type="text"class="form-control" id="detalle" name="detalle" placeholder="Detalle" >
                                <lavel for="detalle">Detalles </lavel>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-floating datetimepicker">
                                <input value="<?=$opEditar["OPNOTIFICACIONCORREO"]?>" type="date"class="form-control" id="notificacion" name="notificacion" placeholder="Notificacion" >
                                <lavel for="notificacion">Notificacion Correo</lavel>
                            </div>
                        </div>
                       
                        <div class="col-md-6">
                            <div class="form-floating">
                                <input value="<?=$opEditar["OPDIRECCIONLOCAL"]?>"type="text"class="form-control" id="direccion" name="direccion" placeholder="Direccion" >
                                <lavel for="direccion">Direccion del Local</lavel>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-floating">
                                <input value="<?=$opEditar["OPPERSONACONTACTO"]?>"type="text"class="form-control" id="contacto" name="contacto" placeholder="Contacto" >
                                <lavel for="contacto">Persona de Contacto</lavel>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-floating">
                                <input value="<?=$opEditar["TELEFONO"]?>"type="text"class="form-control" id="telefono" name="telefono" placeholder="Telefono" >
                                <lavel for="telefono">Telefono</lavel>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-floating">
                                <input value="<?=$opEditar["OPOBSERVACIONES"]?>"type="text"class="form-control" id="observaciones" name="obseravciones" placeholder="Observaciones">
                                <lavel for="observaciones">Observaciones</lavel>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-floating">
                                <input value="<?=$opEditar["OPESTADO"]?>"type="text"class="form-control" id="estado" name="estado" placeholder="Estado" >
                                <lavel for="estado">Estado</lavel>
                            </div>
                        </div>
                        <div class="text-center">
                            <button type="submit" class="btn btn-primary"> "Actualizar" ?></button>
                            <button type="reset" class="btn btn-secondary">Reset</button>
                        </div>
                        </form>
                        </div>
                    </div>
                    <?php endif?>
            <section class="section">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="col-md-4 mx-auto mb-3"></h5>
                                 <!-- si el array asociativo $teachers no tiene nada dentro, entonces imprimir el siguiente div -->
                                 <?php if($op->rowCount()==0):?>
                                    <div class="col-md4 mx-auto mb-3">
                                        <div class="card card-body text-center">
                                            <p>No hay Op Registradas</p>
                                        </div>

                                    </div>
                                <?php else: ?>
                                <!-- Table with stripped rows -->
                                <table class="table datatable">
                                    <thead>
                                        <tr>
                                            <th>OP</th>
                                            <th>Diseñador</th>
                                            <th>Lugar de Produccion</th>
                                            <th>Cliente</th>  
                                            <th>Detalle</th> 
                                            <th>Registro</th> 
                                            <th>Notificacion del Correo</th> 
                                            <th>Vendedor</th> 
                                            <th>Diseñador</th> 
                                            <th>Direccion del Local</th> 
                                            <th>Persona de Contacto</th> 
                                            <th>Telefono</th> 
                                            <th>Observaciones</th> 
                                            <th>Estado</th> 
                                            <th></th> 
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach($op as$op):?>
                                            <tr>
                                            <th><?=$op["IDOP"]?></th> 
                                            <th><?=$op["CEDULA"]?></th> 
                                            <th><?=$op["IDLUGAR"]?></th> 
                                            <td><?=$op["OPCLIENTE"]?></td> 
                                            <td><?=$op["OPDETALLE"]?></td>
                                            <td><?=$op["OPREGISTRO"]?></td>
                                            <td><?=$op["OPNOTIFICACIONCORREO"]?></td>
                                            <td><?=$op["OPVENDEDOR"]?></td>
                                            <td><?=$op["OPDISENIADOR"]?></td>
                                            <td><?=$op["OPDIRECCIONLOCAL"]?></td>
                                            <td><?=$op["OPPERESONACONTACTO"]?></td>
                                            <td><?=$op["TELEFONO"]?></td>
                                            <td><?=$op["OPOBSERAVACIONES"]?></td>
                                            <td><?=$op["OPESTADO"]?></td>
                                            <td>
                                                <a href="op.php?id=<?=$op["IDOP"]?>" class="btn btn-secondary mb-2">Editar</a>
                                            </td>
                                            <td>
                                                <
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