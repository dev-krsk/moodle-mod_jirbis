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
 * Resource module admin settings and defaults
 *
 * @package   mod_jirbis
 * @copyright 2021, Yuriy Yurinskiy <moodle@krsk.dev>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

if ($ADMIN->fulltree) {
    $settings->add(new admin_setting_heading(
            'jirbis/server_settings',
            get_string('config:server', 'mod_jirbis'),
            ''
    ));

    $settings->add(new admin_setting_configtext(
            'jirbis/server_url',
            get_string('config:server:url', 'mod_jirbis'),
            get_string('config:server:url_desc', 'mod_jirbis'),
            'localhost',
            PARAM_TEXT
    ));

    $settings->add(new admin_setting_configtext(
            'jirbis/server_lg',
            get_string('config:server:login', 'mod_jirbis'),
            get_string('config:server:login_desc', 'mod_jirbis'),
            '1',
            PARAM_TEXT
    ));

    $settings->add(new admin_setting_configpasswordunmask(
            'jirbis/server_pw',
            get_string('config:server:pw', 'mod_jirbis'),
            get_string('config:server:pw_desc', 'mod_jirbis'),
            '1'
    ));
}
