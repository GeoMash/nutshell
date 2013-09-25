<?php
/**
 * @package nutshell-plugin
 * @author guillaume
 */
namespace nutshell\plugin\router\handler
{
	use nutshell\plugin\router\Router;
	
	use nutshell\plugin\router\handler\base\Http;
	use nutshell\plugin\router\Route;
	
	/**
	 * @author guillaume
	 * @package nutshell-plugin
	 */
	class Simple extends Http
	{
		private $route		=null;
		private $pointer	=0;
		
		public function __construct($modeConfig)
		{
			parent::__construct($modeConfig);
			$this->calculateRoute();
		}
		
		private function calculateRoute()
		{
			if ($this->request->nodeEmpty($this->pointer))
			{
				if (is_null($this->route))
				{
					$this->route = $this->createRoute('index','index',array());
				}
				else
				{
					$this->route->setControlNamespace('index');
					$this->route->setControl('index');
					$this->route->setAction('index');
					$this->route->setArgs(array());
				}
			}
			else
			{
				//Set the controler to node 0.
				$control=$this->request->node($this->pointer);
				
				//Set some defaults.
				$action	='index';
				$args	=array();
			
				//Check for action.
				$node=$this->request->node($this->pointer+1);
				if (!is_null($node))
				{
					//Set action to node 1.
					$action	=$node;
					$node	=$this->pointer+2;
					//Check for args.
					if (!is_null($node))
					{
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
				}
				if (is_null($this->route))
				{
					$this->route = $this->createRoute($control, $action, $args);
				}
				else
				{
					$this->route->setControlNamespace($this->getControlNamespace($control));
					$this->route->setControl($control);
					$this->route->setAction($action);
					$this->route->setArgs($args);
				}
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