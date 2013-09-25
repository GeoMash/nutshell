<?php
namespace nutshell\core\request
{
	use nutshell\core\exception\RequestException;
	
	abstract class Handler
	{
		public $data	=null;
		public $raw		=null;
		public $files	=null;
		public $nodes	=array();
		
		abstract protected function setupNodes();
		
		abstract protected function setupData();
		
		public function __construct()
		{
			$this->setupNodes();
			$this->setupData();
			if (method_exists($this,'gatherFiles'))
			{
				$this->gatherFiles();
			}
		}
		
		public function get($key)
		{
			return (isset($this->data[$key]))?$this->data[$key]:null;
		}
		
		public function getAll()
		{
			return $this->data;
		}
		
		public function getRaw()
		{
			return $this->raw;
		}
		
		public function getFiles()
		{
			return $this->files;
		}
		
		public function set()
		{
			if ($numArgs=func_num_args())
			{
				$args=func_get_args();
				if (!is_array($args[0]))
				{
					$args[0]=array($args[0]=>$args[1]);
				}
				foreach ($args[0] as $key=>$val)
				{
					$this->data[$key]=$val;
				}
			}
			else
			{
				throw new RequestException(RequestException::MUST_PROVIDE_ARGS, 'Invalid request set. No args given.');
			}
			return $this;
		}
	}
}
?>