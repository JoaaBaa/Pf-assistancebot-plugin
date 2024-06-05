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

//Porcentaje acistencias y pedido de cámara
$string['attendancepercentage'] = 'Attendance time';
$string['attendancepercentage_help'] = 'Enter in minutes the minimum required time of attendance.';
$string['invalidattendancepercentage'] = 'Invalid attendance time. Please enter a valid number.';
$string['attendancepercentagerange'] = 'Attendance percentage must be between 75% and 100%.';

$string['requirecamera'] = 'Require Camera';
$string['requirecamera_help'] = 'Check this box if students are required to have their cameras on.';

//tarea programada
$string['tarea_programada'] = 'Programmed  task in Asistbot2';
$string['print_message'] = 'Print message';

$string['tolerancetime'] = 'Delay tolerance time';
$string['tolerancetime_help'] = 'Time frame where the student may arrive while keeping an attendance check';
$string['tolerancetimelimit'] = 'Max is 25 min';
$string['starttime'] = 'Class start time';
$string['starttime_help'] = 'Beggining of class';
$string['endtime'] = 'Class end time';
$string['endtime_help'] = 'End of class';

$string['executionhour'] = 'Execution Hour';
$string['executionhour_help'] = 'The hour of the day when the task should execute (0-23).';
$string['invalidexecutionhour'] = 'Execution hour must be between 0 and 23.';