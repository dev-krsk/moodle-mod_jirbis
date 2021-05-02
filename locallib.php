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
 * @package   mod_jirbis
 * @copyright 2021, Yuriy Yurinskiy <moodle@krsk.dev>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 **/

defined('MOODLE_INTERNAL') || die();

require_once('includes/BaseJsonRpcClient.php');

function jirbis_dump(...$args)
{
    foreach ($args as $arg) {
        echo '<pre>' . print_r($arg, 1) . '</pre>' . PHP_EOL;
    }
}

function jirbis_url_provider()
{
    $url = get_config('jirbis', 'server_url');

    if (mb_substr($url, -1) === '/') {
        $url = mb_substr($url, 0, -1);
    }

    return "$url/components/com_irbis/ajax_provider.php?task=rpc&class=jwrapper";
}

const JIRBIS_NAME = 200;
const JIRBIS_IZDAT = 210;
const JIRBIS_STR = 215;
const JIRBIS_AUTHOR = 700;
const JIRBIS_AUTHORS = 701;
const JIRBIS_URL = 951;

function jirbis_load()
{
    $config = get_config('jirbis');

    $client =
            new BaseJsonRpcClient('http://biblioteka.sibsau.ru//jirbis2//components//com_irbis//ajax_provider.php?task=rpc&class=jwrapper');
    $client->BeginBatch();
    $results = $client->rpc_auth('libadmin', 'vfhwbgfy152');

    $profile = array(
            'obzor' => array('format' => '@kn_h', 'type' => 'bo'),
    );

    $cnt = $client->find_jrecords('IBIS', '(<.>A=ДОРРЕР$<.>)*((<.>V=FT<.>+<.>V=EXT<.>))', '', 1, 10, $profile);
    //$cnt = $client->FindRecords('IBIS', '(<.>A=ДОРРЕР$<.>)*((<.>V=FT<.>+<.>V=EXT<.>))', '', 1,10);

    if (!$client->CommitBatch()) {
        echo 'Ошибка при выполнении запроса. Возможно, неправильный ответ от сервера, или отсутствие связи с сервером.';
    }

    // Поскольку выполнение запросов происходит не сразу, обработка ошибок должна быть здесь.
    if (!empty($cnt->Error)) {
        echo "Тип ошибки: {$cnt->Error['message']} Расшифровка {$cnt->Error['data']} Код ошибки: {$cnt->Error['code']}";
    }

    foreach ($cnt->Result as $item) {
        jirbis_dump($item);
        if (!array_key_exists('Content', $item)) {
            continue;
        } else {
            $item = $item['Content'];
        }

        jirbis_dump(jirbis_get_info($item, JIRBIS_AUTHOR));
        foreach (jirbis_get_info($item, JIRBIS_AUTHOR) as $t) {
            jirbis_format_author($t);
        }

        $tmp = [
                'author' => jirbis_array_format(jirbis_get_info($item, JIRBIS_AUTHOR), 'jirbis_format_author'),
                'authors' => jirbis_array_format(jirbis_get_info($item, JIRBIS_AUTHORS), 'jirbis_format_author'),
                'izdat' => jirbis_array_format(jirbis_get_info($item, JIRBIS_IZDAT)),
                'name' => jirbis_array_format(jirbis_get_info($item, JIRBIS_NAME)),
                'url' => jirbis_array_format(jirbis_get_info($item, JIRBIS_URL)),
        ];
        jirbis_dump($tmp);

    }
}

function jirbis_get_info(array $record, int $format): array
{
    if (isset($record[$format])) {
        return $record[$format];
    }

    return [];
}

function jirbis_array_format(?array $values, $callback = 'jirbis_format'): array
{
    if (is_null($values)) {
        return [];
    }

    $result = [];

    foreach ($values as $value) {
        $result[] = $callback($value);
    }

    return $result;
}

function jirbis_format(?string $value): ?string
{
    if (is_null($value)) {
        return null;
    }

    $value = str_replace('^A', '', $value);
    $value = str_replace('^B', ', ', $value);
    $value = str_replace('^C', ' : ', $value);
    $value = str_replace('^D', ', ', $value);
    $value = str_replace('^E', ' : ', $value);
    $value = str_replace('^F', ' / ', $value);
    $value = str_replace('^G', ' ; ', $value);
    $value = str_replace('^I', '', $value);

    return $value;
}

function jirbis_format_author(string $value, bool $full = false, $role = false)
{
    preg_match('/\^A(.+?[^\^])(?:\^|$)/', $value, $matches);

    switch (count($matches)) {
        case 0:
            $fam = '';
            jirbis_dump('Warning!!! Не найдено ни одного значения');
            break;
        case 2:
            $fam = $matches[1];
            break;
        default:
            $fam = $matches[1];
            jirbis_dump('Warning!!! Найдено несколько значений');
            break;
    }

    jirbis_dump($fam);

    preg_match('/\^B(.+?[^\^])(?:\^|$)/', $value, $matches);

    switch (count($matches)) {
        case 0:
            $io = '';
            jirbis_dump('Warning!!! Не найдено ни одного значения');
            break;
        case 2:
            $io = $matches[1];
            break;
        default:
            $io = $matches[1];
            jirbis_dump('Warning!!! Найдено несколько значений');
            break;
    }

    jirbis_dump($io);

    preg_match('/\^G(.+?[^\^])(?:\^|$)/', $value, $matches);

    switch (count($matches)) {
        case 0:
            $io_full = '';
            jirbis_dump('Warning!!! Не найдено ни одного значения');
            break;
        case 2:
            $io_full = $matches[1];
            break;
        default:
            $io_full = $matches[1];
            jirbis_dump('Warning!!! Найдено несколько значений');
            break;
    }

    jirbis_dump($io_full);

    preg_match('/\^C(.+?[^\^])(?:\^|$)/', $value, $matches);

    switch (count($matches)) {
        case 0:
            $rang = '';
            jirbis_dump('Warning!!! Не найдено ни одного значения');
            break;
        case 2:
            $rang = $matches[1];
            break;
        default:
            $rang = $matches[1];
            jirbis_dump('Warning!!! Найдено несколько значений');
            break;
    }

    jirbis_dump($rang);

    preg_match('/\^F(.+?[^\^])(?:\^|$)/', $value, $matches);

    switch (count($matches)) {
        case 0:
            $dr = '';
            jirbis_dump('Warning!!! Не найдено ни одного значения');
            break;
        case 2:
            $dr = $matches[1];
            break;
        default:
            $dr = $matches[1];
            jirbis_dump('Warning!!! Найдено несколько значений');
            break;
    }

    jirbis_dump($dr);

    return $fam . ', ' . $io;
}
