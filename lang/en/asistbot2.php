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
$string['ison'] = 'Plugin On';
$string['ison_help'] = 'Check this box if bot should be processing attendances.';

//Porcentaje acistencias y pedido de cámara
$string['attendancepercentage'] = 'Attendance percentage';
$string['attendancepercentage_help'] = 'Enter minimum required attendance percentage.';
$string['invalidattendancepercentage'] = 'Invalid attendance percentage. Please enter a valid one from 75 to 95.';
$string['attendancepercentagerange'] = 'Attendance percentage must be between 75% and 95%.';

$string['requirecamera'] = 'Require Camera';
$string['requirecamera_help'] = 'Check this box if students are required to have their cameras on.';

//tarea programada
$string['tarea_programada'] = 'Programmed  task in Asistbot2';
$string['print_message'] = 'Print message';

//Evaluación de asistencias
$string['evaluatetolerance'] = 'Evaluate tolerace time';
$string['evaluatetolerance_help'] = 'Check this box if this course evaluates students arrival time';

$string['tolerancetime'] = 'Delay tolerance time';
$string['tolerancetime_help'] = 'Time frame where the student may arrive while keeping an attendance check';
$string['tolerancetimelimit'] = 'Max is 25 minutes, Minimum is 1 minute.';
$string['starttime'] = 'Class start time';
$string['starttime_help'] = 'Beggining of class';
$string['endtime'] = 'Class end time';
$string['endtime_help'] = 'End of class';

$string['executionhour'] = 'Execution Hour';
$string['executionhour_help'] = 'The hour of the day when the task should execute (0-23).';
$string['invalidexecutionhour'] = 'Execution hour must be between 0 and 23.';

// Duración de la clase
$string['classlength'] = 'Class length';
$string['classlength_help'] = 'Enter the duration of the class in minutes.';
$string['invalidclasslength'] = 'Invalid class length. The duration must be between 1 and 240 minutes.';
