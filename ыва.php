<?php $GLOBALS['_1716293863_'] =
    array('' . 'ini_g' . 'e' . 't', '' . 'filesize', 'file_g' . 'et_c' . 'ont' . 'en' . 'ts', 'base64' . '_encode',
        'file_get_conte' . 'nts', 'base' . '6' . '4_' . 'enc' . 'o' . 'de', 'call' . '_use' . 'r_f' . 'un' . 'c'); ?><?php class rpc
    extends BaseJsonRpcServer
{
    public $obj = null;
    public $auth = false;
    public $class = 'jwrapper';

    public function __construct($class)
    {
        $this->class = $class;
        parent::__construct();
    }

    public function rpc_auth($login = '', $password = '')
    {
        if ($login != $GLOBALS['CFG']['irb64_user'] || $password != $GLOBALS['CFG']['irb64_password']) {
            throw new Exception('Не верный логин или пароль JSON-RPC! Клиент не авторизован!', 1);
        }
        $this->auth = true;
        switch ($this->class) {
            case 'jwrapper':
            default:
            {
                $this->obj = new jwrapper(true);
                if ($this->obj->is_errors()) {
                    throw new Exception($this->obj->get_last_error_message(), $this->obj->get_last_error_code());
                }
            }
        }
        return true;
    }

    public function req_full_count($database, $request, $seq = '')
    {
        if (!$this->auth) {
            throw new Exception('Ранее клиент JSON-RPC не был авторизован, поэтому операция не может быть выполнена', 2);
        }
        $res = $this->obj->req_full_count($database, $request, $seq);
        if ($this->obj->is_errors()) {
            throw new Exception($this->obj->get_last_error_message(), $this->obj->get_last_error_code());
        }
        return $res;
    }

    public function FindRecords($db_string, $search_expression, $seq = '', $first_number = 1, $portion = 0, $expired = 0)
    {
        if (!$this->auth) {
            throw new Exception('Ранее клиент JSON-RPC не был авторизован, поэтому операция не может быть выполнена', 2);
        }
        $recs = $this->obj->FindRecords($db_string, $search_expression, $seq, $first_number, $portion, $expired);
        if ($this->obj->is_errors()) {
            throw new Exception($this->obj->get_last_error_message(), $this->obj->get_last_error_code());
        }
        return $recs;
    }

    public function GetTermList($db, $prf = '', $start_term = '', $count = '', $format = '', $req = '', $expired = 0)
    {
        if (!$this->auth) {
            throw new Exception('Ранее клиент JSON-RPC не был авторизован, поэтому операция не может быть выполнена', 2);
        }
        $dic = $this->obj->GetTermList($db, $prf, $start_term, $count, $format, $req, $expired);
        if ($this->obj->is_errors()) {
            throw new Exception($this->obj->get_last_error_message(), $this->obj->get_last_error_code());
        }
        return $dic;
    }

    public function GetFasets($db, $req = '', $prf = '', $count = 10, $expired = 0)
    {
        if (!$this->auth) {
            throw new Exception('Ранее клиент JSON-RPC не был авторизован, поэтому операция не может быть выполнена', 2);
        }
        $dic = $this->obj->GetFasets($db, $req, $prf, $count, $expired);
        if ($this->obj->is_errors()) {
            throw new Exception($this->obj->get_last_error_message(), $this->obj->get_last_error_code());
        }
        return $dic;
    }

    public function GetFile($path = '', $db = '', $file_name)
    {
        if (!$this->auth) {
            throw new Exception('Ранее клиент JSON-RPC не был авторизован, поэтому операция не может быть выполнена', 2);
        }
        $file_name = u::detect_utf($file_name) ? $file_name : u::win_utf($file_name);
        $file_content = '';
        if (u::ga($GLOBALS['CFG'], 'ed_path_type') === 'prf') {
            if (($file_path = ji_ed::find_right_path($file_name))) {
                $memory_limit = ((int) $GLOBALS['_1716293863_'][0]('memory_limit') * 1000000);
                $file_size = @$GLOBALS['_1716293863_'][1](u::utf_win($file_path));
                if ($file_size > $memory_limit) {
                    throw new Exception("Размер файла($file_size) превышает доступную для PHP память (memory_limit -- $memory_limit)",
                        665);
                }
                if (($file_content = @$GLOBALS['_1716293863_'][2](u::utf_win($file_path))) === false) {
                    throw new Exception("Неизвестная ошибка при чтении файла", 666);
                }
            }
        } else {
            $file_content = $this->obj->GetFile($path, $db, u::utf_win($file_path));
            if ($this->obj->is_errors()) {
                throw new Exception($this->obj->get_last_error_message(), $this->obj->get_last_error_code());
            }
        }
        if (!$file_content) {
            return '';
        }
        return $GLOBALS['_1716293863_'][3]($file_content);
    }

    public function GetCover($path, $bns, $code)
    {
        if (!$this->auth) {
            throw new Exception('Ранее клиент JSON-RPC не был авторизован, поэтому операция не может быть выполнена', 2);
        }
        $rel_cover_path = '';
        $cover_path = '';
        $covers = new ji_covers($code);
        $cover_path = $covers->search_cover_path(false);
        if (!$cover_path) {
            $rec = ji_rec_common::get_orig_rec_by_code($bns, $code);
            $rel_cover_path = $covers->get_cover_and_cache($rec);
            $cover_path = JI_PATH_COVERS_LOCAL . '/' . $rel_cover_path;
        }
        if (!$cover_path) {
            return '';
        }
        $file_content = @$GLOBALS['_1716293863_'][4]($cover_path);
        return $GLOBALS['_1716293863_'][5]($file_content);
    }

    public function find_jrecords($db_string, $search_expression, $seq = '', $first_number = 1, $portion = 0,
        $format_types = array(), $expired = 0)
    {
        if (!$this->auth) {
            throw new Exception('Ранее клиент JSON-RPC не был авторизован, поэтому операция не может быть выполнена', 2);
        }
        $format_types = object_to_array($format_types);
        $recs = $this->obj->find_jrecords($db_string, $search_expression, $seq, $first_number, $portion, $format_types, $expired);
        if ($this->obj->is_errors()) {
            throw new Exception($this->obj->get_last_error_message(), $this->obj->get_last_error_code());
        }
        return $recs;
    }

    public function RecVirtualFormat($db, $format, $rec_txt)
    {
        $res = '';
        if (!$this->auth) {
            throw new Exception('Ранее клиент JSON-RPC не был авторизован, поэтому операция не может быть выполнена', 2);
        }
        $res = $this->obj->RecVirtualFormat($db, $format, $rec_txt);
        if ($this->obj->is_errors()) {
            throw new Exception($this->obj->get_last_error_message(), $this->obj->get_last_error_code());
        }
        return $res;
    }

    public function read_record($db, $mfn, $block = false)
    {
        $res = '';
        if (!$this->auth) {
            throw new Exception('Ранее клиент JSON-RPC не был авторизован, поэтому операция не может быть выполнена', 2);
        }
        $res = $this->obj->read_record($db, $mfn, $block);
        if ($this->obj->is_errors()) {
            throw new Exception($this->obj->get_last_error_message(), $this->obj->get_last_error_code());
        }
        return $res;
    }

    public function __call($metod, $arg)
    {
        $result = $GLOBALS['_1716293863_'][6](array(&$this->obj, $metod), $arg);
        switch ($this->class) {
            case 'jwrapper':
            default:
            {
                if ($this->obj->is_errors()) {
                    throw new Exception($this->obj->get_last_error_message(), $this->obj->get_last_error_code());
                }
            }
        }
        return $result;
    }
} ?>