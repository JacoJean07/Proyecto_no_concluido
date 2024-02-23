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

// Consulta SQL para obtener datos de la base de datos
$sql = "SELECT OP.*, 
        CEDULA.PERNOMBRES AS CEDULA_NOMBRES, 
        CEDULA.PERAPELLIDOS AS CEDULA_APELLIDOS,
        VENDEDOR.PERNOMBRES AS VENDEDOR_NOMBRES, 
        VENDEDOR.PERAPELLIDOS AS VENDEDOR_APELLIDOS
        FROM OP
        LEFT JOIN PERSONAS AS CEDULA ON OP.CEDULA = CEDULA.CEDULA
        LEFT JOIN PERSONAS AS VENDEDOR ON OP.OPVENDEDOR = VENDEDOR.CEDULA";

// Ejecutar la consulta y obtener el resultado
$resultado = $conn->query($sql);

// Verificar si la consulta se ejecutó correctamente
if (!$resultado) {
    die("Error en la consulta: " . $conn->errorInfo()[2]); // Mostrar mensaje de error y terminar el script
}

// Crear una instancia de PhpSpreadsheet
$excel = new Spreadsheet();

// Seleccionar la hoja activa y establecer su título
$hojaActiva = $excel->getActiveSheet();
$hojaActiva->setTitle("Reporte de las Op");

// Establecer encabezados de columnas
$hojaActiva->setCellValue('A1', 'OP');
$hojaActiva->setCellValue('B1', 'CLIENTE');
$hojaActiva->setCellValue('C1', 'CIUDAD');
$hojaActiva->setCellValue('D1', 'DETALLE');
$hojaActiva->setCellValue('E1', 'FECHA REGISTRO');
$hojaActiva->setCellValue('F1', 'FECHA NOTIFICACION POR CORREO');
$hojaActiva->setCellValue('G1', 'DISENADOR');
$hojaActiva->setCellValue('H1', 'VENDEDOR');
$hojaActiva->setCellValue('I1', 'DIRRECION DEL LOCAL');
$hojaActiva->setCellValue('J1', 'PERSONA DE CONTACTO');
$hojaActiva->setCellValue('K1', 'TELEFONO');
$hojaActiva->setCellValue('L1', 'REPROSESO');
$hojaActiva->setCellValue('M1', 'ESTADO');

// Obtener el número de filas inicial para los datos
$fila = 2;

// Iterar sobre los resultados de la consulta y agregar datos a la hoja de cálculo
while ($rows = $resultado->fetch(PDO::FETCH_ASSOC)) {
    // Convertir el número del estado a texto según diferentes casos
    switch ($rows['OPESTADO']) {
        case 1:
            $estado = 'OP CREADA';
            break;
        case 2:
            $estado = 'OP EN PRODUCCIÓN';
            break;
        case 3:
            $estado = 'OP EN PAUSA';
            break;
        case 4:
            $estado = 'OP ANULADA';
            // Establecer el color de relleno de la fila en la columna M como rojo
            $hojaActiva->getStyle('A'.$fila.':M'.$fila)->applyFromArray([
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'FF0000'], // Color rojo
                ],
            ]);
            break;
        case 5:
            $estado = 'OP FINALIZADA';
            break;
        default:
            $estado = 'ESTADO DESCONOCIDO';
    }
    //CONVERTIR DE NUMERO A LETRAS EN REPROSESO
    switch ($rows['OPREPROSESO']) {
        case 0:
            $reproseso = '';
            break;
        case 1:
            $reproseso = 'ES UN REPROSESO';
            break;
        default:
            $reproseso = 'REPROSEOS DESCONOCIDO';
    }

    // Agregar datos a las celdas
    $hojaActiva->setCellValue('A' . $fila, $rows['IDOP']);
    $hojaActiva->setCellValue('B' . $fila, $rows['OPCLIENTE']);
    $hojaActiva->setCellValue('C' . $fila, $rows['OPCIUDAD']);
    $hojaActiva->setCellValue('D' . $fila, $rows['OPDETALLE']);
    $hojaActiva->setCellValue('E' . $fila, $rows['OPREGISTRO']);
    $hojaActiva->setCellValue('F' . $fila, $rows['OPNOTIFICACIONCORREO']);
    $hojaActiva->setCellValue('G' . $fila, $rows['CEDULA_NOMBRES'] . ' ' . $rows['CEDULA_APELLIDOS']);
    $hojaActiva->setCellValue('H' . $fila, $rows['VENDEDOR_NOMBRES'] . ' ' . $rows['VENDEDOR_APELLIDOS']);
    $hojaActiva->setCellValue('I' . $fila, $rows['OPDIRECCIONLOCAL']);
    $hojaActiva->setCellValue('J' . $fila, $rows['OPPERESONACONTACTO']);
    $hojaActiva->setCellValue('K' . $fila, $rows['TELEFONO']);
    $hojaActiva->setCellValue('L' . $fila, $reproseso);
    $hojaActiva->setCellValue('M' . $fila, $estado); // Usar el estado convertido en lugar del número
    $fila++;
}

// Establecer estilos y ajustes de tamaño de celdas
$hojaActiva->getStyle('A1:M' . $fila)->getAlignment()->setWrapText(true); // Activar el ajuste de texto en las celdas
$hojaActiva->getStyle('A1:M' . $fila)->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER); // Centrar verticalmente el texto en las celdas

// Ajustar automáticamente el tamaño de las columnas y filas
foreach (range('A', 'M') as $columnID) {
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

$hojaActiva->getStyle('A1:M' . $fila)->applyFromArray($styleArray);


// Crear un objeto Writer para Xlsx
$writer = new Xlsx($excel);

// Establecer las cabeceras para forzar la descarga del archivo
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="reporteOP.xlsx"');
header('Cache-Control: max-age=0');

// Guardar el archivo en la salida (output)
$writer->save('php://output');
exit;
