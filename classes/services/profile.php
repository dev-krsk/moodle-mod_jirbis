<?php

namespace mod_jirbis\services;

class profile
{
    // Основное БО
    const JIRBIS_AUTHOR = 700; //1-й автор — заголовок описания
    const JIRBIS_AUTHORS = 701; //Другие индивидуальные авторы, НЕ входящие в заголовок описания
    const JIRBIS_NAME = 200; //Заглавие и сведения об ответственности
    const JIRBIS_IZDAT = 215; //Сведения об издании
    const JIRBIS_OUT_DATA = 210; //Выходные данные


    // ЭД общие для всех видов описания  (РЛ)
    const JIRBIS_URL = 951; //Ссылка - внешний объект

    const JIRBIS_NAME_2 = 461;

    /**
     * @param array $record
     * @param int $key
     * @param string $callback
     * @param bool $ifOneThenReturnOne
     * @return array|string
     */
    public static function get_info(array $record, int $key, string $callback = 'format', bool $ifOneThenReturnOne = true)
    {
        if (isset($record[$key])) {
            $result = self::array_format($record[$key], $callback);

            if ($ifOneThenReturnOne && count($result) == 1) {
                return $result[array_key_first($result)];
            }

            return $result;
        }

        return [];
    }

    protected static function array_format(?array $values, $callback = 'format'): array
    {
        if (is_null($values)) {
            return [];
        }

        $result = [];

        foreach ($values as $value) {
            $result[] = self::$callback($value);
        }

        return $result;
    }

    public static function format(?string $value): ?string
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
        $value = str_replace('^V', '', $value);

        return $value;
    }

    protected static function format_author(?string $value): ?string
    {
        if (is_null($value)) {
            return null;
        }

        preg_match('/\^A(.+?[^\^])(?:\^|$)/', $value, $matches);

        switch (count($matches)) {
            case 0:
                $fam = '';
                break;
            case 2:
                $fam = $matches[1];
                break;
            default:
                $fam = $matches[1];
                break;
        }

        preg_match('/\^B(.+?[^\^])(?:\^|$)/', $value, $matches);

        switch (count($matches)) {
            case 0:
                $io = '';
                break;
            case 2:
                $io = $matches[1];
                break;
            default:
                $io = $matches[1];
                break;
        }

        preg_match('/\^G(.+?[^\^])(?:\^|$)/', $value, $matches);

        switch (count($matches)) {
            case 0:
                $io_full = '';
                break;
            case 2:
                $io_full = $matches[1];
                break;
            default:
                $io_full = $matches[1];
                break;
        }

        preg_match('/\^C(.+?[^\^])(?:\^|$)/', $value, $matches);

        switch (count($matches)) {
            case 0:
                $rang = '';
                break;
            case 2:
                $rang = $matches[1];
                break;
            default:
                $rang = $matches[1];
                break;
        }

        preg_match('/\^F(.+?[^\^])(?:\^|$)/', $value, $matches);

        switch (count($matches)) {
            case 0:
                $dr = '';
                break;
            case 2:
                $dr = $matches[1];
                break;
            default:
                $dr = $matches[1];
                break;
        }

        return $fam . ', ' . $io;
    }
}