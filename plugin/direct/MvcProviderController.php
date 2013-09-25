<?php
/**
 * @package nutshell-plugin
 * @author guillaume
 */
namespace nutshell\plugin\direct
{
	use nutshell\Nutshell;
	use nutshell\plugin\mvc\Controller;

	/**
	 * @author guillaume
	 * @package nutshell-plugin
	 */
	class MvcProviderController extends Controller
	{
		const CODE_FAIL		=0;
		const CODE_SUCCESS	=1;
		
		public $core		=null;
		public $plugin		=null;
		private $responder	=null;
		
		private $response	=array
		(
			'tid'			=>null,
			'type'			=>null,
			'action'		=>null,
			'method'		=>null,
			'name'			=>null,
			'result'		=>null,
			'where'			=>null,
			'timestamp'		=>null
		);
		
		private $result=array
		(
			'success'	=>false,
			'message'	=>null,
			'code'		=>self::CODE_FAIL,
			'data'		=>null
		);
		
		public function __construct(Responder $responder,$request=null)
		{
			$this->core		=Nutshell::getInstance();
			$this->plugin	=$this->core->plugin;
			$this->responder=$responder;
			
			if (!is_null($request))
			{
				$this->response['tid']		=$request->tid;
				$this->response['type']		=$request->type;
				$this->response['action']	=$request->action;
				$this->response['method']	=$request->method;
			}
			else
			{
				$this->response['type']		='polling';
			}
			$this->response['timestamp']=time();
			//Link the response result to result.
			$this->response['result']=&$this->result;
			parent::__construct($this->plugin->Mvc);
		}
		
		public function respond(Array $response=array())
		{
			$this->response=$response;
			return $this;
		}
		
		public function success($message=null,$code=self::CODE_SUCCESS)
		{
			$this->result['success']	=true;
			$this->result['message']	=$message;
			$this->result['code']		=$code;
			return $this;
		}
		
		public function fail($message=null,$code=self::CODE_FAIL)
		{
			$this->result['success']	=false;
			$this->result['message']	=$message;
			$this->result['code']		=$code;
			return $this;
		}
		
		public function setResponseCode($code)
		{
			$this->result['code']		=$code;
			return $this;
		}
		
		public function setResponseMessage($message)
		{
			$this->result['message']		=$message;
			return $this;
		}
		
		public function setResponseData($data)
		{
			$this->result['data']		=$data;
			return $this;
		}
		
		public function sendResponse()
		{
			$this->responder->push($this->response);
			return $this;
		}
		
		public function fireEvent($name,$data=array())
		{
			$this->response['type']='event';
			$this->response['name']=$name;
			$this->response['data']=$data;
			unset($this->response['result']);
			unset($this->response['method']);
			unset($this->response['action']);
			unset($this->response['tid']);
			unset($this->response['where']);
		}
	}
}
?>