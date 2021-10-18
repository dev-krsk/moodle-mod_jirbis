<?php

namespace mod_jirbis\services;

use mod_jirbis\includes\BaseJsonRpcClient;
use moodle_exception;

class api
{
    const QUERY_TITLE = 'title';
    const QUERY_AUTHOR = 'author';
    const QUERY_KEY = 'key';
    const QUERY_YEAR = 'year';

    protected $client;

    protected $profile;

    public function __construct()
    {
        $this->client = new BaseJsonRpcClient($this->getUrlProvider());
        $this->client->BeginBatch();
        $this->client->rpc_auth(
            get_config('jirbis', 'server_lg'),
            get_config('jirbis', 'server_pw')
        );

        $this->profile = array(
            'name' => array('format' => '@brief', 'type' => 'bo'),
        );
    }

    /**
     * Возвращает количество записей по запросу.
     *
     * @param string $base
     * @return mixed
     */
    public function count(string $query, string $base = 'IBIS')
    {
        return $this->client->req_full_count($base, $query);
    }

    /**
     * Получает поля и характеристики записи в виде сложного массива, соответствующего по своей структуре классу jrocord.php
     *
     * @param string $base
     * @return mixed
     */
    public function find(string $query, string $base = 'IBIS', int $start = 1, int $limit = 10)
    {
        return $this->client->find_jrecords($base, $query, '', $start, $limit, $this->profile);
        //return $this->client->FindRecords($base, $query, '', $start, $limit);
    }

    public function generateQuery(array $params = []): string
    {
        $query = '((<.>V=FT<.>+<.>V=EXT<.>))';

        while (\count($params) > 0) {
            $value = mb_strtoupper(end($params));
            $key = key($params);
            unset($params[$key]);

            switch ($key) {
                case self::QUERY_AUTHOR:
                    $query .= "*(<.>A=$value$<.>)";
                    break;
                case self::QUERY_TITLE:
                    $query .= "*(<.>T=$value$<.>)";
                    break;
                case self::QUERY_KEY:
                    $query .= "*(<.>K=$value$<.>)";
                    break;
                case self::QUERY_YEAR:
                    $query .= "*(<.>G=$value$<.>)";
                    break;
            }
        }

        return $query;
    }

    public function load(string $query, string $base = 'IBIS', int $page = 1, int $limit = 10)
    {
        $query = mb_strtoupper($query);
        $query = "(<.>A=$query$<.>)*";

        $cnt = $this->count($query, $base);

        $start = ($page - 1) * $limit + 1;

        $result = $this->find(
            $query,
            $base,
            $start,
            $limit
        );

        if (!$this->client->CommitBatch()) {
            throw new moodle_exception(
                'error:error_request_to_server',
                'mod_jirbis',
                null,
                null,
                print_r($cnt, 1)
            );
        }

        // Поскольку выполнение запросов происходит не сразу, обработка ошибок должна быть здесь.
        if (!empty($cnt->Error)) {
            throw new moodle_exception(
                'error:error_request_to_server_other',
                'mod_jirbis',
                null,
                null,
                print_r($cnt, 1)
            );
        }

        if ($cnt->Result != 0 && $page > ceil($cnt->Result / $limit)) {
            throw new moodle_exception('error:not_found_page', 'mod_jirbis');
        }

        $data = [];

        foreach ($result->Result as $item) {
            if (!array_key_exists('formating', $item)) {
                continue;
            } else {
                $formating = $item['formating'];
            }

            if (!array_key_exists('Content', $item)) {
                continue;
            } else {
                $content = $item['Content'];
            }

            $data[] = [
                'name' => $formating['name']['value'],
                'url' => profile::get_info($content, profile::JIRBIS_URL),
            ];

        }


        return [
            'current_page' => $page,
            'max_page' => ceil($cnt->Result / $limit),
            'limit' => $limit,
            'count' => $cnt->Result,
            'data' => $data
        ];
    }

    public function getUrlProvider(): string
    {
        $url = get_config('jirbis', 'server_url');

        if (mb_substr($url, -1) === '/') {
            $url = mb_substr($url, 0, -1);
        }

        return "$url/components/com_irbis/ajax_provider.php?task=rpc&class=jwrapper";
    }

    public function jirbis_dump(...$args)
    {
        foreach ($args as $arg) {
            echo '<pre>' . print_r($arg, 1) . '</pre>' . PHP_EOL;
        }
    }
}