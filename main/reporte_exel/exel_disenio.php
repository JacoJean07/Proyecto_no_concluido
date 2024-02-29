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
        $stmPrimeraSemana4 = $conn->prepare($sqlFecha1);
        $stmPrimeraSemana4->bindParam(':fecha_limite', $fecha_limite4);
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

        // de la primera semana
        $nuevaHoja->setCellValue('K6', 'DISEÑADOR');
        $nuevaHoja->setCellValue('L6', 'LUNES');
        $nuevaHoja->setCellValue('M6', 'MARTES');
        $nuevaHoja->setCellValue('N6', 'MIERCOLES');
        $nuevaHoja->setCellValue('O6', 'JUEVES');
        $nuevaHoja->setCellValue('P6', 'VIERNES');
        $nuevaHoja->setCellValue('Q6', 'SABADO');
        $nuevaHoja->setCellValue('R6', 'DOMINGO');
        //SEGUNDA SEMANA
        $nuevaHoja->setCellValue('U6', 'DISEÑADOR');
        $nuevaHoja->setCellValue('V6', 'LUNES');
        $nuevaHoja->setCellValue('W6', 'MARTES');
        $nuevaHoja->setCellValue('X6', 'MIERCOLES');
        $nuevaHoja->setCellValue('Y6', 'JUEVES');
        $nuevaHoja->setCellValue('Z6', 'VIERNES');
        $nuevaHoja->setCellValue('AA6', 'SABADO');
        $nuevaHoja->setCellValue('AB6', 'DOMINGO');
        //TERCERA semana
        $nuevaHoja->setCellValue('AE6', 'DISEÑADOR');
        $nuevaHoja->setCellValue('AF6', 'LUNES');
        $nuevaHoja->setCellValue('AG6', 'MARTES');
        $nuevaHoja->setCellValue('AH6', 'MIERCOLES');
        $nuevaHoja->setCellValue('AI6', 'JUEVES');
        $nuevaHoja->setCellValue('AJ6', 'VIERNES');
        $nuevaHoja->setCellValue('AK6', 'SABADO');
        $nuevaHoja->setCellValue('AL6', 'DOMINGO');
        //CUARTA semana
        $nuevaHoja->setCellValue('AO6', 'DISEÑADOR');
        $nuevaHoja->setCellValue('AP6', 'LUNES');
        $nuevaHoja->setCellValue('AQ6', 'MARTES');
        $nuevaHoja->setCellValue('AR6', 'MIERCOLES');
        $nuevaHoja->setCellValue('AS6', 'JUEVES');
        $nuevaHoja->setCellValue('AT6', 'VIERNES');
        $nuevaHoja->setCellValue('AU6', 'SABADO');
        $nuevaHoja->setCellValue('AV6', 'DOMINGO');
        //QUINTA semna
        $nuevaHoja->setCellValue('AZ6', 'DISEÑADOR');
        $nuevaHoja->setCellValue('BA6', 'LUNES');
        $nuevaHoja->setCellValue('BB6', 'MARTES');
        $nuevaHoja->setCellValue('BC6', 'MIERCOLES');
        $nuevaHoja->setCellValue('BD6', 'JUEVES');
        $nuevaHoja->setCellValue('BE6', 'VIERNES');
        $nuevaHoja->setCellValue('BF6', 'SABADO');
        $nuevaHoja->setCellValue('BG6', 'DOMINGO');

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

            // Mostrar los datos de la segunda consulta si están disponibles
            if ($rowPrimeraSemana) {
                // Mostrar el diseñador en la columna K
                $nuevaHoja->setCellValue('K' . $filaNuevaHoja, $rowPrimeraSemana['CEDULA_NOMBRES'] . ' ' . $rowPrimeraSemana['CEDULA_APELLIDOS']);

                // Mostrar la cantidad de registros por día de la semana
                $nuevaHoja->setCellValue('L' . $filaNuevaHoja, $rowPrimeraSemana['LUNES']);
                $nuevaHoja->setCellValue('M' . $filaNuevaHoja, $rowPrimeraSemana['MARTES']);
                $nuevaHoja->setCellValue('N' . $filaNuevaHoja, $rowPrimeraSemana['MIERCOLES']);
                $nuevaHoja->setCellValue('O' . $filaNuevaHoja, $rowPrimeraSemana['JUEVES']);
                $nuevaHoja->setCellValue('P' . $filaNuevaHoja, $rowPrimeraSemana['VIERNES']);
                $nuevaHoja->setCellValue('Q' . $filaNuevaHoja, $rowPrimeraSemana['SABADO']);
                $nuevaHoja->setCellValue('R' . $filaNuevaHoja, $rowPrimeraSemana['DOMINGO']);
            }
            // Mostrar los datos de la segunda consulta si están disponibles
            if ($rowPrimeraSemana1) {
                // Mostrar el diseñador en la columna U
                $nuevaHoja->setCellValue('U' . $filaNuevaHoja, $rowPrimeraSemana1['CEDULA_NOMBRES'] . ' ' . $rowPrimeraSemana1['CEDULA_APELLIDOS']);

                // Mostrar la cantidad de registros por día de la semana
                $nuevaHoja->setCellValue('V' . $filaNuevaHoja, $rowPrimeraSemana1['LUNES']);
                $nuevaHoja->setCellValue('W' . $filaNuevaHoja, $rowPrimeraSemana1['MARTES']);
                $nuevaHoja->setCellValue('X' . $filaNuevaHoja, $rowPrimeraSemana1['MIERCOLES']);
                $nuevaHoja->setCellValue('Y' . $filaNuevaHoja, $rowPrimeraSemana1['JUEVES']);
                $nuevaHoja->setCellValue('Z' . $filaNuevaHoja, $rowPrimeraSemana1['VIERNES']);
                $nuevaHoja->setCellValue('AA' . $filaNuevaHoja, $rowPrimeraSemana1['SABADO']);
                $nuevaHoja->setCellValue('AB' . $filaNuevaHoja, $rowPrimeraSemana1['DOMINGO']);
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
                $nuevaHoja->setCellValue('AZ' . $filaNuevaHoja, $rowPrimeraSemana4['CEDULA_NOMBRES'] . ' ' . $rowPrimeraSemana4['CEDULA_APELLIDOS']);

                // Mostrar la cantidad de registros por día de la semana
                $nuevaHoja->setCellValue('BA' . $filaNuevaHoja, $rowPrimeraSemana4['LUNES']);
                $nuevaHoja->setCellValue('BB' . $filaNuevaHoja, $rowPrimeraSemana4['MARTES']);
                $nuevaHoja->setCellValue('BC' . $filaNuevaHoja, $rowPrimeraSemana4['MIERCOLES']);
                $nuevaHoja->setCellValue('BD' . $filaNuevaHoja, $rowPrimeraSemana4['JUEVES']);
                $nuevaHoja->setCellValue('BE' . $filaNuevaHoja, $rowPrimeraSemana4['VIERNES']);
                $nuevaHoja->setCellValue('BF' . $filaNuevaHoja, $rowPrimeraSemana4['SABADO']);
                $nuevaHoja->setCellValue('BG' . $filaNuevaHoja, $rowPrimeraSemana4['DOMINGO']);
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
