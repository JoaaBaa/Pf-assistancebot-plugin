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
 * Library of interface functions and constants.
 *
 * @package     mod_asistbot2
 * @copyright   2024 Your Name <abich@example.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Return if the plugin supports $feature.
 *
 * @param string $feature Constant representing the feature.
 * @return true | null True if the feature is supported, null otherwise.
 */
function asistbot2_supports($feature) {
    switch ($feature) {
        case FEATURE_MOD_INTRO:
            return true;
        default:
            return null;
    }
}

/**
 * Saves a new instance of the mod_asistbot2 into the database.
 *
 * Given an object containing all the necessary data, (defined by the form
 * in mod_form.php) this function will create a new instance and return the id
 * number of the instance.
 *
 * @param object $moduleinstance An object from the form.
 * @param mod_asistbot2_mod_form $mform The form.
 * @return int The id of the newly inserted record.
 */
function asistbot2_add_instance($moduleinstance, $mform = null) {
    global $DB;

    $moduleinstance->timecreated = time();
    $moduleinstance-> ison;
    $moduleinstance-> evaluatetolerance;
    $moduleinstance-> attendancepercentage;
    $moduleinstance-> requirecamera;
    $moduleinstance-> tolerancetime;
    $moduleinstance-> classlength;
    $moduleinstance-> starttime;
    $moduleinstance-> endtime;
    $moduleinstance-> executionhour;
    $id = $DB->insert_record('asistbot2', $moduleinstance);

    return $id;
}

/**
 * Updates an instance of the mod_asistbot2 in the database.
 *
 * Given an object containing all the necessary data (defined in mod_form.php),
 * this function will update an existing instance with new data.
 *
 * @param object $moduleinstance An object from the form in mod_form.php.
 * @param mod_asistbot2_mod_form $mform The form.
 * @return bool True if successful, false otherwise.
 */
function asistbot2_update_instance($moduleinstance, $mform = null) {
    global $DB;

    $moduleinstance->timemodified = time();
    $moduleinstance->id = $moduleinstance->instance;

    return $DB->update_record('asistbot2', $moduleinstance);
}

/**
 * Removes an instance of the mod_asistbot2 from the database.
 *
 * @param int $id Id of the module instance.
 * @return bool True if successful, false on failure.
 */
function asistbot2_delete_instance($id) {
    global $DB;

    $exists = $DB->get_record('asistbot2', array('id' => $id));
    if (!$exists) {
        return false;
    }

    $DB->delete_records('asistbot2', array('id' => $id));

    return true;
}

/**
 * Extends the global navigation tree by adding mod_asistbot2 nodes if there is a relevant content.
 *
 * This can be called by an AJAX request so do not rely on $PAGE as it might not be set up properly.
 *
 * @param navigation_node $asistbot2node An object representing the navigation tree node.
 * @param stdClass $course
 * @param stdClass $module
 * @param cm_info $cm
 */
function asistbot2_extend_navigation($asistbot2node, $course, $module, $cm) {
}

/**
 * Extends the settings navigation with the mod_asistbot2 settings.
 *
 * This function is called when the context for the page is a mod_asistbot2 module.
 * This is not called by AJAX so it is safe to rely on the $PAGE.
 *
 * @param settings_navigation $settingsnav {@see settings_navigation}
 * @param navigation_node $asistbot2node {@see navigation_node}
 */
function asistbot2_extend_settings_navigation($settingsnav, $asistbot2node = null) {
}
