<?php 

require "../sql/database.php";

session_start();
//si la sesion no existe, mandar al login.php y dejar de ejecutar el resto; se puede hacer un required para ahorra codigo
if (!isset($_SESSION["user"])) {
  header("Location: ../login-form/login.php");
  return;
}

//declaramos la variable error que nos ayudara a mostrar errores, etc.
$error = null;
$state = 1;

if (($_SESSION["user"]["ROL"]) && ($_SESSION["user"]["ROL"] == 1)) {
    //llamar los contactos de la base de datos y especificar que sean los que tengan el persona_id de la funcion sesion_start
    $personas = $conn->query("SELECT * FROM personas");
    //verificamos el metodo que usa el form con un if
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        //validamos que no se manden datos vacios
        if (empty($_POST["cedula"]) || empty($_POST["nombres"]) || empty($_POST["apellidos"]) || empty($_POST["nacimiento"]) || empty($_POST["areatrabajo"])) {
            $error = "POR FAVOR RELLENA TODOS LOS CAMPOS";
        } else {
            //sdeclaramos variables y las asignamos a los valores recibidos del input
            $cedula = $_POST["cedula"];
            $nombres = $_POST["nombres"];
            $apellidos = $_POST["apellidos"];
            $nacimiento = $_POST["nacimiento"];
            $estado = $state;
            $areatrabajo = $_POST["areatrabajo"];
            
            //preparamos una sentencia SQL
            $statement = $conn->prepare("INSERT INTO PERSONAS ( CEDULA, PERNOMBRES, PERAPELLIDOS, PERFECHANACIMIENTO, PERESTADO, PERAREATRABAJO) VALUES (:cedula, :nombres, :apellidos, :nacimiento, :estado, :areatrabajo)");
            //sanitizamos los datos para evitar inyecciones SQL
            $statement->bindParam(":cedula", $_POST["cedula"]);
            $statement->bindParam(":nombres", $_POST["nombres"]);
            $statement->bindParam(":apellidos", $_POST["apellidos"]);
            $statement->bindParam(":nacimiento", $_POST["nacimiento"]);
            $statement->bindParam(":areatrabajo", $_POST["areatrabajo"]);
            $statement->bindParam(":estado", $state);
            //ejecutamos
            $statement->execute();
            //redirigimos a el home.php
            header("Location: personas.php");
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
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Nuevo Trabajador</h5>

                    <form class="row g-3" method="POST" action="personas.php">
                    <div class="col-md-6">
                        <div class="form-floating">
                        <input type="text" class="form-control" id="cedula" name="cedula" placeholder="Cedula">
                        <label for="cedula">Cedula</label>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-floating">
                        <input type="text" class="form-control" id="nombres" name="nombres" placeholder="Nombres">
                        <label for="nombres">Nombres</label>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-floating">
                        <input type="text" class="form-control" id="apellidos" name="apellidos" placeholder="Apellidos">
                        <label for="apellidos">Apellidos</label>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="form-floating">
                        <input type="date" class="form-control" placeholder="Nacimiento" id="nacimiento" name="nacimiento"></input>
                        <label for="nacimiento">Fecha de Nacimiento</label>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-floating mb-3">
                        <select class="form-select" id="areatrabajo" aria-label="State" name="areatrabajo">
                            <option value="Carpinteria">Carpinteria</option>
                            <option value="ACM">ACM</option>
                            <option value="Pintura">Pintura</option>
                            <option value="Acrilicos y Acabados">Acrilicos y Acabados</option>
                            <option value="Maquinas">Maquinas</option>
                            <option value="Impresiones">Impresiones</option>
                            <option value="Diseno Grafico">Diseno Grafico</option>
                        </select>
                        <label for="areatrabajo">Area de Trabajo</label>
                        </div>
                    </div>
                    <div class="text-center">
                        <button type="submit" class="btn btn-primary">Submit</button>
                        <button type="reset" class="btn btn-secondary">Reset</button>
                    </div>
                    </form>

                </div>
            </div>

            <section class="section">
                <div class="row">
                    <div class="col-lg-12">

                    <div class="card">
                        <div class="card-body">
                        <h5 class="card-title">Trabajadores</h5>
                        <!-- si el array asociativo $teachers no tiene nada dentro, entonces imprimir el siguiente div -->
                        <?php if ($personas->rowCount() == 0): ?>
                            <div class= "col-md-4 mx-auto mb-3">
                                <div class= "card card-body text-center">
                                    <p>No hay Trabajadores Aun.</p>
                                </div>
                            </div>
                        <?php else: ?>
                        <!-- Table with stripped rows -->
                        <table class="table datatable">
                            <thead>
                            <tr>
                                <th>Apellidos</th>
                                <th>Nombres</th>
                                <th>Cedula</th>
                                <th>Edad</th>
                                <th>Area de Trabajo</th>
                                <th></th>
                                <th></th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php foreach ($personas as $persona): ?>
                                <tr>
                                <th><?= $persona["PERAPELLIDOS"]?></th>
                                <td><?= $persona["PERNOMBRES"]?></td>
                                <td><?= $persona["CEDULA"]?></td>
                                <td>
                                    <?php
                                    // Calcular la edad a partir de la fecha de nacimiento
                                    $birthdate = new DateTime($persona["PERFECHANACIMIENTO"]);
                                    $today = new DateTime();
                                    $age = $today->diff($birthdate)->y;
                                    echo $age;
                                    ?>
                                </td>
                                <td><?= $persona["PERAREATRABAJO"]?></td>
                                <td>
                                    <a href="#" class="btn btn-secondary mb-2">Editar</a>
                                </td>
                                <td>
                                    <a href="#" class="btn btn-danger mb-2">Eliminar</a>
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
