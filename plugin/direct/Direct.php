<?php
/**
 * @package nutshell-plugin
 * @author guillaume
 */
namespace nutshell\plugin\direct
{
	use nutshell\plugin\mvc\Controller;
	use nutshell\plugin\direct\exception\DirectException;
	use nutshell\core\plugin\Plugin;
	use nutshell\behaviour\Native;
	use nutshell\behaviour\Singleton;
	
	/**
	 * @package nutshell-plugin
	 * @author guillaume
	 */
	class Direct extends Plugin implements Native,Singleton
	{
		private $services=array();
		
		public static function registerBehaviours()
		{
			require(__DIR__.'/behaviour/Pollable.php');
			require(__DIR__.'/behaviour/Remotable.php');
		}
		
		public function init()
		{
			
		}
		
		public function initService(Controller $controller,$APIRef)
		{
			if (isset($this->config->{$APIRef}))
			{
				if (!isset($this->services[$APIRef]))
				{
					return $this->services[$APIRef]=new Service($controller,$APIRef);
				}
				else
				{
					return $this->services[$APIRef];
				}
			}
			else
			{
				throw new DirectException(DirectException::INVALID_API_REFERENCE, $APIRef.'" has not been defined in config.');
			}
		}
	}
}
?>