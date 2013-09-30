<?php
/**
 * @package nutshell-plugin
 * @author guillaume
 */
namespace nutshell\plugin\mvc
{
	use nutshell\core\plugin\PluginExtension;
	use nutshell\behaviour\Loadable;
	use nutshell\Nutshell;
	
	/**
	 * @author guillaume
	 * @package nutshell-plugin
	 * @abstract
	 */
	abstract class Model extends MvcConnect implements Loadable
	{		
		public static function getInstance(Array $args=array())
		{
			$className=get_called_class();
			if (!isset($GLOBALS['NUTSHELL_MODEL'][$className]))
			{
				$instance=new static();
				if (method_exists($instance,'init'))
				{
					call_user_func_array(array($instance,'init'),$args);
				}
				$GLOBALS['NUTSHELL_MODEL'][$className]=$instance;
			}
			return $GLOBALS['NUTSHELL_MODEL'][$className];
		}
	}
}
?>