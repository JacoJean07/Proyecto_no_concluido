
<?php 

require "../sql/database.php";

session_start();
//si la sesion no existe, mandar al login.php y dejar de ejecutar el resto; se puede hacer un required para ahorra codigo
if (!isset($_SESSION["user"])) {
  header("Location: ../login-form/login.php");
  return;
}

$totalFilas = $conn->query("SELECT COUNT(*) AS total_filas FROM PERSONAS WHERE PERESTADO = 1")->fetchColumn();
$kardex = $conn->query("SELECT * FROM KARDEX ORDER BY IDKARDEX DESC LIMIT 10");
$op = $conn->query("SELECT OP.*, 
                          CEDULA.PERNOMBRES AS CEDULA_NOMBRES, CEDULA.PERAPELLIDOS AS CEDULA_APELLIDOS,
                          VENDEDOR.PERNOMBRES AS VENDEDOR_NOMBRES, VENDEDOR.PERAPELLIDOS AS VENDEDOR_APELLIDOS,
                          COUNT(PLANOS.IDPLANO) AS NUMERO_PLANOS
                   FROM OP
                   LEFT JOIN PERSONAS AS CEDULA ON OP.CEDULA = CEDULA.CEDULA
                   LEFT JOIN PERSONAS AS VENDEDOR ON OP.OPVENDEDOR = VENDEDOR.CEDULA
                   LEFT JOIN PLANOS ON OP.IDOP = PLANOS.IDOP
                   GROUP BY OP.IDOP ORDER BY IDOP DESC");

date_default_timezone_set('America/Lima'); 



?>

<?php require "./partials/header.php"; ?>
<?php require "./partials/dashboard.php"; ?>




    <div class="pagetitle">
      <h1>Dashboard</h1>
      <nav>
        <ol class="breadcrumb">
          <li class="breadcrumb-item"><a href="index.php">Home</a></li>
          <li class="breadcrumb-item active">Dashboard</li>
        </ol>
      </nav>
    </div><!-- End Page Title -->

    <section class="section dashboard">
      <div class="row">

        <!-- Left side columns -->
        <div class="col-lg-8">
          <div class="row">

            
            <!-- Customers Card -->
            <div class="col-xxl-4 col-xl-12">

              <div class="card info-card customers-card">

                

                <div class="card-body">
                  <h5 class="card-title">Trabajadores <span>|</span></h5>

                  <div class="d-flex align-items-center">
                    <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                      <i class="bi bi-people"></i>
                    </div>
                    <div class="ps-3">
                      <h6><?= $totalFilas ?></h6>
                      <span class="text-danger small pt-1 fw-bold"></span> <span class="text-muted small pt-2 ps-1">Personas</span>

                    </div>
                  </div>

                </div>
              </div>

            </div><!-- End Customers Card -->

            

            <!-- Recent Sales -->
            <div class="col-12">
              <div class="card recent-sales overflow-auto">

                <div class="filter">
                  <a class="icon" href="#" data-bs-toggle="dropdown"><i class="bi bi-three-dots"></i></a>
                  <ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow">
                    <li class="dropdown-header text-start">
                      <h6>Filter</h6>
                    </li>

                    <li><a class="dropdown-item" href="#">Today</a></li>
                    <li><a class="dropdown-item" href="#">This Month</a></li>
                    <li><a class="dropdown-item" href="#">This Year</a></li>
                  </ul>
                </div>

                <div class="card-body">
                  <h5 class="card-title">Ordenes de produccion recientes <span>| </span></h5>

                  <table class="table table-borderless datatable">
                    <thead>
                      <tr>
                        <th scope="col">ID OP</th>
                        <th scope="col">Cliente</th>
                        <th scope="col">Descripción</th>
                        <th scope="col">Planos</th>
                        <th scope="col">Estado</th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php foreach($op as $op) : ?>
                        <tr>
                          <th scope="row"><a href="#"><?= $op["IDOP"] ?> </a></th>
                          <td><?= $op["OPCLIENTE"] ?></td>
                          <td><a href="#" class="text-primary"><?= $op["OPDETALLE"] ?></a></td>
                          <td><?= $op["NUMERO_PLANOS"] ?></td>
                          <td><span class="badge 
                            <?php if ($op["OPESTADO"] == 'OP CREADA') : ?>
                              bg-secondary
                            <?php elseif ($op["OPESTADO"] == 'EN PRODUCCION') : ?>
                              bg-primary
                            <?php elseif ($op["OPESTADO"] == 'FINALIZADA') : ?>
                              bg-success
                            <?php endif ?>
                          "><?= $op["OPESTADO"] ?></span></td>
                        </tr>
                      <?php endforeach ?>
                    </tbody>
                  </table>

                </div>

              </div>
            </div><!-- End Recent Sales -->

            

          </div>
        </div><!-- End Left side columns -->

        <!-- Right side columns -->
        <div class="col-lg-4">

          <!-- Recent Activity -->
          <div class="card">
            <div class="filter">
              <a class="icon" href="#" data-bs-toggle="dropdown"><i class="bi bi-three-dots"></i></a>
              <ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow">
                <li class="dropdown-header text-start">
                  <h6>Filter</h6>
                </li>

                <li><a class="dropdown-item" href="#">Today</a></li>
                <li><a class="dropdown-item" href="#">This Month</a></li>
                <li><a class="dropdown-item" href="#">This Year</a></li>
              </ul>
            </div>

            <div class="card-body">
              <h5 class="card-title">Actividad Reciente <span>| Today</span></h5>

              <div class="activity">
                
                <?php foreach($kardex as $kar) : ?>
                  <?php
                  // PARA CALCULAR EL TIEMPO DE CADA ACCION
                  $fechaMovimiento = new DateTime($kar["KARFECHA"]);
                  $fechaActual = new DateTime();

                  // Calcula la diferencia entre las dos fechas
                  $diferencia = $fechaActual->diff($fechaMovimiento);

                  // Accede a los componentes de la diferencia
                  $horas = $diferencia->h;
                  $minutos = $diferencia->i;

                  // Formatea el resultado
                  $tiempoTranscurrido = '';
                  if ($horas > 0) {
                      $tiempoTranscurrido .= $horas . ' h ';
                  }
                  $tiempoTranscurrido .= $minutos . ' min';
                  ?>
                <div class="activity-item d-flex">
                  <div class="activite-label"><?= $tiempoTranscurrido ?></div>
                  <i class='bi bi-circle-fill activity-badge align-self-start 
                  <?php if ($kar["KARACCION"] == "ELIMINO") :?>
                    text-danger
                  <?php elseif ($kar["KARACCION"] == "CREO") : ?>
                    text-success
                  <?php elseif ($kar["KARACCION"] == "EDITO") : ?>
                    text-warning
                  <?php elseif ($kar["KARACCION"] == "RESTAURO") : ?>
                    text-primary
                  <?php else : ?>
                    text-muted
                  <?php endif ?>
                    '></i>
                  <div class="activity-content">
                    <?= $kar["KARUSER"]?> <b><?= $kar["KARACCION"]?></b> un registro en la tabla <b><?= $kar["KARTABLA"]?></b><br>
                    Dato : <?= $kar["KARROW"]?><br>
                    Fecha: <?= $kar["KARFECHA"]?>
                  </div>
                </div><!-- End activity item-->

                <?php endforeach ?>

              </div>

            </div>
          </div><!-- End Recent Activity -->

            </div>
          </div><!-- End News & Updates -->

        </div><!-- End Right side columns -->

      </div>
    </section>



  <?php require "./partials/footer.php"; ?>
