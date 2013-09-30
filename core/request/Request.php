<?php
namespace nutshell\core\request
{
	use nutshell\Nutshell;
	use nutshell\core\Component;
	use nutshell\core\exception\RequestException;
	use nutshell\core\request\handler\Http;
	use nutshell\core\request\handler\Cli;
	use nutshell\core\request\handler\Cgi;
	use \SeekableIterator;
	use \Countable;
	use \OutOfBoundsException;
	
	class Request extends Component implements SeekableIterator,Countable
	{
		private $handler=null;
		
		public function __construct()
		{
			if (NS_INTERFACE==Nutshell::INTERFACE_HTTP)
			{
				require(__DIR__._DS_.'handler'._DS_.'Http.php');
				$this->handler=new Http();
			}
			else if (NS_INTERFACE==Nutshell::INTERFACE_CGI)
			{
				require(__DIR__._DS_.'handler'._DS_.'Cgi.php');
				$this->handler=new Cgi();
			}
			else if (NS_INTERFACE==Nutshell::INTERFACE_CLI
			|| NS_INTERFACE==Nutshell::INTERFACE_PHPUNIT)
			{
				require(__DIR__._DS_.'handler'._DS_.'Cli.php');
				$this->handler=new Cli();
			}
			$this->normalizeData();
			$this->rewind();
			parent::__construct();
		}
		
		public static function register()
		{
			static::load(array('Handler.php'));
		}
		
		public function get($key)
		{
			return $this->handler->get($key);
		}
		
		public function getAll()
		{
			return $this->handler->getAll();
		}
		
		public function getFiles()
		{
			return $this->handler->getFiles();
		}
		
		public function moveFile($ref,$destination)
		{
			if (method_exists($this->handler,'moveFile'))
			{
				return $this->handler->moveFile($ref,$destination);
			}
			else
			{
				throw new RequestException(RequestException::FILE_NO_SUPPORT, 'The active file handler does not support moving files.');
			}
		}
		
		public function getFileError($ref)
		{
			if (method_exists($this->handler,'getFileError'))
			{
				return $this->handler->getFileError($ref);
			}
			else
			{
				throw new RequestException(RequestException::FILE_NO_SUPPORT, 'The active file handler does not support getting file errors.');
			}
		}
		
		/**
		 * 
		 * @access public
		 * @return Request $this
		 * @throws RequestException
		 */
		public function set()
		{
			if ($numArgs=func_num_args())
			{
				$args=func_get_args();
				call_user_func_array(array($this->handler,'set'),$args);
			}
			else
			{
				throw new RequestException(RequestException::MUST_PROVIDE_ARGS, 'Invalid request set. No args given.');
			}
			return $this;
		}
		
		public function getRaw()
		{
			return $this->handler->getRaw();
		}
		
		public function node($num)
		{
			if (isset($this->handler->nodes[$num]))
			{
				return $this->handler->nodes[$num];
			}
			return null;
		}
		
		public function nodeEmpty($num)
		{
			return (isset($this->handler->nodes[$num]) && empty($this->handler->nodes[$num]));
		}
		
		public function getNodes()
		{
			return $this->handler->nodes;
		}
		
		public function firstNode()
		{
			return reset($this->handler->nodes);
		}
		
		public function lastNode()
		{
			return end($this->handler->nodes);
		}
		
		private function resetIndex($exclude=null)
		{
			$newIndex=array();
			for ($i=0,$j=count($this->INDEX); $i<$j; $i++)
			{
				if ($this->INDEX[$i]!==$exclude)
				{
					$newIndex[]=$this->INDEX[$i];
				}
			}
			$this->INDEX=$newIndex;
			if (isset($this->INDEX[$this->POSITION]))
			{
				$this->CURRENT=$this->INDEX[$this->POSITION];
			}
			else
			{
				$this->CURRENT=null;
			}
		}
		
		private function normalizeData()
		{
			foreach ($this->handler->data as $key=>&$val)
			{
				$this->INDEX[]=$key;
			}
		}
		
		/*** [START Implementation of Seekable Iterator] ***/
		
		private $INDEX			=array();
		private $POSITION		=0;
		private $CURRENT		=null;
		
		public function current()
		{
			return $this->handler->data[$this->CURRENT];
		}
		
		public function key()
		{
			return $this->CURRENT;
		}
		
		public function next()
		{
			$this->POSITION++;
			if (isset($this->INDEX[$this->POSITION]))
			{
				$this->CURRENT=$this->INDEX[$this->POSITION];
			}
			else
			{
				$this->CURRENT=null;
			}
			return $this;
		}
		
		public function rewind()
		{
			$this->POSITION=0;
			if (isset($this->INDEX[0]))
			{
				$this->CURRENT=$this->INDEX[0];
			}
			else
			{
				$this->CURRENT=null;
			}
			return $this;
		}
		
		public function valid()
		{
			return !is_null($this->CURRENT);
		}
		
		public function seek($position)
		{
			$this->POSITION=$position;
			if (isset($this->INDEX[$this->POSITION]))
			{
				$this->CURRENT=$this->INDEX[$this->POSITION];
			}
			else
			{
				$this->CURRENT=null;
			}
			if (!$this->valid())
			{
				throw new OutOfBoundsException('Invalid seek position ('.$position.').');
			}
			return $this;
		}
		
		/*** [END Implementation of Seekable Iterator] ***/
		
		/*** [START Implementation of Countable] ***/
		
		public $length=0;
		
		public function count()
		{
			return count($this->handler->data);
		}
		
		/*** [END Implementation of Countable] ***/
	}
}
?>