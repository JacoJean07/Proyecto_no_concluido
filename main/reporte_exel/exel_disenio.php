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
    if(isset($_POST['selectYear']) && isset($_POST['selectMonth'])) {
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

        // Crear una nueva hoja en el archivo de Excel y establecer su título como "Reporte"
        $nuevaHoja = $excel->createSheet()->setTitle('REPORTE');
        // Consulta SQL para obtener los diseñadores y la cantidad de registros que tienen cada uno
        $sqlNuevaHoja = "SELECT 
                            CEDULA.PERNOMBRES AS CEDULA_NOMBRES, 
                            CEDULA.PERAPELLIDOS AS CEDULA_APELLIDOS,
                            COUNT(*) AS REGISTROS
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
        //ESTABLECER ENCABEZADOS DE COLUMNAS
        $nuevaHoja->setCellValue('A1', 'DISEÑADOR');
        $nuevaHoja->setCellValue('B1', 'CANTIDAD DE REGISTROS');

        // Obtener el número de filas inicial para los datos de la hoja nueva
        $filaNuevaHoja = 2;

        // Iterar sobre los resultados de la consulta y agregar datos a la hoja de cálculo
        while ($row = $stmtNuevaHoja->fetch(PDO::FETCH_ASSOC)) {
            // Mostrar el diseñador y la cantidad de registros en la hoja nueva
            $nuevaHoja->setCellValue('A' . $filaNuevaHoja, $row['CEDULA_NOMBRES'] . ' ' . $row['CEDULA_APELLIDOS']);
            $nuevaHoja->setCellValue('B' . $filaNuevaHoja, $row['REGISTROS']);

            // Incrementar el contador de filas para la hoja nueva
            $filaNuevaHoja++;
        }

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
?>
