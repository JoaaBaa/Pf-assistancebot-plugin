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
 * The main mod_asistbot2 configuration form.
 *
 * @package     mod_asistbot2
 * @copyright   2024 Your Name <abich@example.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/course/moodleform_mod.php');

/**
 * Module instance settings form.
 *
 * @package     mod_asistbot2
 * @copyright   2024 Your Name <abich@example.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_asistbot2_mod_form extends moodleform_mod {

    /**
     * Defines forms elements
     */
    public function definition() {
        global $CFG;

        $mform = $this->_form;

        // Adding the "general" fieldset, where all the common settings are shown.
        $mform->addElement('header', 'general', get_string('general', 'form'));

        // Adding the standard "name" field.
        $mform->addElement('text', 'name', get_string('asistbot2name', 'mod_asistbot2'), array('size' => '64'));

        if (!empty($CFG->formatstringstriptags)) {
            $mform->setType('name', PARAM_TEXT);
        } else {
            $mform->setType('name', PARAM_CLEANHTML);
        }

        $mform->addRule('name', null, 'required', null, 'client');
        $mform->addRule('name', get_string('maximumchars', '', 255), 'maxlength', 255, 'client');
        $mform->addHelpButton('name', 'asistbot2name', 'mod_asistbot2');

        // Adding the standard "intro" and "introformat" fields.
        if ($CFG->branch >= 29) {
            $this->standard_intro_elements();
        } else {
            $this->add_intro_editor();
        }

        // Adding the rest of mod_asistbot2 settings, spreading all them into this fieldset
        // ... or adding more fieldsets ('header' elements) if needed for better logic.
        $mform->addElement('static', 'label1', 'asistbot2settings', get_string('asistbot2settings', 'mod_asistbot2'));
        $mform->addElement('header', 'asistbot2fieldset', get_string('asistbot2fieldset', 'mod_asistbot2'));

        // Adding a percentage field for attendance.
        $mform->addElement('text', 'attendancepercentage', get_string('attendancepercentage', 'mod_asistbot2'), array('size' => '4'));
        $mform->setType('attendancepercentage', PARAM_INT);
        $mform->addRule('attendancepercentage', null, 'required', null, 'client');
        $mform->addHelpButton('attendancepercentage', 'attendancepercentage', 'mod_asistbot2');

        // Adding a checkbox for requiring camera.
        $mform->addElement('advcheckbox', 'requirecamera', get_string('requirecamera', 'mod_asistbot2'));
        $mform->addHelpButton('requirecamera', 'requirecamera', 'mod_asistbot2');
        
        // ESTE MÉTODO VALIDABA PORCENTAJE DE ASISTENCIA, CAMBIAMOS A TIEMPO EN MINUTOS DE  DURACION DE CLASE,
        // PERO SI NECESITAN UNA VALIDACION DE FORMULARIO ACA DEJO EL EJEMPLO. 
        //public function validation($data, $files) {
        //    $errors = parent::validation($data, $files);
    
        //    if ($data['attendancepercentage'] < 75 || $data['attendancepercentage'] > 100) {
        //        $errors['attendancepercentage'] = get_string('attendancepercentagerange', 'mod_asistbot2');
        //    }
    
        //    return $errors;
        //}

        // Add standard elements.
        $this->standard_coursemodule_elements();

        // Add standard buttons.
        $this->add_action_buttons();
    }
}
