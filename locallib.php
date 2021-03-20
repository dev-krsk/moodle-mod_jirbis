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
 * Local library file for jirbis2. These are non-standard functions that are used only by jirbis2.
 *
 * @package   mod_jirbis2
 * @copyright 2021, Yuriy Yurinskiy <moodle@krsk.dev>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 **/

defined('MOODLE_INTERNAL') || die();

function jirbis2_url_provider() {
    $url = get_config('jirbis2', 'server_url');

    if (mb_substr($url, -1) === '/') {
        $url = mb_substr($url, 0, -1);
    }

    return "$url/components/com_irbis/ajax_provider.php?task=rpc&class=jwrapper";
}