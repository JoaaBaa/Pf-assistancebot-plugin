<?php

namespace mod_asistbot2\task;

defined('MOODLE_INTERNAL') || die();
$path_to_utils = __DIR__.'/../../utils.php';
echo $path_to_utils;
require($path_to_utils);
class print_message extends \core\task\scheduled_task {

    public function get_name() {
        return get_string('print_message', 'mod_asistbot2');
    }

    public function execute() {
        mtrace("Me estoy ejecutando\n");
        global $DB;

        $instances = $DB->get_records('asistbot2');
        foreach ($instances as $instance) {
            $course = $DB->get_record('course', array('id' => $instance->course), '*', MUST_EXIST);
            $cm = $DB->get_record('course_modules', array('instance' => $instance->id, 'course' => $course->id, 'module' => $DB->get_field('modules', 'id', array('name' => 'asistbot2'))), '*', MUST_EXIST);
            \mod_asistbot2\utils\Utils::procesarReuniones($cm->id, $instance->id, $course, $cm, $instance);
        }
    }

    public function get_run_if_component_disabled() {
        return true;
    }
}
