<?php
require "../sql/database.php";
require "./partials/kardex.php";

session_start();

// Si la sesión no existe, redirigir al login.php y dejar de ejecutar el resto
if (!isset($_SESSION["user"])) {
    header("Location: ../login-form/login.php");
    return;
}

// Declaramos la variable $registros
$registros = null;

//BUSCAMOS EL DATO DEL USER PARA QUE SE IDENTIFIQUE
$usuario = $_SESSION["user"]["CEDULA"];

// Validamos los perfiles
if ($_SESSION["user"]["ROL"] == 2) {
    // Definir los nombres de los días de la semana
    $dias_semana = ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'];

    // Si el rol es 2 (Diseñador ADMIN), seleccionamos los registros donde el diseñador es el usuario actual, con información adicional de ORDENDISENIO
    $registros = $conn->prepare("SELECT R.*, O.MARCA, P.PERNOMBRES, P.PERAPELLIDOS 
    FROM REGISTROS R 
    JOIN ORDENDISENIO O ON R.PRODUCTO = O.PRODUCTO 
    JOIN PERSONAS P ON R.DISENIADOR = P.CEDULA
    JOIN USUARIOS U ON P.CEDULA = U.CEDULA
    WHERE U.ROL = 3
    ORDER BY R.ID DESC");
    $registros->execute();

    // Obtenemos los nombres de todos los usuarios con el rol 3
    $usuarios_rol_3 = $conn->prepare("SELECT P.PERNOMBRES, P.PERAPELLIDOS
        FROM PERSONAS P 
        JOIN USUARIOS U ON P.CEDULA = U.CEDULA
        WHERE U.ROL = 3");
    $usuarios_rol_3->execute();

    // Creamos un array para almacenar los nombres de los usuarios con rol 3
    $nombres_usuarios_rol_3 = [];
    while ($row = $usuarios_rol_3->fetch(PDO::FETCH_ASSOC)) {
        $nombres_usuarios_rol_3[] = $row["PERNOMBRES"] . " " . $row["PERAPELLIDOS"];
    }


    // Consulta SQL para obtener las horas trabajadas por día
    $sql = "SELECT 
                R.DISENIADOR,
                DAYOFWEEK(R.HORA_INICIO) AS dia_semana,
                SUM(TIME_TO_SEC(TIMEDIFF(R.HORA_FINAL, R.HORA_INICIO))) AS total_segundos
            FROM 
                REGISTROS R
                JOIN PERSONAS P ON R.DISENIADOR = P.CEDULA
                JOIN USUARIOS U ON P.CEDULA = U.CEDULA
            WHERE 
                U.ROL = 3
            GROUP BY 
                R.DISENIADOR, dia_semana;
            ";

    $consulta_horas_trabajadas = $conn->prepare($sql);
    $consulta_horas_trabajadas->execute();

    // Inicializar array multidimensional para almacenar las horas trabajadas por día
    $horas_trabajadas_por_dia = array(
        1 => array(),
        2 => array(),
        3 => array(),
        4 => array(),
        5 => array(),
        6 => array(),
        7 => array()
    );

    while ($row = $consulta_horas_trabajadas->fetch(PDO::FETCH_ASSOC)) {
        $dia_semana = $row['dia_semana'];
        $total_segundos = $row['total_segundos'];

        // Agregar los segundos trabajados al array del día correspondiente
        $horas_trabajadas_por_dia[$dia_semana][] = $total_segundos;
    }

    // Verificar los resultados
    // var_dump($horas_trabajadas_por_dia);
    // die();

} elseif ($_SESSION["user"]["ROL"] == 3) {
    // Si el rol es 3 (Diseñador), seleccionamos los registros donde el diseñador es el usuario actual, con información adicional de ORDENDISENIO
    $registros = $conn->prepare("SELECT R.*, O.MARCA, P.PERNOMBRES, P.PERAPELLIDOS 
                                    FROM REGISTROS R 
                                    JOIN ORDENDISENIO O ON R.PRODUCTO = O.PRODUCTO 
                                    JOIN PERSONAS P ON R.DISENIADOR = P.CEDULA
                                    WHERE R.DISENIADOR = :usuario
                                    ORDER BY ID DESC");
    $registros->bindParam(":usuario", $usuario);
    $registros->execute();
} else {
    // Si el rol no es ninguno de los anteriores, redirigimos al usuario a la página de inicio
    header("Location:./index.php");
    return;
}

?>


<?php require "./partials/header.php"; ?>
<?php require "./partials/dashboard.php"; ?>

<section class="section">
    <div class="row">
        <div class="">
            <?php if (($_SESSION["user"]["ROL"]) && ($_SESSION["user"]["ROL"] == 3)) : ?>
                <section class="section">
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="card">
                                <div class="card-body">
                                    <div class="card-header">
                                        <h5 class="card-tittle">MIS REGISTROS</h5>
                                    </div>
                                    <h5 class="col-md-4 mx-auto mb-3"></h5>

                                    <?php if ($registros->rowCount() == 0) : ?>
                                        <div class="col-md-4 mx-auto mb-3">
                                            <div class="card card-body text-center">
                                                <p>No hay registros aún</p>
                                            </div>
                                        </div>
                                    <?php else : ?>
                                        <!-- Table with stripped rows -->
                                        <table class="table datatable">
                                            <thead>
                                                <tr>
                                                    <th>#</th>
                                                    <th>PRODUCTO</th>
                                                    <th>MARCA</th>
                                                    <th>HORA INICIO</th>
                                                    <th>HORA FINAL</th>
                                                    <th>OBSERVACIONES</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php $contador = $registros->rowCount(); ?>
                                                <?php foreach ($registros as $registros) : ?>
                                                    <tr>
                                                        <td><?= $contador-- ?></td>
                                                        <th><?= $registros["PRODUCTO"] ?></th>
                                                        <th><?= $registros["MARCA"] ?></th>
                                                        <td><?= $registros["HORA_INICIO"] ?></td>
                                                        <td><?= $registros["HORA_FINAL"] ?></td>
                                                        <td><?= $registros["OBSERVACIONES"] ?></td>
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
            <?php elseif (($_SESSION["user"]["ROL"]) && ($_SESSION["user"]["ROL"] == 2)) : ?>
                <section class="section">
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="card">
                                <div class="card-body">
                                    <div class="card-header">
                                        <h5 class="card-tittle">REGISTROS</h5>
                                        <button type="button" class="btn btn-success btn-xs" data-bs-toggle="modal" data-bs-target="#reporte">
                                            <img src="../exel/exel_icon.png" alt="Icono Excel" class="me-1" style="width: 25px; height: 25px;">
                                            Exportar a Excel
                                        </button>
                                        <div class="modal fade" id="reporte" tabindex="-1" style="display: none;" aria-hidden="true">
                                            <div class="modal-dialog modal-dialog-centered">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title">REPORTE DE LAS ORDENES DE DISEÑO</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <p>ESTA SEGURA DE GENERAR EL REPORTE DE LAS ORDENES DE DISEÑO SI ES ASI POR FAVOR SELECCIONE EL AÑO Y EL MES DEL REPORTE QUE SE VA A GENERAR Y SELECCIONE GENERAR EL REPORTE</p>
                                                        <form action="./reporte_exel/exel_disenio.php" method="post"> <!-- Modificado: Formulario que envía los datos mediante POST -->
                                                            <div class="form-group">
                                                                <label for="selectYear">Año:</label>
                                                                <select class="form-control" id="selectYear" name="selectYear"> <!-- Agregado: name="selectYear" para identificar el campo en PHP -->
                                                                    <option value="2024">2024</option>
                                                                    <option value="2025">2025</option>
                                                                    <option value="2026">2026</option>
                                                                    <option value="2027">2027</option>
                                                                    <!-- Agrega más opciones según sea necesario -->
                                                                </select>
                                                            </div>
                                                            <div class="form-group">
                                                                <label for="selectMonth">Mes:</label>
                                                                <select class="form-control" id="selectMonth" name="selectMonth"> <!-- Agregado: name="selectMonth" para identificar el campo en PHP -->
                                                                    <option value="1">Enero</option>
                                                                    <option value="2">Febrero</option>
                                                                    <option value="3">Marzo</option>
                                                                    <option value="4">Abril</option>
                                                                    <option value="5">Mayo</option>
                                                                    <option value="6">Junio</option>
                                                                    <option value="7">Julio</option>
                                                                    <option value="8">Agosto</option>
                                                                    <option value="9">Septiembre</option>
                                                                    <option value="10">Octubre</option>
                                                                    <option value="11">Noviembre</option>
                                                                    <option value="12">Diciembre</option>
                                                                    <!-- Agrega más opciones según sea necesario -->
                                                                </select>
                                                            </div>
                                                            <div class="modal-footer">
                                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                                                                <button type="submit" class="btn btn-success">Generar Reporte</button> <!-- Modificado: Botón submit para enviar el formulario -->
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <h5 class="col-md-4 mx-auto mb-3"></h5>

                                    <?php if ($registros->rowCount() == 0) : ?>
                                        <div class="col-md-4 mx-auto mb-3">
                                            <div class="card card-body text-center">
                                                <p>No hay registros aún</p>
                                            </div>
                                        </div>
                                    <?php else : ?>
                                        <!-- Table with stripped rows -->
                                        <table class="table datatable">
                                            <thead>
                                                <tr>
                                                    <th>#</th>
                                                    <th>DISEÑADOR</th>
                                                    <th>PRODUCTO</th>
                                                    <th>MARCA</th>
                                                    <th>HORA INICIO</th>
                                                    <th>HORA FINAL</th>
                                                    <th>OBSERVACIONES</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($registros as $registros) : ?>

                                                    <tr>
                                                        <th><?= $registros["ID"] ?></th>
                                                        <th><?= $registros["PERNOMBRES"] . " " . $registros["PERAPELLIDOS"] ?></th>
                                                        <th><?= $registros["PRODUCTO"] ?></th>
                                                        <th><?= $registros["MARCA"] ?></th>
                                                        <td><?= $registros["HORA_INICIO"] ?></td>
                                                        <td><?= $registros["HORA_FINAL"] ?></td>
                                                        <td><?= $registros["OBSERVACIONES"] ?></td>
                                                    </tr>
                                                <?php endforeach ?>
                                            </tbody>
                                        </table>
                                    <?php endif ?>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-12">
                            <div class="card">
                                <div class="card-body">
                                    <h5 class="card-title">SEMANA</h5>

                                    <!-- Column Chart -->
                                    <div id="columnChart"></div>

                                    <script>
                                        document.addEventListener("DOMContentLoaded", () => {
                                            new ApexCharts(document.querySelector("#columnChart"), {
                                                series: [
                                                    <?php foreach ($horas_trabajadas_por_dia as $dia => $segundos) : ?> {
                                                            name: '<?php echo $dias_semana[$dia - 1]; ?>',
                                                            data: [
                                                                <?php foreach ($segundos as $hora) : ?>
                                                                    <?php echo ($hora / 3600); ?>, // Convertir segundos a horas
                                                                <?php endforeach ?>
                                                            ]
                                                        },
                                                    <?php endforeach ?>
                                                ],
                                                chart: {
                                                    type: 'bar',
                                                    height: 350
                                                },
                                                plotOptions: {
                                                    bar: {
                                                        horizontal: false,
                                                        columnWidth: '55%',
                                                        endingShape: 'rounded'
                                                    },
                                                },
                                                dataLabels: {
                                                    enabled: false
                                                },
                                                stroke: {
                                                    show: true,
                                                    width: 2,
                                                    colors: ['transparent']
                                                },
                                                xaxis: {
                                                    categories: <?php echo json_encode($nombres_usuarios_rol_3); ?>
                                                },
                                                yaxis: {
                                                    title: {
                                                        text: 'HORAS AL DIA'
                                                    }
                                                },
                                                fill: {
                                                    opacity: 1
                                                },
                                                tooltip: {
                                                    y: {
                                                        formatter: function(val) {
                                                            return val + " horas"
                                                        }
                                                    }
                                                }
                                            }).render();
                                        });
                                    </script>
                                    <!-- End Column Chart -->

                                </div>
                            </div>
                        </div>
                    </div>
                </section>
            <?php endif ?>
        </div>
    </div>
</section>

<?php require "./partials/footer.php"; ?>