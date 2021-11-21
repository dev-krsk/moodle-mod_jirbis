<?php

namespace mod_jirbis\services;

use cache;
use mod_jirbis\includes\BaseJsonRpcClient;
use moodle_exception;

class api
{
    const CACHE_EXP = 86400;
    const QUERY_TITLE = 'title';
    const QUERY_AUTHOR = 'author';
    const QUERY_KEY = 'key';
    const QUERY_YEAR = 'year';

    protected $client;

    protected $profile;

    protected $cache;
    protected $expCache;

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

        $this->cache = cache::make('mod_jirbis', 'cnt_result');
        $this->expCache = get_config('jirbis', 'other:cache_exp');

        if (empty($this->expCache)) {
            $this->expCache = self::CACHE_EXP;
        }
    }

    public static function generateQuery(array $params = []): string
    {
        $query = '((<.>V=FT<.>+<.>V=EXT<.>))';

        while (\count($params) > 0) {
            $value = mb_strtoupper(end($params));
            $key = key($params);
            unset($params[$key]);

            if (empty($value)) {
                continue;
            }

            switch ($key) {
                case self::QUERY_AUTHOR:
                    $query = "(<.>A=$value$<.>)*$query";
                    break;
                case self::QUERY_TITLE:
                    $query = "(<.>T=$value$<.>)*$query";
                    break;
                case self::QUERY_KEY:
                    $query = "(<.>K=$value$<.>)*$query";
                    break;
                case self::QUERY_YEAR:
                    $query = "(<.>G=$value$<.>)*$query";
                    break;
            }
        }

        return $query;
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

    public function loadCache(string  $query, string $base, int $start, int $limit, int $cnt) {
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
        if (!empty($result->Error)) {
            throw new moodle_exception(
                'error:error_request_to_server_other',
                'mod_jirbis',
                null,
                null,
                print_r($cnt, 1)
            );
        }

        $response = new \stdClass();
        $response->Cnt = $cnt;
        $response->Result = $result->Result;

        return $response;
    }

    public function loadWithoutCache(string $query, string $base, int $start, int $limit) {
        $cnt = $this->count($query, $base);

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

        $response = new \stdClass();
        $response->Cnt = $cnt->Result;
        $response->Result = $result->Result;

        return $response;
    }

    public function load(string $query, string $base = 'IBIS', int $page = 1, int $limit = 10)
    {
        $start = ($page - 1) * $limit + 1;

        if (false !== $cnt = $this->getCache($query)) {
            $response = $this->loadCache($query, $base, $start, $limit, $cnt);
        } else {
            $response = $this->loadWithoutCache($query, $base, $start, $limit);

            $this->setCache($query, $response->Cnt);
        }

        if ($start > $response->Cnt) {
            throw new moodle_exception('error:not_found_page', 'mod_jirbis');
        }

        $data = [];

        foreach ($response->Result as $item) {
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
            'max_page' => ceil($response->Cnt / $limit),
            'limit' => $limit,
            'count' => $response->Cnt,
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

    public function getCache($key) {
        $cnt = $this->cache->get($key);

        if ($cnt === false || !is_int($cnt)) {
            return false;
        }

        $exp = $this->cache->get("EXP_$key");

        if ($exp === false || round(microtime(true) - $exp) > $this->expCache) {
            return false;
        }

        return $cnt;
    }

    public function setCache($key, $value) {
        $this->cache->set_many([
            $key => $value,
            "EXP_$key" => microtime(true)
        ]);
    }

    public function jirbis_dump(...$args)
    {
        foreach ($args as $arg) {
            echo '<pre>' . print_r($arg, 1) . '</pre>' . PHP_EOL;
        }
    }
}