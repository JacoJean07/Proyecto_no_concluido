<?php
require "../../sql/database.php"; // Incluir archivo de configuración de la base de datos
require "../partials/kardex.php"; // Incluir otros archivos necesarios
require "../../exel/vendor/autoload.php"; // Incluir la biblioteca PhpSpreadsheet

session_start(); // Iniciar sesión

// Si la sesión no existe, redirigir al formulario de inicio de sesión y salir del script
if (!isset($_SESSION["user"])) {
    header("Location: ../login-form/login.php");
    exit;
}

use PhpOffice\PhpSpreadsheet\Spreadsheet; // Importar la clase Spreadsheet
use PhpOffice\PhpSpreadsheet\Writer\Xlsx; // Importar la clase Xlsx para escribir en formato Excel
use PhpOffice\PhpSpreadsheet\IOFactory; // Importar la clase IOFactory para manejar la entrada y salida

if (!isset($_SESSION["user"]) || !isset($_SESSION["user"]["ROL"]) || ($_SESSION["user"]["ROL"] == 1 || $_SESSION["user"]["ROL"] == 2)) {
    // Verificar si se enviaron los parámetros del año y el mes
    if (isset($_POST['selectYear']) && isset($_POST['selectMonth'])) {
        $year = $_POST['selectYear'];
        $month = $_POST['selectMonth'];

        // Consulta SQL para obtener datos de la base de datos con filtro por año y mes

        $sql = "SELECT REGISTROS.*,
CEDULA.PERNOMBRES AS CEDULA_NOMBRES, 
CEDULA.PERAPELLIDOS AS CEDULA_APELLIDOS
FROM REGISTROS 
LEFT JOIN PERSONAS AS CEDULA ON REGISTROS.DISENIADOR = CEDULA.CEDULA
WHERE YEAR(REGISTROS.HORA_INICIO) = :year AND MONTH(REGISTROS.HORA_INICIO) = :month";

        // Preparar y ejecutar la consulta con parámetros
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':year', $year);
        $stmt->bindParam(':month', $month);
        $stmt->execute();

        // Verificar si la consulta se ejecutó correctamente
        if (!$stmt) {
            die("Error en la consulta: " . $conn->errorInfo()[2]); // Mostrar mensaje de error y terminar el script
        }

        // Crear una instancia de PhpSpreadsheet
        $excel = new Spreadsheet();
        //CARGAR IMAGEN
        $imgPath = '../../exel/logo_icon.jpeg'; //ruta de la Imagen
        $drawing = new \PhpOffice\PhpSpreadsheet\Worksheet\Drawing();
        $drawing->setName('Logo');
        $drawing->setDescription('Logo');
        $drawing->setPath($imgPath);
        $drawing->setHeight(70); // Establecer la altura de la imagen
        $drawing->setWidth(70); // Establecer el ancho de la imagen

        // Añadir la imagen al archivo de Excel
        $drawing->setWorksheet($excel->getActiveSheet());

        // Seleccionar la hoja activa y establecer su título
        $hojaActiva = $excel->getActiveSheet();
        $hojaActiva->setTitle("Reporte de las Op");
        $hojaActiva->setCellValue('C3', 'FECHA DEL REPORTE');
        $hojaActiva->setCellValue('C2', 'REPORTE GENERADO POR');
        $hojaActiva->getStyle('C2:C3')->getFont()->setBold(true)->setSize(13);

        // Obtener la cédula del usuario actualmente logueado
        $cedulaUsuario = $_SESSION["user"]["CEDULA"];

        // Consultar la base de datos para obtener los nombres y apellidos asociados a la cédula
        $sqlUsuario = "SELECT PERNOMBRES, PERAPELLIDOS FROM PERSONAS WHERE CEDULA = :cedulaUsuario";
        $stmtUsuario = $conn->prepare($sqlUsuario);
        $stmtUsuario->bindParam(':cedulaUsuario', $cedulaUsuario);
        $stmtUsuario->execute();
        $usuario = $stmtUsuario->fetch(PDO::FETCH_ASSOC);

        // Verificar si se encontraron resultados
        if ($usuario) {
            // Obtener nombres y apellidos del usuario
            $nombresUsuario = $usuario['PERNOMBRES'];
            $apellidosUsuario = $usuario['PERAPELLIDOS'];

            // Mostrar los nombres y apellidos del usuario en la celda D3
            $hojaActiva->setCellValue('D2', $nombresUsuario . ' ' . $apellidosUsuario);
        } else {
            // En caso de no encontrar resultados, mostrar un mensaje alternativo
            $hojaActiva->setCellValue('D2', 'Usuario no encontrado');
        }

        // Obtener la fecha y hora actual
        $fechaHoraActual = date('Y-m-d H:i:s'); // Formato: Año-Mes-Día Hora:Minuto:Segundo

        // Añadir la fecha y hora actual en la celda D4
        $hojaActiva->setCellValue('D3', $fechaHoraActual);
        $hojaActiva->getStyle('D2:D3')->getFont()->setBold(true)->setSize(13);

        // Establecer encabezados de columnas
        $hojaActiva->setCellValue('A6', 'N0.');
        $hojaActiva->setCellValue('B6', 'DISEÑADOR.');
        $hojaActiva->setCellValue('C6', 'MARCA.');
        $hojaActiva->setCellValue('D6', 'PRODUCTO.');
        $hojaActiva->setCellValue('E6', 'FECHA HORA INICIO.');
        $hojaActiva->setCellValue('F6', 'FECHA  HORA FINAL.');
        $hojaActiva->setCellValue('G6', 'TIEMPO.');
        $hojaActiva->setCellValue('H6', 'OBSERVACION.');

        // Obtener el número de filas inicial para los datos
        $fila = 7;

        // Iterar sobre los resultados de la consulta y agregar datos a la hoja de cálculo
        while ($rows = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $hojaActiva->setCellValue('A' . $fila, $rows['ID']);
            $hojaActiva->setCellValue('B' . $fila, $rows['CEDULA_NOMBRES'] . ' ' . $rows['CEDULA_APELLIDOS']);
            $hojaActiva->setCellValue('C' . $fila, $rows['PRODUCTO']);
            $hojaActiva->setCellValue('D' . $fila, $rows['PRODUCTO']);
            $hojaActiva->setCellValue('E' . $fila, $rows['HORA_INICIO']);
            $hojaActiva->setCellValue('F' . $fila, $rows['HORA_FINAL']);

            // Calcular la diferencia entre la hora inicial y la hora final
            $horaInicio = strtotime($rows['HORA_INICIO']);
            $horaFinal = strtotime($rows['HORA_FINAL']);
            $diferencia = $horaFinal - $horaInicio;

            // Formatear la diferencia en horas, minutos y segundos
            $horas = floor($diferencia / 3600);
            $minutos = floor(($diferencia % 3600) / 60);
            $segundos = $diferencia % 60;

            // Construir el tiempo en un formato legible
            $tiempo = sprintf('%02d:%02d:%02d', $horas, $minutos, $segundos);

            // Asignar el tiempo a la columna correspondiente
            $hojaActiva->setCellValue('G' . $fila, $tiempo);

            $hojaActiva->setCellValue('H' . $fila, $rows['OBSERVACIONES']);

            // Establecer estilos de la fila 6
            $hojaActiva->getStyle('A6:H6')->applyFromArray([
                'font' => [
                    'bold' => true, // Negrita
                    'size' => 14,   // Tamaño de letra 14
                    'color' => ['rgb' => 'FFFFFF'], // Color de fuente blanco
                ],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '0000FF'], // Color de relleno azul
                ],
                'alignment' => [
                    'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER, // Centrado horizontal
                    'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER, // Centrado vertical
                ],
            ]);

            // Establecer el alto de la fila 6
            $hojaActiva->getRowDimension('6')->setRowHeight(70);

            $fila++;
        }

        // Establecer estilos y ajustes de tamaño de celdas
        $hojaActiva->getStyle('A6:H' . $fila)->getAlignment()->setWrapText(true); // Activar el ajuste de texto en las celdas
        $hojaActiva->getStyle('A6:H' . $fila)->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER); // Centrar verticalmente el texto en las celdas

        // Ajustar automáticamente el tamaño de las columnas y filas
        foreach (range('A', 'H') as $columnID) {
            $hojaActiva->getColumnDimension($columnID)->setAutoSize(true);
        }

        // Agregar bordes a las celdas
        $styleArray = [
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    'color' => ['rgb' => '000000'], // Color del borde (en este caso, negro)
                ],
            ],
        ];

        $hojaActiva->getStyle('A6:H' . $fila)->applyFromArray($styleArray);

        // Crear una nueva hoja en el archivo de Excel y establecer su título
        $nuevaHoja = $excel->createSheet()->setTitle('REPORTE');
        // Definir la fecha límite para la primera semana del mes
        $fecha_limite = date('Y-m-01', strtotime($year . '-' . $month));
        $fecha_limite1 = date('Y-m-08', strtotime($year . '-' . $month));
        $fecha_limite2 = date('Y-m-15', strtotime($year . '-' . $month));
        $fecha_limite3 = date('Y-m-22', strtotime($year . '-' . $month));

         $fecha_limite4 = date('Y-m-29', strtotime($year . '-' . $month));


        // Consulta SQL para obtener los diseñadores y la cantidad de registros que tienen cada uno por día de la semana en la primera semana del mes
        $sqlFecha1 = "SELECT 
                    CEDULA.PERNOMBRES AS CEDULA_NOMBRES, 
                    CEDULA.PERAPELLIDOS AS CEDULA_APELLIDOS,
                    SUM(CASE WHEN DAYOFWEEK(REGISTROS.HORA_INICIO) = 2 THEN 1 ELSE 0 END) AS LUNES,
                    SUM(CASE WHEN DAYOFWEEK(REGISTROS.HORA_INICIO) = 3 THEN 1 ELSE 0 END) AS MARTES,
                    SUM(CASE WHEN DAYOFWEEK(REGISTROS.HORA_INICIO) = 4 THEN 1 ELSE 0 END) AS MIERCOLES,
                    SUM(CASE WHEN DAYOFWEEK(REGISTROS.HORA_INICIO) = 5 THEN 1 ELSE 0 END) AS JUEVES,
                    SUM(CASE WHEN DAYOFWEEK(REGISTROS.HORA_INICIO) = 6 THEN 1 ELSE 0 END) AS VIERNES,
                    SUM(CASE WHEN DAYOFWEEK(REGISTROS.HORA_INICIO) = 7 THEN 1 ELSE 0 END) AS SABADO,
                    SUM(CASE WHEN DAYOFWEEK(REGISTROS.HORA_INICIO) = 1 THEN 1 ELSE 0 END) AS DOMINGO
                FROM 
                    REGISTROS 
                    LEFT JOIN PERSONAS AS CEDULA ON REGISTROS.DISENIADOR = CEDULA.CEDULA
                WHERE 
                    REGISTROS.HORA_INICIO >= :fecha_limite AND REGISTROS.HORA_INICIO < DATE_ADD(:fecha_limite, INTERVAL 7 DAY)
                GROUP BY 
                    REGISTROS.DISENIADOR";
        // Preparar y ejecutar la consulta con parámetros para la primera semana
        $stmPrimeraSemana = $conn->prepare($sqlFecha1);
        $stmPrimeraSemana->bindParam(':fecha_limite', $fecha_limite);
        $stmPrimeraSemana->execute();
        $stmPrimeraSemana1 = $conn->prepare($sqlFecha1);
        $stmPrimeraSemana1->bindParam(':fecha_limite', $fecha_limite1);
        $stmPrimeraSemana1->execute();
        $stmPrimeraSemana2 = $conn->prepare($sqlFecha1);
        $stmPrimeraSemana2->bindParam(':fecha_limite', $fecha_limite2);
        $stmPrimeraSemana2->execute();
        $stmPrimeraSemana3 = $conn->prepare($sqlFecha1);
        $stmPrimeraSemana3->bindParam(':fecha_limite', $fecha_limite3);
        $stmPrimeraSemana3->execute();
        /*  $stmPrimeraSemana4 = $conn->prepare($sqlFecha1);
        $stmPrimeraSemana4->bindParam(':fecha_limite', $fecha_limite4);
        $stmPrimeraSemana4->execute();*/
        // Calcular el último día del mes específico
        $ultimoDiaMes = date('Y-m-t', strtotime($year . '-' . $month . '-01'));

        // Calcular los últimos tres días del mes específico
        $fecha_limite_29 = date('Y-m-d', strtotime($year . '-' . $month . '-29'));
        $fecha_limite_30 = date('Y-m-d', strtotime($year . '-' . $month . '-30'));
        $fecha_limite_31 = date('Y-m-d', strtotime($year . '-' . $month . '-31'));

        // Determinar en qué columna colocar los datos según el último día del mes
        $ultimoDia = date('j', strtotime($ultimoDiaMes)); // Obtener el día del mes
        $columna_29 = 'BA';
        $columna_30 = 'BB';
        $columna_31 = 'BC';

        if ($ultimoDia == 31) {
            $columna_29 = 'BA';
            $columna_30 = 'BB';
            $columna_31 = 'BC';
        } elseif ($ultimoDia == 30) {
            $columna_29 = 'BA';
            $columna_30 = 'BB';
        } elseif ($ultimoDia == 29) {
            $columna_29 = 'BA';
        }

        // Consulta SQL para obtener el contador de registros para los últimos tres días del mes y año específicos, por diseñador
        $sqlNueva1 = "SELECT 
               CEDULA.PERNOMBRES AS CEDULA_NOMBRES, 
               CEDULA.PERAPELLIDOS AS CEDULA_APELLIDOS,
               SUM(CASE WHEN DATE(hora_inicio) = :fecha_limite_29 THEN 1 ELSE 0 END) AS registros_29,
               SUM(CASE WHEN DATE(hora_inicio) = :fecha_limite_30 THEN 1 ELSE 0 END) AS registros_30,
               SUM(CASE WHEN DATE(hora_inicio) = :fecha_limite_31 THEN 1 ELSE 0 END) AS registros_31
           FROM REGISTROS 
           LEFT JOIN PERSONAS AS CEDULA ON REGISTROS.DISENIADOR = CEDULA.CEDULA
           WHERE 
               DATE(hora_inicio) IN (:fecha_limite_29, :fecha_limite_30, :fecha_limite_31)
               AND YEAR(hora_inicio) = :anio
               AND MONTH(hora_inicio) = :mes
           GROUP BY DISENIADOR";

        // Preparar y ejecutar la consulta
        $stmPrimeraSemana4 = $conn->prepare($sqlNueva1);
        $stmPrimeraSemana4->bindParam(':fecha_limite_29', $fecha_limite_29);
        $stmPrimeraSemana4->bindParam(':fecha_limite_30', $fecha_limite_30);
        $stmPrimeraSemana4->bindParam(':fecha_limite_31', $fecha_limite_31);
        $stmPrimeraSemana4->bindParam(':anio', $year); // Año específico
        $stmPrimeraSemana4->bindParam(':mes', $month); // Mes específico
        $stmPrimeraSemana4->execute();


        // Consulta SQL para obtener los diseñadores y la cantidad de registros que tienen cada uno por día de la semana
        $sqlNuevaHoja = "SELECT 
                            CEDULA.PERNOMBRES AS CEDULA_NOMBRES, 
                            CEDULA.PERAPELLIDOS AS CEDULA_APELLIDOS,
                            SUM(CASE WHEN DAYOFWEEK(REGISTROS.HORA_INICIO) = 2 THEN 1 ELSE 0 END) AS LUNES,
                            SUM(CASE WHEN DAYOFWEEK(REGISTROS.HORA_INICIO) = 3 THEN 1 ELSE 0 END) AS MARTES,
                            SUM(CASE WHEN DAYOFWEEK(REGISTROS.HORA_INICIO) = 4 THEN 1 ELSE 0 END) AS MIERCOLES,
                            SUM(CASE WHEN DAYOFWEEK(REGISTROS.HORA_INICIO) = 5 THEN 1 ELSE 0 END) AS JUEVES,
                            SUM(CASE WHEN DAYOFWEEK(REGISTROS.HORA_INICIO) = 6 THEN 1 ELSE 0 END) AS VIERNES,
                            SUM(CASE WHEN DAYOFWEEK(REGISTROS.HORA_INICIO) = 7 THEN 1 ELSE 0 END) AS SABADO,
                            SUM(CASE WHEN DAYOFWEEK(REGISTROS.HORA_INICIO) = 1 THEN 1 ELSE 0 END) AS DOMINGO
                        FROM 
                            REGISTROS 
                            LEFT JOIN PERSONAS AS CEDULA ON REGISTROS.DISENIADOR = CEDULA.CEDULA
                        WHERE 
                            YEAR(REGISTROS.HORA_INICIO) = :year 
                            AND MONTH(REGISTROS.HORA_INICIO) = :month
                        GROUP BY 
                            REGISTROS.DISENIADOR";

        // Preparar y ejecutar la consulta con parámetros para la nueva hoja
        $stmtNuevaHoja = $conn->prepare($sqlNuevaHoja);
        $stmtNuevaHoja->bindParam(':year', $year);
        $stmtNuevaHoja->bindParam(':month', $month);
        $stmtNuevaHoja->execute();



        // Establecer encabezados de columnas en la nueva hoja
        $nuevaHoja->setCellValue('A6', 'DISEÑADOR');
        $nuevaHoja->setCellValue('B6', 'LUNES');
        $nuevaHoja->setCellValue('C6', 'MARTES');
        $nuevaHoja->setCellValue('D6', 'MIERCOLES');
        $nuevaHoja->setCellValue('E6', 'JUEVES');
        $nuevaHoja->setCellValue('F6', 'VIERNES');
        $nuevaHoja->setCellValue('G6', 'SABADO');
        $nuevaHoja->setCellValue('H6', 'DOMINGO');



        // Crear un array para almacenar las fechas de la primera semana
        $fechas_primera_semana = array();
        for ($i = 0; $i < 7; $i++) {
            // Obtener la fecha para cada día de la primera semana
            $fecha = date('Y-m-d', strtotime($fecha_limite . " +$i days"));
            // Agregar la fecha al array
            $fechas_primera_semana[] = $fecha;
        }

        // Array de los nombres de los días de la semana en español
        $dias_semana_espanol = array(
            'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado', 'Domingo'
        );

        // Crear un array para almacenar los encabezados de la primera semana
        $encabezados_primera_semana = array();
        foreach ($fechas_primera_semana as $fecha) {
            // Obtener el nombre completo del día de la semana
            $nombre_dia = $dias_semana_espanol[date('N', strtotime($fecha)) - 1];
            // Formatear la fecha como "dd/mm" y añadir el nombre del día
            $encabezado = date('d/m', strtotime($fecha)) . ' ' . $nombre_dia;
            // Agregar el encabezado al array
            $encabezados_primera_semana[] = $encabezado;
        }

        // de la primera semana
        $nuevaHoja->setCellValue('K6', 'DISEÑADOR');
        $columna = 'L';
        foreach ($encabezados_primera_semana as $encabezado) {
            $nuevaHoja->setCellValue($columna . '6', $encabezado);
            $columna++;
        }


        // Crear un array para almacenar las fechas de la segunda semana
        $fechas_segunda_semana = array();
        for ($i = 0; $i < 7; $i++) {
            // Obtener la fecha para cada día de la segunda semana
            $fecha_segunda_semana = date('Y-m-d', strtotime($fecha_limite1 . " +$i days"));
            // Agregar la fecha al array de la segunda semana
            $fechas_segunda_semana[] = $fecha_segunda_semana;
        }

        // Crear un array para almacenar los encabezados de la segunda semana
        $encabezados_segunda_semana = array();
        foreach ($fechas_segunda_semana as $fecha) {
            // Obtener el nombre completo del día de la semana
            $nombre_dia = $dias_semana_espanol[date('N', strtotime($fecha)) - 1];
            // Formatear la fecha como "dd/mm" y añadir el nombre del día
            $encabezado = date('d/m', strtotime($fecha)) . ' ' . $nombre_dia;
            // Agregar el encabezado al array
            $encabezados_segunda_semana[] = $encabezado;
        }

        // SEGUNDA SEMANA
        $nuevaHoja->setCellValue('U6', 'DISEÑADOR');
        $columna1 = 'V';
        foreach ($encabezados_segunda_semana as $encabezado) {
            $nuevaHoja->setCellValue($columna1 . '6', $encabezado);
            $columna1++;
        }
        //TERCERA semana

        // Crear un array para almacenar las fechas de la tercera semana
        $fechas_tercera_semana = array();
        for ($i = 0; $i < 7; $i++) {
            // Obtener la fecha para cada día de la tercera semana
            $fecha_tercera_semana = date('Y-m-d', strtotime($fecha_limite2 . " +$i days"));
            // Agregar la fecha al array de la tercera semana
            $fechas_tercera_semana[] = $fecha_tercera_semana;
        }

        // Crear un array para almacenar los encabezados de la tercera semana
        $encabezados_tercera_semana = array();
        foreach ($fechas_tercera_semana as $fecha) {
            // Obtener el nombre completo del día de la semana
            $nombre_dia = $dias_semana_espanol[date('N', strtotime($fecha)) - 1];
            // Formatear la fecha como "dd/mm" y añadir el nombre del día
            $encabezado = date('d/m', strtotime($fecha)) . ' ' . $nombre_dia;
            // Agregar el encabezado al array
            $encabezados_tercera_semana[] = $encabezado;
        }

        // Mostrar los encabezados en la tercera semana
        $nuevaHoja->setCellValue('AE6', 'DISEÑADOR');
        $columna1 = 'AF';
        foreach ($encabezados_tercera_semana as $encabezado) {
            $nuevaHoja->setCellValue($columna1 . '6', $encabezado);
            $columna1++;
        }

        //CUARTA semana
        // Crear un array para almacenar las fechas de la tercera semana
        $fechas_cuarta_semana = array();
        for ($i = 0; $i < 7; $i++) {
            // Obtener la fecha para cada día de la tercera semana
            $fecha_cuarta_semana = date('Y-m-d', strtotime($fecha_limite3 . " +$i days"));
            // Agregar la fecha al array de la tercera semana
            $fechas_cuarta_semana[] = $fecha_cuarta_semana;
        }

        // Crear un array para almacenar los encabezados de la tercera semana
        $encabezados_cuarta_semana = array();
        foreach ($fechas_cuarta_semana as $fecha) {
            // Obtener el nombre completo del día de la semana
            $nombre_dia = $dias_semana_espanol[date('N', strtotime($fecha)) - 1];
            // Formatear la fecha como "dd/mm" y añadir el nombre del día
            $encabezado = date('d/m', strtotime($fecha)) . ' ' . $nombre_dia;
            // Agregar el encabezado al array
            $encabezados_cuarta_semana[] = $encabezado;
        }

        // Mostrar los encabezados en la tercera semana
        $nuevaHoja->setCellValue('AO6', 'DISEÑADOR');
        $columna1 = 'AP';
        foreach ($encabezados_cuarta_semana as $encabezado) {
            $nuevaHoja->setCellValue($columna1 . '6', $encabezado);
            $columna1++;
        }


        //QUINTA semna
         // Crear un array para almacenar las fechas de la tercera semana
         $fechas_quinta_semana = array();
         for ($i = 0; $i < 3; $i++) {
             // Obtener la fecha para cada día de la tercera semana
             $fecha_quinta_semana = date('Y-m-d', strtotime($fecha_limite4 . " +$i days"));
             // Agregar la fecha al array de la tercera semana
             $fechas_quinta_semana[] = $fecha_quinta_semana;
         }
 
         // Crear un array para almacenar los encabezados de la tercera semana
         $encabezados_quinta_semana = array();
         foreach ($fechas_quinta_semana as $fecha) {
             // Obtener el nombre completo del día de la semana
             $nombre_dia = $dias_semana_espanol[date('N', strtotime($fecha)) - 1];
             // Formatear la fecha como "dd/mm" y añadir el nombre del día
             $encabezado = date('d/m', strtotime($fecha)) . ' ' . $nombre_dia;
             // Agregar el encabezado al array
             $encabezados_quinta_semana[] = $encabezado;
         }
 
         // Mostrar los encabezados en la tercera semana
         $nuevaHoja->setCellValue('AZ6', 'DISEÑADOR');
         $columna1 = 'BA';
         foreach ($encabezados_quinta_semana as $encabezado) {
             $nuevaHoja->setCellValue($columna1 . '6', $encabezado);
             $columna1++;
         }


        // Obtener el número de filas inicial para los datos de la hoja nueva
        $filaNuevaHoja = 7;
        // Obtener el número de filas necesario para ambas consultas
        $totalFilas = max($stmPrimeraSemana->rowCount(), $stmPrimeraSemana1->rowCount(), $stmPrimeraSemana2->rowCount(), $stmPrimeraSemana3->rowCount(),  $stmPrimeraSemana4->rowCount(), $stmtNuevaHoja->rowCount());

        // Iterar sobre las filas y escribir los datos en la hoja de Excel
        for ($i = 0; $i < $totalFilas; $i++) {
            // Obtener los datos de la primera consulta
            $rowPrimeraSemana = $stmPrimeraSemana->fetch(PDO::FETCH_ASSOC);
            $rowPrimeraSemana1 = $stmPrimeraSemana1->fetch(PDO::FETCH_ASSOC);
            $rowPrimeraSemana2 = $stmPrimeraSemana2->fetch(PDO::FETCH_ASSOC);
            $rowPrimeraSemana3 = $stmPrimeraSemana3->fetch(PDO::FETCH_ASSOC);
            $rowPrimeraSemana4 = $stmPrimeraSemana4->fetch(PDO::FETCH_ASSOC);
            // Obtener los datos de la segunda consulta
            $rowNuevaHoja = $stmtNuevaHoja->fetch(PDO::FETCH_ASSOC);   // Mostrar los datos de la primera consulta si están disponibles
            if ($rowNuevaHoja) {
                // Mostrar el diseñador en la columna A
                $nuevaHoja->setCellValue('A' . $filaNuevaHoja, $rowNuevaHoja['CEDULA_NOMBRES'] . ' ' . $rowNuevaHoja['CEDULA_APELLIDOS']);

                // Mostrar la cantidad de registros por día de la semana
                $nuevaHoja->setCellValue('B' . $filaNuevaHoja, $rowNuevaHoja['LUNES']);
                $nuevaHoja->setCellValue('C' . $filaNuevaHoja, $rowNuevaHoja['MARTES']);
                $nuevaHoja->setCellValue('D' . $filaNuevaHoja, $rowNuevaHoja['MIERCOLES']);
                $nuevaHoja->setCellValue('E' . $filaNuevaHoja, $rowNuevaHoja['JUEVES']);
                $nuevaHoja->setCellValue('F' . $filaNuevaHoja, $rowNuevaHoja['VIERNES']);
                $nuevaHoja->setCellValue('G' . $filaNuevaHoja, $rowNuevaHoja['SABADO']);
                $nuevaHoja->setCellValue('H' . $filaNuevaHoja, $rowNuevaHoja['DOMINGO']);
            }

            // Mostrar los datos de la primera consulta si están disponibles
            if ($rowPrimeraSemana) {
                // Mostrar el diseñador en la columna K
                $nuevaHoja->setCellValue('K' . $filaNuevaHoja, $rowPrimeraSemana['CEDULA_NOMBRES'] . ' ' . $rowPrimeraSemana['CEDULA_APELLIDOS']);

                $columna = 'L'; // Inicializar la primera columna donde se colocarán los valores de la consulta
                foreach ($encabezados_primera_semana as $encabezado) {
                    // Obtener el nombre del día de la semana desde el encabezado
                    $nombre_dia = substr($encabezado, strpos($encabezado, ' ') + 1);

                    // Asignar el valor correspondiente del array de la consulta al día correspondiente
                    switch ($nombre_dia) {
                        case 'Lunes':
                            $nuevaHoja->setCellValue($columna . $filaNuevaHoja, $rowPrimeraSemana['LUNES']);
                            break;
                        case 'Martes':
                            $nuevaHoja->setCellValue($columna . $filaNuevaHoja, $rowPrimeraSemana['MARTES']);
                            break;
                        case 'Miércoles':
                            $nuevaHoja->setCellValue($columna . $filaNuevaHoja, $rowPrimeraSemana['MIERCOLES']);
                            break;
                        case 'Jueves':
                            $nuevaHoja->setCellValue($columna . $filaNuevaHoja, $rowPrimeraSemana['JUEVES']);
                            break;
                        case 'Viernes':
                            $nuevaHoja->setCellValue($columna . $filaNuevaHoja, $rowPrimeraSemana['VIERNES']);
                            break;
                        case 'Sábado':
                            $nuevaHoja->setCellValue($columna . $filaNuevaHoja, $rowPrimeraSemana['SABADO']);
                            break;
                        case 'Domingo':
                            $nuevaHoja->setCellValue($columna . $filaNuevaHoja, $rowPrimeraSemana['DOMINGO']);
                            break;
                        default:
                            // En caso de que no se encuentre el nombre del día, asignar un valor vacío
                            $nuevaHoja->setCellValue($columna . $filaNuevaHoja, '');
                    }

                    // Avanzar a la siguiente columna
                    $columna++;
                }
            }

            // Mostrar los datos de la segunda consulta si están disponibles
            if ($rowPrimeraSemana1) {
                // Mostrar el diseñador en la columna U
                $nuevaHoja->setCellValue('U' . $filaNuevaHoja, $rowPrimeraSemana1['CEDULA_NOMBRES'] . ' ' . $rowPrimeraSemana1['CEDULA_APELLIDOS']);

                // Inicializar la primera columna donde se colocarán los valores de la consulta
                $columna1 = 'V';

                // Iterar sobre los encabezados de la segunda semana
                foreach ($encabezados_segunda_semana as $encabezado) {
                    // Obtener el nombre del día de la semana desde el encabezado
                    $nombre_dia1 = substr($encabezado, strpos($encabezado, ' ') + 1);

                    // Asignar el valor correspondiente del array de la consulta al día correspondiente
                    switch ($nombre_dia1) {
                        case 'Lunes':
                            $nuevaHoja->setCellValue($columna1 . $filaNuevaHoja, $rowPrimeraSemana1['LUNES']);
                            break;
                        case 'Martes':
                            $nuevaHoja->setCellValue($columna1 . $filaNuevaHoja, $rowPrimeraSemana1['MARTES']);
                            break;
                        case 'Miércoles':
                            $nuevaHoja->setCellValue($columna1 . $filaNuevaHoja, $rowPrimeraSemana1['MIERCOLES']);
                            break;
                        case 'Jueves':
                            $nuevaHoja->setCellValue($columna1 . $filaNuevaHoja, $rowPrimeraSemana1['JUEVES']);
                            break;
                        case 'Viernes':
                            $nuevaHoja->setCellValue($columna1 . $filaNuevaHoja, $rowPrimeraSemana1['VIERNES']);
                            break;
                        case 'Sábado':
                            $nuevaHoja->setCellValue($columna1 . $filaNuevaHoja, $rowPrimeraSemana1['SABADO']);
                            break;
                        case 'Domingo':
                            $nuevaHoja->setCellValue($columna1 . $filaNuevaHoja, $rowPrimeraSemana1['DOMINGO']);
                            break;
                        default:
                            // En caso de que no se encuentre el nombre del día, asignar un valor vacío
                            $nuevaHoja->setCellValue($columna1 . $filaNuevaHoja, '');
                    }

                    // Avanzar a la siguiente columna
                    $columna1++;
                }
            }
            // Mostrar los datos de la segunda consulta si están disponibles
            if ($rowPrimeraSemana2) {
                // Mostrar el diseñador en la columna AE
                $nuevaHoja->setCellValue('AE' . $filaNuevaHoja, $rowPrimeraSemana2['CEDULA_NOMBRES'] . ' ' . $rowPrimeraSemana2['CEDULA_APELLIDOS']);

                // Mostrar la cantidad de registros por día de la semana
                $nuevaHoja->setCellValue('AF' . $filaNuevaHoja, $rowPrimeraSemana2['LUNES']);
                $nuevaHoja->setCellValue('AG' . $filaNuevaHoja, $rowPrimeraSemana2['MARTES']);
                $nuevaHoja->setCellValue('AH' . $filaNuevaHoja, $rowPrimeraSemana2['MIERCOLES']);
                $nuevaHoja->setCellValue('AI' . $filaNuevaHoja, $rowPrimeraSemana2['JUEVES']);
                $nuevaHoja->setCellValue('AJ' . $filaNuevaHoja, $rowPrimeraSemana2['VIERNES']);
                $nuevaHoja->setCellValue('AK' . $filaNuevaHoja, $rowPrimeraSemana2['SABADO']);
                $nuevaHoja->setCellValue('AL' . $filaNuevaHoja, $rowPrimeraSemana2['DOMINGO']);
            }
            // Mostrar los datos de la segunda consulta si están disponibles
            if ($rowPrimeraSemana3) {
                // Mostrar el diseñador en la columna AO
                $nuevaHoja->setCellValue('AO' . $filaNuevaHoja, $rowPrimeraSemana3['CEDULA_NOMBRES'] . ' ' . $rowPrimeraSemana3['CEDULA_APELLIDOS']);

                // Mostrar la cantidad de registros por día de la semana
                $nuevaHoja->setCellValue('AP' . $filaNuevaHoja, $rowPrimeraSemana3['LUNES']);
                $nuevaHoja->setCellValue('AQ' . $filaNuevaHoja, $rowPrimeraSemana3['MARTES']);
                $nuevaHoja->setCellValue('AR' . $filaNuevaHoja, $rowPrimeraSemana3['MIERCOLES']);
                $nuevaHoja->setCellValue('AS' . $filaNuevaHoja, $rowPrimeraSemana3['JUEVES']);
                $nuevaHoja->setCellValue('AT' . $filaNuevaHoja, $rowPrimeraSemana3['VIERNES']);
                $nuevaHoja->setCellValue('AU' . $filaNuevaHoja, $rowPrimeraSemana3['SABADO']);
                $nuevaHoja->setCellValue('AV' . $filaNuevaHoja, $rowPrimeraSemana3['DOMINGO']);
            }
            // Mostrar los datos de la segunda consulta si están disponibles
            if ($rowPrimeraSemana4) {
                // Mostrar el diseñador en la columna AZ
                /*$nuevaHoja->setCellValue('AZ' . $filaNuevaHoja, $rowPrimeraSemana4['CEDULA_NOMBRES'] . ' ' . $rowPrimeraSemana4['CEDULA_APELLIDOS']);

                // Mostrar la cantidad de registros por día de la semana
                $nuevaHoja->setCellValue('BA' . $filaNuevaHoja, $rowPrimeraSemana4['LUNES']);
                $nuevaHoja->setCellValue('BB' . $filaNuevaHoja, $rowPrimeraSemana4['MARTES']);
                $nuevaHoja->setCellValue('BC' . $filaNuevaHoja, $rowPrimeraSemana4['MIERCOLES']);
                $nuevaHoja->setCellValue('BD' . $filaNuevaHoja, $rowPrimeraSemana4['JUEVES']);
                $nuevaHoja->setCellValue('BE' . $filaNuevaHoja, $rowPrimeraSemana4['VIERNES']);
                $nuevaHoja->setCellValue('BF' . $filaNuevaHoja, $rowPrimeraSemana4['SABADO']);
                $nuevaHoja->setCellValue('BG' . $filaNuevaHoja, $rowPrimeraSemana4['DOMINGO']);*/
                // Mostrar el diseñador en la columna AZ
                $nuevaHoja->setCellValue('AZ' . $filaNuevaHoja, $rowPrimeraSemana4['CEDULA_NOMBRES'] . ' ' . $rowPrimeraSemana4['CEDULA_APELLIDOS']);

                // Mostrar la cantidad de registros por día de la semana
                $nuevaHoja->setCellValue($columna_29 . $filaNuevaHoja, $rowPrimeraSemana4['registros_29']);
                $nuevaHoja->setCellValue($columna_30 . $filaNuevaHoja, $rowPrimeraSemana4['registros_30']);
                $nuevaHoja->setCellValue($columna_31 . $filaNuevaHoja, $rowPrimeraSemana4['registros_31']);
            }
            $filaNuevaHoja++;
        }

        foreach (range('A', 'Z') as $columnID) {
            $nuevaHoja->getColumnDimension($columnID)->setAutoSize(true);
        }



        // Establecer un ancho específico para las columnas AE, AO y AZ
        $nuevaHoja->getColumnDimension('AE')->setWidth(40);
        $nuevaHoja->getColumnDimension('AO')->setWidth(40);
        $nuevaHoja->getColumnDimension('AZ')->setWidth(40);

        $nuevaHoja->getColumnDimension('AA')->setWidth(15);
        $nuevaHoja->getColumnDimension('AB')->setWidth(15);
        $nuevaHoja->getColumnDimension('AF')->setWidth(15);
        $nuevaHoja->getColumnDimension('AG')->setWidth(15);
        $nuevaHoja->getColumnDimension('AH')->setWidth(15);
        $nuevaHoja->getColumnDimension('AI')->setWidth(15);
        $nuevaHoja->getColumnDimension('AJ')->setWidth(15);
        $nuevaHoja->getColumnDimension('AK')->setWidth(15);
        $nuevaHoja->getColumnDimension('AL')->setWidth(15);
        $nuevaHoja->getColumnDimension('AP')->setWidth(15);
        $nuevaHoja->getColumnDimension('AQ')->setWidth(15);
        $nuevaHoja->getColumnDimension('AR')->setWidth(15);
        $nuevaHoja->getColumnDimension('AS')->setWidth(15);
        $nuevaHoja->getColumnDimension('AT')->setWidth(15);
        $nuevaHoja->getColumnDimension('AU')->setWidth(15);
        $nuevaHoja->getColumnDimension('AV')->setWidth(15);
        $nuevaHoja->getColumnDimension('BA')->setWidth(15);
        $nuevaHoja->getColumnDimension('BB')->setWidth(15);
        $nuevaHoja->getColumnDimension('BA')->setWidth(15);
        $nuevaHoja->getColumnDimension('BC')->setWidth(15);
        $nuevaHoja->getColumnDimension('BD')->setWidth(15);
        $nuevaHoja->getColumnDimension('BE')->setWidth(15);
        $nuevaHoja->getColumnDimension('BF')->setWidth(15);
        $nuevaHoja->getColumnDimension('BG')->setWidth(15);
        // Función para aplicar estilos comunes a la fila 6 en un rango de columnas específico
        function applyCommonStylesToRow6($sheet, $startColumn, $endColumn)
        {
            $columnRange = $startColumn . '6:' . $endColumn . '6';

            $sheet->getStyle($columnRange)->applyFromArray([
                'font' => [
                    'bold' => true,
                    'size' => 14,
                    'color' => ['rgb' => 'FFFFFF'],
                ],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '0000FF'],
                ],
                'alignment' => [
                    'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                    'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                ],
            ]);
        }


        // Llamar a la función para cada rango de columnas
        applyCommonStylesToRow6($nuevaHoja, 'A', 'H');
        applyCommonStylesToRow6($nuevaHoja, 'K', 'R');
        applyCommonStylesToRow6($nuevaHoja, 'AE', 'AL');
        applyCommonStylesToRow6($nuevaHoja, 'U', 'AB');
        applyCommonStylesToRow6($nuevaHoja, 'AO', 'AV');
        applyCommonStylesToRow6($nuevaHoja, 'AZ', 'BG');


        // Establecer el alto de la fila 6
        $nuevaHoja->getRowDimension('6')->setRowHeight(70);

        // Finalmente, ajusta el índice de la hoja activa
        $excel->setActiveSheetIndex(0); // Puedes ajustar el índice según sea necesario

        // Guardar el archivo de Excel y enviarlo como descarga
        $writer = new Xlsx($excel);

        // Establecer las cabeceras para forzar la descarga del archivo
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="REPORTE_DE_LOS_REGISTROS_DE_DISEÑADOR.xlsx"');
        header('Cache-Control: max-age=0');

        // Guardar el archivo en la salida (output)
        $writer->save('php://output');

        // Registrar el movimiento en el kardex
        registrarEnKardex($_SESSION["user"]["ID_USER"], $_SESSION["user"]["USER"], "Se a generado un reporte", 'REGISTROS DISEÑO', "Reporte");

        exit;
    } else {
        // Si no se enviaron los parámetros esperados, redirigir al usuario
        header("Location:./index.php");
        return;
    }
} else {
    // Si el usuario no tiene permisos para generar el reporte, redirigirlo
    header("Location:../index.php");
    return;
}
