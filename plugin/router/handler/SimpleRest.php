<?php
/**
 * @package nutshell-plugin
 * @author Guillaume Bodi <guillaume@spinifexgroup.com>
 */
namespace nutshell\plugin\router\handler
{
	use nutshell\plugin\router\Router;
	use nutshell\plugin\router\Route;
	use nutshell\plugin\router\handler\base\Http;
	
	/**
	 * @author Guillaume Bodi <guillaume@spinifexgroup.com>
	 * @package nutshell-plugin
	 */
	class SimpleRest extends Http
	{
		private $route = null;
		private $pointer = 0;
		
		public function __construct()
		{
			parent::__construct($modeConfig);
			$this->calculateRoute();
		}
		
		public function calculateRoute()
		{
			if ($this->request->nodeEmpty($this->pointer))
			{
				$this->route = $this->createRoute('index','index',array());
			}
			else
			{
				//Set the controller to node @pointer.
				$control = $this->request->node($this->pointer);
				
				//Set some defaults.
				$action	= 'index';
				$args	= array();
				
				//Check for action.
				if (!is_null($this->request->node($this->pointer + 1)))
				{
					//Set args to nodes @pointer + 1 onwards.
					$node = $this->pointer + 1;
					while (true)
					{
						//grab the next node
						$arg = $this->request->node($node++);
						
						if(is_null($arg)) 
						{
							break;
						}
						//append to the args array 
						$args[] = $arg;
					}
				}
				
				$this->route = $this->createRoute($control, $action, $args);
			}
		}
		
		public function advancePointer()
		{
			$this->pointer++;
			$this->calculateRoute();
		}
		
		public function rewindPointer()
		{
			$this->pointer--;
			$this->calculateRoute();
		}
		
		public function getRoute()
		{
			return $this->route;
		}
	}
}
?>