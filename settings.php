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
 * Plugin administration pages are defined here.
 *
 * @package     mod_asistbot2
 * @category    admin
 * @copyright   2024 Your Name <abich@example.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

if ($hassiteconfig) {
    $settings = new admin_settingpage('mod_asistbot2_settings', new lang_string('pluginname', 'mod_asistbot2'));

    // phpcs:ignore Generic.CodeAnalysis.EmptyStatement.DetectedIf
    if ($ADMIN->fulltree) {
        // TODO: Define actual plugin settings page and add it to the tree - {@link https://docs.moodle.org/dev/Admin_settings}.
        // Add the attendance percentage setting.
    $settings->add(new admin_setting_configtext('mod_asistbot2/attendancepercentage',
        get_string('attendancepercentage', 'mod_asistbot2'),
        get_string('attendancepercentage_help', 'mod_asistbot2'), '', PARAM_INT));

    // Add the require camera setting.
    $settings->add(new admin_setting_configcheckbox('mod_asistbot2/requirecamera',
        get_string('requirecamera', 'mod_asistbot2'),
        get_string('requirecamera_help', 'mod_asistbot2'), 0));
    }
}
