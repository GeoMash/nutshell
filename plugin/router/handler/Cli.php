<?php
/**
 * @package nutshell-plugin
 * @author Guillaume Bodi <guillaume@spinifexgroup.com>
 */
namespace nutshell\plugin\router\handler
{
	use nutshell\plugin\router\Router;

	use nutshell\core\plugin\PluginExtension;
	use nutshell\plugin\router\Route;
	
	/**
	 * @author Guillaume Bodi <guillaume@spinifexgroup.com>
	 * @package nutshell-plugin
	 */
	class Cli extends PluginExtension
	{
		private $route = null;

		private $pointer = 0;
		
		const OPTION_CONTROLLER = 'c';
		
		const OPTION_ACTION = 'a';
		
		const DEFAULT_CONTROLLER = 'index';
		
		const DEFAULT_ACTION = 'index';
		
		public function __construct($modeConfig)
		{
			parent::__construct($modeConfig);
			$this->calculateRoute();
		}
		
		private function calculateRoute()
		{
			$controlNameSpace = '';
			$nodes = $this->request->getNodes();
			$actionOption = false;
			
			//check for the existence of an action flag
			if($item = $this->request->get(self::OPTION_ACTION))
			{
				array_unshift($nodes, $item);
				$actionOption = true;
			}
			
			//check for the existence of a controller flag
			if($item = $this->request->get(self::OPTION_CONTROLLER)) 
			{
				array_unshift($nodes, $item);
			}
			else if($actionOption) 
			{
				array_unshift($nodes, self::DEFAULT_CONTROLLER);
			}

			//define the parameters as all the nodes
			$controller = array_shift($nodes);
			$action = array_shift($nodes);
			$args = $nodes;
			
			if(!$controller)
			{
				$controller = self::DEFAULT_CONTROLLER;
			}
			
			if(!$action)
			{
				$action = self::DEFAULT_ACTION;
			}
			if(is_null($args))
			{
				$args = array();
			}

			if(strpos($controller, '.') !== false) 
			{
				$split = explode('.', $controller);
				// only keep up to the pointer index
				$split = array_slice($split, 0, $this->pointer + 1);
				$controller = array_pop($split);
				$controlNameSpace = $split ? implode('\\', $split) . '\\' : '';
			}

			if(is_null($this->route))
			{
				$this->route = new Route($controlNameSpace, $controller, $action, $args);
			} else {
				$this->route->setControlNamespace($controlNameSpace);
				$this->route->setControl($controller);
				$this->route->setAction($action);
				$this->route->setArgs($args);
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