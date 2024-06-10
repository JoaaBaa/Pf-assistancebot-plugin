<?php
/**
 * Prints an instance of mod_asistbot2.
 *
 * @package     mod_asistbot2
 * @copyright   2024 Your Name <abich@example.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

 
require(__DIR__.'/../../config.php');
require_once(__DIR__.'/lib.php');
require_once(__DIR__.'/utils.php');

// Set the time zone to Argentina
date_default_timezone_set('America/Argentina/Buenos_Aires');

// Course module id.
$id = optional_param('id', 0, PARAM_INT);

// Activity instance id.
$a = optional_param('a', 0, PARAM_INT);

if ($id) {
    $cm = get_coursemodule_from_id('asistbot2', $id, 0, false, MUST_EXIST);
    $course = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
    $moduleinstance = $DB->get_record('asistbot2', array('id' => $cm->instance), '*', MUST_EXIST);
} else {
    $moduleinstance = $DB->get_record('asistbot2', array('id' => $a), '*', MUST_EXIST);
    $course = $DB->get_record('course', array('id' => $moduleinstance->course), '*', MUST_EXIST);
    $cm = get_coursemodule_from_instance('asistbot2', $moduleinstance->id, $course->id, false, MUST_EXIST);
}

require_login($course, true, $cm);

$modulecontext = context_module::instance($cm->id);

$PAGE->set_url('/mod/asistbot2/view.php', array('id' => $cm->id));
$PAGE->set_title(format_string($moduleinstance->name));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($modulecontext);

echo $OUTPUT->header();

mod_asistbot2\utils\Utils::procesarReuniones($id, $a, $course, $cm, $moduleinstance);

echo "las reuniones se procesaron correctamente";

echo $OUTPUT->footer();




