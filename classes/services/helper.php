<?php

namespace mod_jirbis\services;

class helper
{
    public static function remote_file_exists($url): bool
    {
        return true;
// Работает не стабильно, сервер через раз возвращает коды 503, 400 или 200
//        $url = str_replace(" ", '%20', $url);
//
//        return (bool) preg_match('~HTTP/1\.\d\s+200\s+OK~', @current(get_headers($url)));
    }
}