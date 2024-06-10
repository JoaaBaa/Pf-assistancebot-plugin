<?php
// This file is part of Moodle - https://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

/**
 * Plugin strings are defined here.
 *
 * @package     mod_asistbot2
 * @category    string
 * @copyright   2024 Your Name <abich@example.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['pluginname'] = 'ASISTBOT2';
$string['modulename'] = 'AsistBot2';
$string['modulenameplural'] = 'AsistBots2';

//on-off del bot
$string['ison'] = 'Plugin Encendido';
$string['ison_help'] = 'Tildar si se quiere que el recurso esté procesando las asistencias.';

//Porcentaje acistencias y pedido de cámara
$string['attendancepercentage'] = 'Porcentaje de asistencia';
$string['attendancepercentage_help'] = 'Ingrese el porcentaje mínimo de asistencia Requerido para el curso.';
$string['invalidattendancepercentage'] = 'Rango de asistencia inválido. Ingrese un porcentaje válido entre 75 y 95.';
$string['attendancepercentagerange'] = 'El rango de asistencia debe estar entre 75% y 95%.';

$string['requirecamera'] = 'Requiere cámara';
$string['requirecamera_help'] = 'Marcar si la camara debe estar encendida';

//Tarea programada
$string['tarea_programada'] = 'Tarea Programada de Asistbot2';

//Evaluación de asistencias
$string['evaluatetolerance'] = 'Evaluar tiempo de tolerancia';
$string['evaluatetolerance_help'] = 'Marcar si el curso evaluará el tiempo de llegada de los alumnos';

$string['tolerancetime'] = 'Tiempo de tolerancia de retraso';
$string['tolerancetime_help'] = 'Tiempo de gracia para que el alumno no pierda el presente';
$string['tolerancetimelimit'] = 'De evaluarse, el tiempo de tolerancia no puede ser mayor a 25 minutos ni inferior a 1 minuto.';
$string['starttime'] = 'Hora de inicio';
$string['starttime_help'] = 'Hora de inicio de la clase';
$string['endtime'] = 'Hora de finalización';
$string['endtime_help'] = 'Hora de fin de la clase';
$string['endtimegreater'] = 'La hora de finalización debe ser mayor que la hora de inicio';

$string['executionhour'] = 'Hora de ejecución';
$string['executionhour_help'] = 'Hora en la cual la tarea programada deba ejecutarse (0-23).';
$string['invalidexecutionhour'] = 'La hora debe estar entre 0 y 23.';

// Duración de la clase
$string['classlength'] = 'Duración de clase';
$string['classlength_help'] = 'Ingrese la duración de la clase en minutos.';
$string['invalidclasslength'] = 'Duración inválida. Ingrese un valor representativo de 1 a 240 minutos.';
