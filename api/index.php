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
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle. If not, see <http://www.gnu.org/licenses/>.

/**
 * API for module jirbis2
 *
 * @package   mod_jirbis
 * @copyright 2021, Yuriy Yurinskiy <moodle@krsk.dev>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use mod_jirbis\services\api;

require_once('../../../config.php');

$courseid = required_param('id', PARAM_INT); // course id

$author = optional_param(api::QUERY_AUTHOR, null, PARAM_TEXT);
$title = optional_param(api::QUERY_TITLE, null, PARAM_TEXT);
$key = optional_param(api::QUERY_KEY, null, PARAM_TEXT);
$year = optional_param(api::QUERY_YEAR, null, PARAM_TEXT);

$base = optional_param('base', 'IBIS', PARAM_TEXT);
$page = optional_param('page', 1, PARAM_INT);
$limit = optional_param('limit', 10, PARAM_INT);

header('Content-Type: application/json');

if (!$course = $DB->get_record('course', array('id' => $courseid))) {
    http_response_code(404);

    echo json_encode([
        'error' => get_string('invalidcourseid', 'error')
    ], true);
    return;
}

require_login($course);
$context = context_course::instance($course->id);

if (!has_capability('mod/jirbis:addinstance', $context)) {
    http_response_code(404);
    echo json_encode([
        'error' => 'access denied'
    ], true);
    return;
}

try {
    $api = new api();

    $query = api::generateQuery([
        api::QUERY_AUTHOR => $author,
        api::QUERY_KEY => $key,
        api::QUERY_TITLE => $title,
        api::QUERY_YEAR => $year,
    ]);

    $data = $api->load($query, $base, $page, $limit);
} catch (moodle_exception $e) {
    http_response_code(500);

    $data = [
        'error' => $e->getMessage()
    ];
}

echo json_encode($data, 1);
