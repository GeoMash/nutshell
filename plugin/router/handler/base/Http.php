<?php
/**
 * @package nutshell-plugin
 * @author guillaume
 */
namespace nutshell\plugin\router\handler\base
{
	use nutshell\plugin\router\Route;

	use nutshell\core\plugin\PluginExtension;

	/**
	 * 
	 * @author guillaume
	 * @package nutshell-plugin
	 */
	abstract class Http extends PluginExtension
	{
		protected $modeConfig=null;
		
		public function __construct($modeConfig)
		{
			$this->modeConfig=$modeConfig;
			parent::__construct();
		}
		
		protected function getControlNamespace($controller)
		{
			$pointer	=-1;
			$thisNode	=$this->request->node($pointer);
			$namespace	='';
			$firstNode	=$this->request->node(0);
			if (!empty($firstNode))
			{
				while (($thisNode=$this->request->node(++$pointer))!=$controller)
				{
					$namespace.=$thisNode.'\\';
				}
			}
			return $namespace;
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
			return new Route($this->getControlNamespace($controller), $controller, $action, $args);
		}
	}
}
?>