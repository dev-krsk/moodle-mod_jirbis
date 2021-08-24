<?php
// This file is part of Moodle - http://moodle.org/
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
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * @package   mod_jirbis
 * @copyright 2021, Yuriy Yurinskiy <moodle@krsk.dev>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

/**
 * Add page instance.
 * @param stdClass $moduleinstance
 * @param mod_jirbis_mod_form $mform
 * @return int new page instance id
 */
function jirbis_add_instance($moduleinstance, $mform = null) {
    global $CFG, $DB;

    $moduleinstance->timecreated = time();
    $moduleinstance->name = mb_substr($moduleinstance->content_name, 0, 255);

    $id = $DB->insert_record('jirbis', $moduleinstance);

    return $id;
}

/**
 * Updates an instance of the mod_jirbis in the database.
 *
 * Given an object containing all the necessary data (defined in mod_form.php),
 * this function will update an existing instance with new data.
 *
 * @param object $moduleinstance An object from the form in mod_form.php.
 * @param mod_jirbis_mod_form $mform The form.
 * @return bool True if successful, false otherwise.
 */
function jirbis_update_instance($moduleinstance, $mform = null) {
    global $DB;

    $moduleinstance->timemodified = time();
    $moduleinstance->name = mb_substr($moduleinstance->content_name, 0, 255);
    $moduleinstance->id = $moduleinstance->instance;

    return $DB->update_record('jirbis', $moduleinstance);
}

/**
 * Removes an instance of the mod_jirbis from the database.
 *
 * @param int $id Id of the module instance.
 * @return bool True if successful, false on failure.
 */
function jirbis_delete_instance($id) {
    global $DB;

    $exists = $DB->get_record('jirbis', array('id' => $id));
    if (!$exists) {
        return false;
    }

    $DB->delete_records('jirbis', array('id' => $id));

    return true;
}

