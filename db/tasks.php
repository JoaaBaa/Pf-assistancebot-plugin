<?php
// Este archivo es parte de Moodle - http://moodle.org/
//
// Moodle es software libre: puedes redistribuirlo y/o modificarlo
// bajo los términos de la Licencia Pública General de GNU publicada por
// la Free Software Foundation, ya sea la versión 3 de la Licencia, o
// (a tu elección) cualquier versión posterior.
//
// Moodle se distribuye con la esperanza de que sea útil,
// pero SIN NINGUNA GARANTÍA; sin siquiera la garantía implícita de
// COMERCIABILIDAD o APTITUD PARA UN PROPÓSITO PARTICULAR. Consulta la
// Licencia Pública General de GNU para más detalles.
//
// Deberías haber recibido una copia de la Licencia Pública General de GNU
// junto con Moodle. Si no es así, consulta <http://www.gnu.org/licenses/>.

/**
 * Configuración del horario de tareas para el plugin plugintype_pluginname.
 *
 * @package     mod_asistbot2
 * @category    upgrade
 * @copyright   2024 Your Name <abich@example.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$tasks = [
    [
        'classname' => 'mod_asistbot2\task\print_message', // Nombre de la clase que contiene la tarea programada.
        'blocking' => 0, // Indica si la tarea debe bloquear otras tareas mientras se ejecuta. 0 (no bloquea), 1 (bloquea).
        'minute' => '*/2', // Minutos en los que la tarea debe ejecutarse.
        'hour' => '*', // Horas en las que la tarea debe ejecutarse.
        'day' => '*', // Días del mes en los que la tarea debe ejecutarse. '*' para todos los días del mes.
        'month' => '*', // Meses del año en los que la tarea debe ejecutarse. '*' para todos los meses.
        'dayofweek' => '*', // Días de la semana en los que la tarea debe ejecutarse. '5' para los viernes (0 es domingo, 6 es sábado).
    ],
    // Agregar más tareas si es necesario...
];