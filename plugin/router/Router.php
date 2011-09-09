<?php
/**
 * @package nutshell-plugin
 * @author guillaume
 */
namespace nutshell\plugin\router
{
	use nutshell\core\plugin\Plugin;
	use nutshell\behaviour\Native;
	use nutshell\behaviour\Singleton;
	use nutshell\plugin\router\handler\Simple;
	use nutshell\plugin\router\handler\Advanced;
	
	/**
	 * @author guillaume
	 * @package nutshell-plugin
	 */
	class Router extends Plugin implements Native,Singleton
	{
		const MODE_SIMPLE		='simple';
		const MODE_SIMPLE_REST	='simpleRest';
		const MODE_ADVANCED		='advanced';
		
		private $handler=null;
		
		public static function loadDependencies()
		{
			require(__DIR__.'/Route.php');
			require(__DIR__.'/handler/Simple.php');
			require(__DIR__.'/handler/SimpleRest.php');
			require(__DIR__.'/handler/Advanced.php');
		}
		
		public static function registerBehaviours()
		{
			
		}
		
		public function init()
		{
			//Handle simple routing.
			if ($this->config->mode==self::MODE_SIMPLE)
			{
				$this->handler=new Simple();
			}
			//Handle simple rest routing.
			elseif ($this->config->mode==self::MODE_SIMPLE_REST)
			{
				$this->handler=new SimpleRest();
			}
			//Handle advanced routing.
			elseif ($this->config->mode==self::MODE_ADVANCED)
			{
				$this->handler=new Advanced();
			}
		}
		
		public function getMode()
		{
			return $this->config->mode;
		}
		
		public function getRoute()
		{
			return $this->handler->getRoute();
		}
	}
}
?>