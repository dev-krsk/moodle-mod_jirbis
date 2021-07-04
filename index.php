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
 * Library of functions and constants for module jirbis2
 *
 * @package   mod_jirbis
 * @copyright 2021, Yuriy Yurinskiy <moodle@krsk.dev>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once('../../config.php');

$id = required_param('id', PARAM_INT); // course id
$query = optional_param('query', 'доррер', PARAM_TEXT);

$course = $DB->get_record('course', array('id'=>$id), '*', MUST_EXIST);

require_course_login($course);

$PAGE->set_url('/mod/jirbis/index.php', array('id' => $course->id));
$PAGE->set_title($course->shortname);
$PAGE->set_heading($course->fullname);

echo $OUTPUT->header();

$api = new \mod_jirbis\services\api();

$api->load($query);

echo $OUTPUT->footer();
