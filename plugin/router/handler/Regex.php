<?php
/**
 * @package nutshell-plugin
 * @author guillaume
 */
namespace nutshell\plugin\router\handler
{
	use nutshell\core\plugin\PluginExtension;
	use nutshell\plugin\router\Route;

	/**
	 * 
	 * @author guillaume
	 * @package nutshell-plugin
	 */
	class Regex extends PluginExtension
	{
		protected $modeConfig=null;
		private $route		=null;
		
		public function __construct($modeConfig)
		{
			parent::__construct();
			$this->modeConfig=$modeConfig;
			$this->calculateRoute();
		}
		
		public function calculateRoute()
		{
			set_time_limit(5);
			$path=$this->flattenNodes();
			foreach ($this->modeConfig->map as $regex=>$controller)
			{
				if (preg_match('/'.str_replace('/','\/',$regex).'/',$path)===1)
				{
					$controller=str_replace('/','\\',$controller);
					if (is_null($this->route))
					{
						$this->route = $this->createRoute($controller,'index',array());
					}
					else
					{
						$this->route->setControlNamespace('');
						$this->route->setControl($controller);
						$this->route->setAction('index');
						$this->route->setArgs(array());
					}
					// print_r($this->route);exit();
					return;
				}
			}
		}
		
		public function getRoute()
		{
			return $this->route;
		}
		
		private function flattenNodes()
		{
			$nodes=$this->request->getNodes();
			if (count($nodes)===1 && empty($nodes[0]))
			{
				return '/';
			}
			else
			{
				return '/'.implode('/',$nodes).'/';
			}
		}
		
		/**
		 * Generates the Route
		 * 
		 * @param String $controller
		 * @param String $action
		 * @param Array $args
		 * @return the nutshell\plugin\router\Route
		 */
		protected function createRoute($controller, $action, $args)
		{
			return new Route('', $controller, $action, $args);
		}
	}
}
?>