<?php
    /**
     * Base JSON-RPC 2.0 Client
     * @package    Eaze
     * @subpackage Model
     * @author     Sergeyfast
     * @link       http://www.jsonrpc.org/specification
     */
    class BaseJsonRpcClient {
		 public $debug_request =false;
        /**
         * Use Objects in Result
         * @var bool
         */
        public $UseObjectsInResults = false;

        /**
         * Curl Options
         * @var array
         */
        public $CurlOptions = array(
            CURLOPT_POST             => 1
            , CURLOPT_RETURNTRANSFER => 1
            , CURLOPT_HTTPHEADER     => array( 'Content-Type' => 'application/json' )
        );

        /**
         * Current Request id
         * @var int
         */
        private $id = 1;

        /**
         * Is Batch Call Flag
         * @var bool
         */
        private $isBatchCall = false;

        /**
         * Batch Calls
         * @var BaseJsonRpcCall[]
         */
        private $batchCalls = array();

        /**
         * Batch Notifications
         * @var BaseJsonRpcCall[]
         */
        private $batchNotifications = array();
        
        private $ssl=false;
		private $login='';
		private $password='';

        /**
         * Create New JsonRpc client
         * @param string $serverUrl
         * @return BaseJsonRpcClient
         */
        public function __construct( $serverUrl ,$ssl=false,$login='',$password='') {
            $this->CurlOptions[CURLOPT_URL] = $serverUrl;
            $this->ssl=$ssl;
            $this->login=$login;
            $this->password=$password;
            
        }


        /**
         * Get Next Request Id
         * @param bool $isNotification
         * @return int
         */
        protected function getRequestId( $isNotification = false ) {
            return $isNotification ? null : $this->id++;
        }


        /**
         * Begin Batch Call
         * @return bool
         */
        public function BeginBatch() {
            if ( !$this->isBatchCall ) {
                $this->batchNotifications = array();
                $this->batchCalls         = array();
                $this->isBatchCall        = true;
                return true;
            }

            return false;
        }


        /**
         * Commit Batch
         */
        public function CommitBatch() {
            $result = false;
            if ( !$this->isBatchCall || ( !$this->batchCalls && !$this->batchNotifications ) ) {
                return $result;
            }

            $result = $this->processCalls( array_merge( $this->batchCalls, $this->batchNotifications ) );
            $this->RollbackBatch();

            return $result;
        }


        /**
         * Rollback Calls
         * @return bool
         */
        public function RollbackBatch() {
            $this->isBatchCall = false;
            $this->batchCalls  = array();

            return true;
        }


        /**
         * Process Call
         * @param string $method
         * @param array  $parameters
         * @param int    $id
         * @return mixed
         */
        protected function call( $method, $parameters, $id = null ) {
            $call = new BaseJsonRpcCall( $method, $parameters, $id );
            if ( $this->isBatchCall ) {
                if ( $call->Id ) {
                    $this->batchCalls[$call->Id] = $call;
                } else {
                    $this->batchNotifications[] = $call;
                }
            } else {
                $this->processCalls( array( $call ) );
            }

            return $call;
        }


        /**
         * Process Magic Call
         * @param string $method
         * @param array $parameters
         * @return BaseJsonRpcCall
         */
        public function __call( $method, $parameters = array() ) {
        	if (count($parameters)==1 && is_object($parameters[0])){
        		// Для любимого ЛЭТИ... 
        	 	$parameters=$parameters[0];
        	}
            return $this->call( $method, $parameters, $this->getRequestId() );
        }


        /**
         * Process Calls
         * @param BaseJsonRpcCall[] $calls
         * @return mixed
         */
        protected function processCalls( $calls ) {
            // Prepare Data
            $singleCall = !$this->isBatchCall ? reset( $calls ) : null;
            $result     = $this->batchCalls ? array_values( array_map( 'BaseJsonRpcCall::GetCallData', $calls ) ) : BaseJsonRpcCall::GetCallData( $singleCall );
			
            
            try{
	            
            	// Send Curl Request
            	$json_req=json_encode( $result );
            	
            	if ($this->debug_request)
            		$this->output_json_req('Запрос',$json_req);
            		
            		
	            $options = $this->CurlOptions + array( CURLOPT_POSTFIELDS =>  $json_req);
	            
	            
				if ($this->ssl){
					$options+=array(CURLOPT_SSL_VERIFYPEER => false);
				}
							
				if ($this->login){
					$options+=array(CURLOPT_HTTPAUTH => CURLAUTH_BASIC)+array(CURLOPT_USERPWD => $this->login . ":" . $this->password);
				}	            
	            
	            $ch      = curl_init();
	            curl_setopt_array( $ch, $options );
	
	            $recieved_data = curl_exec( $ch );
	            
            	
            	if ($this->debug_request)
            		$this->output_json_req('Ответ',$recieved_data);
            		
	            $data = @json_decode( $recieved_data, ! $this->UseObjectsInResults );
	            
				//var_export($data);
	            if (curl_errno($ch)) 
					throw new Exception(curl_error($ch),curl_errno($ch));	                
				
				$info=curl_getinfo($ch);   
				
				if ($info['http_code']!=200)  
					throw new Exception("Ошибка сервера: \" ".trim(str_replace(array("\n","\r"),'',strip_tags($recieved_data)))." \"",9995);	                 
				
				if ( $data === null or (!is_object($data) && !is_array($data))){ 	            	
	            	throw new Exception("Ошибка декодирования ответа: \" ".trim(str_replace(array("\n","\r"),'',strip_tags($recieved_data)))." \"",9996);	
				}
	            					
	            curl_close( $ch );
            }catch(Exception $e){
     			$error=new stdClass();
            	$error->code=$e->getCode();
            	$error->message='Низкоуровневая ошибка взаимодействия с внешним сервером';
            	$error->data=is_string($e->getMessage()) ? $e->getMessage() : var_dump($e->getMessage());
            	
            	if (!$this->UseObjectsInResults){
	   				$error=(array)$error;
            	} 
            	
            	// 
            	$data=$result;
            	// добавляем в каждый запрос сообщение об ошибке. 
            	foreach ($data as &$res )
            	$res=array_merge($res,
	            	array(				   
					   'error' => $error,				   

					)
				);
				
				if ($this->UseObjectsInResults){
	   				$data=(object)$data;
            	} 				
            }

			  
 
			
            // Process Results for Batch Calls
            if ( $this->batchCalls ) {
                foreach ( $data as $dataCall ) {
                    // Problem place?
                    $key  = $this->UseObjectsInResults ? $dataCall->id : $dataCall['id'];
                    $this->batchCalls[$key]->SetResult( $dataCall, $this->UseObjectsInResults );
                }
            } else {
                // Process Results for Call
                $singleCall->SetResult( $data, $this->UseObjectsInResults );
            }

            return true;
        }
        protected function output_json_req($title,$json_req){
        	echo "<h3>$title</h3><br><span style='font-family: \"Courier New\";'>$json_req</span>";
        	
        }
        
    }


    /**
     * Base Json Rpc Call
     * @package    Eaze
     * @subpackage Model
     * @author     Sergeyfast
     * @link       http://www.jsonrpc.org/specification
     */
    class BaseJsonRpcCall {

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
         * @return bool
         */
        public function HasError() {
            return !empty( $this->Error );
        }


        /**
         * @param string $method
         * @param array  $params
         * @param string $id
         */
        public function __construct( $method, $params, $id ) {
            $this->Method = $method;
            $this->Params = $params;
            $this->Id     = $id;
        }


        /**
         * Get Call Data
         * @param BaseJsonRpcCall $call
         * @return array
         */
        public static function GetCallData( BaseJsonRpcCall $call ) {
            return array(
                'jsonrpc'  => '2.0'
                , 'id'     => $call->Id
                , 'method' => $call->Method
                , 'params' => $call->Params
            );
        }


        /**
         * Set Result
         * @param mixed $data
         * @param bool  $useObjects
         */
        public function SetResult( $data, $useObjects = false ) {
            if ( $useObjects ) {
                $this->Error  = property_exists( $data, 'error' ) ? $data->error : null;
                $this->Result = property_exists( $data, 'result' ) ? $data->result : null;
            } else {
                $this->Error  = isset( $data['error'] ) ? $data['error'] : null;
                $this->Result = isset( $data['result'] ) ? $data['result'] : null;
            }
        }
        
    }

?>