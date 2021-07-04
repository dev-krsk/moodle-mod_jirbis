<?php

namespace mod_jirbis\includes;

/**
 * Base Json Rpc Call
 *
 * @package    Eaze
 * @subpackage Model
 * @author     Sergeyfast
 * @link       http://www.jsonrpc.org/specification
 */
class BaseJsonRpcCall
{

    /** @var int */
    public $Id;

    /** @var string */
    public $Method;

    /** @var array */
    public $Params;

    /** @var array */
    public $Error;

    /** @var mixed */
    public $Result;

    /**
     * Has Error
     *
     * @return bool
     */
    public function HasError()
    {
        return !empty($this->Error);
    }

    /**
     * @param string $method
     * @param array $params
     * @param string $id
     */
    public function __construct($method, $params, $id)
    {
        $this->Method = $method;
        $this->Params = $params;
        $this->Id = $id;
    }

    /**
     * Get Call Data
     *
     * @param BaseJsonRpcCall $call
     * @return array
     */
    public static function GetCallData(self $call)
    {
        return array(
            'jsonrpc' => '2.0',
            'id' => $call->Id,
            'method' => $call->Method,
            'params' => $call->Params
        );
    }

    /**
     * Set Result
     *
     * @param mixed $data
     * @param bool $useObjects
     */
    public function SetResult($data, $useObjects = false)
    {
        if ($useObjects) {
            $this->Error = property_exists($data, 'error') ? $data->error : null;
            $this->Result = property_exists($data, 'result') ? $data->result : null;
        } else {
            $this->Error = isset($data['error']) ? $data['error'] : null;
            $this->Result = isset($data['result']) ? $data['result'] : null;
        }
    }

}