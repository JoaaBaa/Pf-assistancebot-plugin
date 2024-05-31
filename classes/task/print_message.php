<?php

namespace mod_asistbot2\task;

defined('MOODLE_INTERNAL') || die();

class print_message extends \core\task\scheduled_task {

    public function get_name() {
        return get_string('printmessage', 'mod_asistbot2');
    }

    public function execute() {
        mtrace("Me estoy ejecutando\n");
    }

    public function get_run_if_component_disabled() {
        return true;
    }
}