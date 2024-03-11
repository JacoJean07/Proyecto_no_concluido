<?php

session_start();


// Establecer el tiempo de inactividad en segundos
$inactive = 10000;

// Comprobar si la variable de sesión 'timeout' está establecida
if (isset($_SESSION['timeout'])) {
    // Calcular el tiempo de inactividad
    $session_life = time() - $_SESSION['timeout'];

    // Si ha pasado el tiempo de inactividad, destruir la sesión
    if ($session_life > $inactive) {
        session_destroy();
        header("Location: logout.php"); // redirigir al usuario a la página de logout
        exit();
    }
}

// Actualizar la variable de sesión 'timeout' con la hora actual
$_SESSION['timeout'] = time();?>

<script>
    // Obtener el tiempo de inactividad en milisegundos
    var inactiveTime = <?php echo $inactive * 1000; ?>;

    // Función para recargar la página después de que pase el tiempo de inactividad
    function reloadPage() {
        location.reload();
    }

    // Reiniciar el temporizador de inactividad cada vez que se detecte una interacción del usuario
    var timeout = setTimeout(reloadPage, inactiveTime);

    document.addEventListener("mousemove", function() {
        clearTimeout(timeout);
        timeout = setTimeout(reloadPage, inactiveTime);
    });

    document.addEventListener("keydown", function() {
        clearTimeout(timeout);
        timeout = setTimeout(reloadPage, inactiveTime);
    });
</script>
