<!-- ======= Sidebar ======= -->
<aside id="sidebar" class="sidebar">

<!-- mostrar el siguiente nav para las secciones existentes-->
<?php if($_SESSION["user"]["ROL"] == 1 || $_SESSION["user"]["ROL"] == 2 || $_SESSION["user"]["ROL"] == 3) : ?>
<ul class="sidebar-nav" id="sidebar-nav">

  <li class="nav-item">
    <a class="nav-link " href="index.php">
      <i class="bi bi-grid"></i>
      <span>Dashboard</span>
    </a>
  </li><!-- End Dashboard Nav -->
  <!-- si existe una sesion iniciada pon los siguientes hipervinculos  -->
  <?php if($_SESSION["user"]["ROL"] == 1) : ?>
  <li class="nav-item">
    <a class="nav-link collapsed" data-bs-target="#components-nav" data-bs-toggle="collapse" href="#">
      <i class="bi bi-menu-button-wide"></i><span>Usuarios</span><i class="bi bi-chevron-down ms-auto"></i>
    </a>
    <ul id="components-nav" class="nav-content collapse " data-bs-parent="#sidebar-nav">
      <li>
        <a href="personas.php">
          <i class="bi bi-circle"></i><span>Personas</span>
        </a>
      </li>
      <li>
        <a href="usuarios.php">
          <i class="bi bi-circle"></i><span>Usuarios</span>
        </a>
      </li>
      <li>
        <a href="personasEliminadas.php">
          <i class="bi bi-circle"></i><span>Personas Eliminadas</span>
        </a>
      </li>
    </ul>
  </li><!-- End Components Nav -->

  <li class="nav-item">
    <a class="nav-link collapsed" data-bs-target="#forms-nav" data-bs-toggle="collapse" href="#">
      <i class="bi bi-journal-text"></i><span>OP's</span><i class="bi bi-chevron-down ms-auto"></i>
    </a>
    <ul id="forms-nav" class="nav-content collapse " data-bs-parent="#sidebar-nav">
      <li>
        <a href="ciudades.php">
          <i class="bi bi-circle"></i><span>Ciudad de Produccion</span>
        </a>
      </li>
      <li>
        <a href="op.php">
          <i class="bi bi-circle"></i><span>Orden de Produccion</span>
        </a>
      </li>
    </ul>
  </li>

  <!-- si existe una sesion iniciada pon los siguientes hipervinculos  -->
  <?php elseif($_SESSION["user"]["ROL"] == 2) : ?>

  <li class="nav-item">
    <a class="nav-link collapsed" data-bs-target="#forms-nav" data-bs-toggle="collapse" href="#">
      <i class="bi bi-journal-text"></i><span>Registros</span><i class="bi bi-chevron-down ms-auto"></i>
    </a>
    <ul id="forms-nav" class="nav-content collapse " data-bs-parent="#sidebar-nav">
      <li>
        <a href="registro.php">
          <i class="bi bi-circle"></i><span>Ingresar Registro</span>
        </a>
      </li>
      <li>
        <a href="#">
          <i class="bi bi-circle"></i><span>Historial de mis registros</span>
        </a>
      </li>
    </ul>
  </li>
  <?php endif ?>

    
</ul>
<?php else : ?>
<?php endif ?>
</aside><!-- End Sidebar-->

<main id="main" class="main">
