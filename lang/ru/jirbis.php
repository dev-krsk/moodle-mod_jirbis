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
 * Strings for component 'jirbis2', language 'ru'
 *
 * @package   mod_jirbis
 * @copyright 2021, Yuriy Yurinskiy <moodle@krsk.dev>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['modulename'] = 'J-ИРБИС 2';
$string['modulename_link'] = 'mod/jirbis/view';
$string['modulename_help'] = 'Модуль J-ИРБИС 2 включает интеграцию с библиотечной информационной системой J-ИРБИС 2.';
$string['privacy:metadata'] = 'Модуль J-ИРБИС 2 не сохраняет никаких персональных данных.';
$string['pluginadministration'] = 'Администрирование J-ИРБИС 2';
$string['pluginname'] = 'J-ИРБИС 2';

$string['config:server'] = 'Настройки сервера';
$string['config:server:url'] = 'URL';
$string['config:server:url_desc'] = 'URL сервера J-ИРБИС 2';
$string['config:server:login'] = 'Имя пользователя';
$string['config:server:login_desc'] = 'Имя пользователя для TCP/IP сервера ИРБИС';
$string['config:server:pw'] = 'Пароль';
$string['config:server:pw_desc'] = 'Пароль для TCP/IP сервера ИРБИС';
$string['config:other'] = 'Прочие настройки';
$string['config:other:cache_exp'] = 'Время жизни кеша';
$string['config:other:cache_exp_desc'] = 'Врямя жизни кеша для данных с сервера ИРБИС';
$string['config:other:debug'] = 'Режим отладки';
$string['config:other:debug_desc'] = 'Режим отладки модальнойц формы';

$string['error:not_found'] = 'Информации по вашему запросу не найдено.';
$string['error:not_found_page'] = 'Вы обращаетесь к несуществующей странице.';
$string['error:error_request_to_server'] = 'Ошибка при выполнении запроса. Возможно, неправильный ответ от сервера, или отсутствие связи с сервером.';
$string['error:error_request_to_server_other'] = 'Ошибка при выполнении запроса.';
$string['error:error_request_to_server_503'] = 'Сервер библиотеки временно не отвечает, попробуйте позже.';