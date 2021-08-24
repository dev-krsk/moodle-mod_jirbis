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
 * JIRBIS module version information
 *
 * @package   mod_jirbis
 * @copyright 2021, Yuriy Yurinskiy <moodle@krsk.dev>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use mod_jirbis\services\helper;

require_once('../../config.php');
$id = required_param('id', PARAM_INT);

$cm             = get_coursemodule_from_id('jirbis', $id, 0, false, MUST_EXIST);
$course         = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
$moduleinstance = $DB->get_record('jirbis', array('id' => $cm->instance), '*', MUST_EXIST);

require_login($course, false, $cm);

$modulecontext = context_module::instance($cm->id);

$event = \mod_jirbis\event\course_module_viewed::create(array(
    'objectid' => $moduleinstance->id,
    'context' => $modulecontext
));
$event->add_record_snapshot('course', $course);
$event->add_record_snapshot('jirbis', $moduleinstance);
$event->trigger();


$PAGE->set_url('/mod/page/view.php', array('id' => $cm->id));
$PAGE->set_title(format_string($moduleinstance->name));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($modulecontext);

echo $OUTPUT->header();

echo '<p>' . $moduleinstance->content_name . '</p>';
if(helper::remote_file_exists($moduleinstance->content_url)) {
    echo '<p><a href="' . $moduleinstance->content_url . '" class="btn btn-primary">Скачать</a></p>';
    echo '<p><embed src="' . $moduleinstance->content_url . '" width="100%" height="800px" /></p>';
} else {
    echo '<p class="alert alert-warning">Файл отсутвует на сервере библиотеки</p>';
}

echo $OUTPUT->footer();
