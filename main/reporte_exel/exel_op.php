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

if (($_SESSION["user"]["ROL"]) && ($_SESSION["user"]["ROL"] == 1)) {
     //llamr los contactos de la base de datos y especificar que sean los que tengan la op_id de la funcion seccion_start
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
date_default_timezone_set('America/Lima'); 
// Crear una instancia de PhpSpreadsheet
$excel = new Spreadsheet();
//CARGAR IMAGENE
$imgPath = '../../exel/logo_icon.jpeg';//ruta de la Imagen
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
$hojaActiva->setCellValue('C2','REPORTE GENERADO POR');
$hojaActiva->getStyle('C2:C3')->getFont()->setBold(true)->setSize(13);
// Obtener la cédula del usuario actualmente logueado
$cedulaUsuario = $_SESSION["user"]["CEDULA"];

// Consultar la base de datos para obtener los nombres y apellidos asociados a la cédula
$sqlUsuario = "SELECT PERNOMBRES, PERAPELLIDOS FROM PERSONAS WHERE CEDULA = :cedulaUsuario";
$stmt = $conn->prepare($sqlUsuario);
$stmt->bindParam(':cedulaUsuario', $cedulaUsuario);
$stmt->execute();
$usuario = $stmt->fetch(PDO::FETCH_ASSOC);

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
$hojaActiva->setCellValue('A6', 'OP');
$hojaActiva->setCellValue('B6', 'CLIENTE');
$hojaActiva->setCellValue('C6', 'CIUDAD');
$hojaActiva->setCellValue('D6', 'DETALLE');
$hojaActiva->setCellValue('E6', 'FECHA REGISTRO');
$hojaActiva->setCellValue('F6', 'FECHA NOTIFICACION POR CORREO');
$hojaActiva->setCellValue('G6', 'DISEÑADOR');
$hojaActiva->setCellValue('H6', 'VENDEDOR');
$hojaActiva->setCellValue('I6', 'DIRRECION DEL LOCAL');
$hojaActiva->setCellValue('J6', 'PERSONA DE CONTACTO');
$hojaActiva->setCellValue('K6', 'TELEFONO');
$hojaActiva->setCellValue('L6', 'REPROSESO');
$hojaActiva->setCellValue('M6', 'ESTADO');

// Obtener el número de filas inicial para los datos
$fila = 7;

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
    $estado = 'OTRA PALABRA';
    // Establecer el color de relleno de la fila en la columna M como rojo y color de fuente blanco
    $hojaActiva->getStyle('A' . $fila . ':M' . $fila)->applyFromArray([
        'fill' => [
            'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
            'startColor' => ['rgb' => 'FF0000'], // Color rojo
        ],
        'font' => [
            'color' => ['rgb' => 'FFFFFF'], // Color de fuente blanco
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
// Establecer el estilo de negrita y tamaño de letra en la columna 'OP' con tamaño de letra 16
$hojaActiva->getStyle('A' . $fila)->applyFromArray([
'font' => [
    'bold' => true, // Establecer como negrita
    'size' => 16,   // Establecer tamaño de letra
],
]);

// Establecer estilos de la fila 6
$hojaActiva->getStyle('A6:M6')->applyFromArray([
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
$hojaActiva->getStyle('A6:M' . $fila)->getAlignment()->setWrapText(true); // Activar el ajuste de texto en las celdas
$hojaActiva->getStyle('A6:M' . $fila)->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER); // Centrar verticalmente el texto en las celdas

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

$hojaActiva->getStyle('A5:M' . $fila)->applyFromArray($styleArray);


// Crear un objeto Writer para Xlsx
$writer = new Xlsx($excel);

// Establecer las cabeceras para forzar la descarga del archivo
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="reporteOP.xlsx"');
header('Cache-Control: max-age=0');

// Guardar el archivo en la salida (output)
$writer->save('php://output');
// Registrar el movimiento en el kardex
registrarEnKardex($_SESSION["user"]["ID_USER"], $_SESSION["user"]["USER"], "Se a generado un reporte", 'OP', "Reporte");

exit;


}else {
    header("Location:./index.php");
    return;
}
?>
   