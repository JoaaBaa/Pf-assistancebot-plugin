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
//Porcentaje acistencias y pedido de cámara
$string['attendancepercentage'] = 'Tiempo de asistencia';
$string['attendancepercentage_help'] = 'Ingrese en minutos el valor mínimo de asistencia Requerido para el curso.';
$string['invalidattendancepercentage'] = 'Rango de asistencia inválido. Ingrese un número válido.';
$string['attendancepercentagerange'] = 'El rango de asistencia debe estar entre 75% y 100%.';

$string['requirecamera'] = 'Requiere cámara';
$string['requirecamera_help'] = 'Marcar si la camara debe estar encendida';

//Tarea programada
$string['tarea_programada'] = 'Tarea Programada de Asistbot2';