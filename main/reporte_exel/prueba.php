<?php
if ($rowHojaHora) {
    // Mostrar el diseñador en la columna A
    $nuevaHoja->setCellValue('A' . $filaNuevaHoja, $rowHojaHora['CEDULA_NOMBRES'] . ' ' . $rowHojaHora['CEDULA_APELLIDOS']);

    // Mostrar la cantidad de registros por día de la semana
    $nuevaHoja->setCellValue('B' . $filaNuevaHoja, $rowHojaHora['LUNES']);
    $nuevaHoja->setCellValue('C' . $filaNuevaHoja, $rowHojaHora['MARTES']);
    $nuevaHoja->setCellValue('D' . $filaNuevaHoja, $rowHojaHora['MIERCOLES']);
    $nuevaHoja->setCellValue('E' . $filaNuevaHoja, $rowHojaHora['JUEVES']);
    $nuevaHoja->setCellValue('F' . $filaNuevaHoja, $rowHojaHora['VIERNES']);
    $nuevaHoja->setCellValue('G' . $filaNuevaHoja, $rowHojaHora['SABADO']);
    $nuevaHoja->setCellValue('H' . $filaNuevaHoja, $rowHojaHora['DOMINGO']);
}