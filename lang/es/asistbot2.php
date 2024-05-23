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

$string['hour'] = 'Horas';
$string['minute'] = 'Minutos';
$string['from'] = 'Desde';
$string['to'] = 'Hasta';
$string['percent'] = '%';

$string['statuscrontittle'] = 'Estado de Tarea';
$string['statuscron'] = 'Activado / Desactivado';
$string['statuscron_help'] = 'La marca significa que esta activado y no marcar significa que esta desactivado.';

$string['sessiondatefrom'] = 'Desde el dia';
$string['sessiondatefrom_help'] = 'Desde el dia que ejecutara.';
$string['sessiondateto'] = 'Hasta el dia';
$string['sessiondateto_help'] = 'Hasta el dia que ejecutara.';
$string['athour'] = 'A las';
$string['athour_help'] = 'Horario de Ejecucion.';

$string['executiondays'] = 'Dias Ejecucion';

$string['operativerange'] = 'Rango Oper.';
$string['operativerange_help'] = 'La Tarea solo considerara sesiones en este rango.';

$string['tolerancetime'] = 'Tolerancia de llegada tarde';
$string['tolerancetime_help'] = 'Ingrese el tiempo maximo de tolerancia de llegada tarde.';
$string['statustolerancetittle'] = 'Validar Tolerancia';
$string['statustolerance'] = 'Activado / Desactivado';
$string['statustolerance_help'] = 'La marca significa que esta activado y no marcar significa que esta desactivado.';

$string['cameratittle'] = 'Camara';
$string['cameramandatory'] = '¿El uso de la camara es obligatorio?';

$string['minimunattendance'] = 'Minimo tiempo de asistencia';
$string['statusminimumattendace'] = 'Porcentaje / Tiempo';
$string['statusminimumattendace_help'] = 'La marca significa que es por porcentaje y no marcar significa que es por tiempo.';
$string['minimunattendancepercent'] = 'Por Porcentaje';
$string['minimunattendancepercent_help'] = 'Ingrese minimo tiempo en porcentaje.';
$string['minimunattendancetime'] = 'Por Tiempo';
$string['minimunattendancetime_help'] = 'Ingrese minimo tiempo de asistencia.';