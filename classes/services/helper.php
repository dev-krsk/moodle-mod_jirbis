<?php

namespace mod_jirbis\services;

class helper
{
    public static function remote_file_exists($url): bool
    {
        return (bool) preg_match('~HTTP/1\.\d\s+200\s+OK~', @current(get_headers($url)));
    }
}