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
 * This file contains the forms to create and edit an instance of this module
 *
 * @package   mod_jirbis
 * @copyright 2021, Yuriy Yurinskiy <moodle@krsk.dev>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

require_once($CFG->dirroot . '/course/moodleform_mod.php');

/**
 * JIRBIS2 settings form.
 *
 * @package   mod_jirbis
 * @copyright 2021, Yuriy Yurinskiy <moodle@krsk.dev>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_jirbis_mod_form extends moodleform_mod {
    function definition() {
        global $PAGE, $COURSE;

        $PAGE->force_settings_menu();

        $mform = $this->_form;

        $config = get_config('jirbis');

        $PAGE->requires->js_call_amd('mod_jirbis/modal_search_handle', 'init', [$COURSE->id, get_config('jirbis', 'debug')]);

        $mform->addElement('header', 'general', get_string('general', 'form'));

        $mform->addElement('button', 'modal_search_show', 'Открыть модалку');

        $mform->addElement('text', 'content_name', 'Выбранный ресурс', ['style' => 'width:100%', 'readonly' => true]);
        $mform->setType('content_name', PARAM_TEXT);
        $mform->addRule('content_name', 'Чтобы заполнить данное поле, необходимо выбрать ресурс в модальном окне', 'required', null, 'client');

        $mform->addElement('text', 'content_url', 'URL', ['style' => 'width:100%', 'readonly' => true]);
        $mform->setType('content_url', PARAM_TEXT);
        $mform->addRule('content_url', 'Чтобы заполнить данное поле, необходимо выбрать ресурс в модальном окне', 'required', null, 'client');

        //-------------------------------------------------------
        $this->standard_coursemodule_elements();
        //-------------------------------------------------------
        $this->add_action_buttons();
    }
}

