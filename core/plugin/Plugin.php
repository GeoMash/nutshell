<?php
/**
 * @package nutshell
 */
namespace nutshell\core\plugin
{
	use nutshell\behaviour\Singleton;
	use nutshell\behaviour\AbstractFactory;
	use nutshell\behaviour\Loadable;

	use nutshell\Nutshell;
	use nutshell\core\exception\PluginException;
	use nutshell\core\Component;
	use nutshell\core\config\Config;
	use nutshell\core\HookManager;
	use nutshell\helper\ObjectHelper;
	
	/**
	 * @package nutshell
	 * @abstract
	 */
	abstract class Plugin extends AbstractPlugin
	{
		/**
		 * 
		 * @see nutshell\core\Component
		 */
		public static function register() 
		{
			if (!isset($GLOBALS['NUTSHELL_PLUGIN_SINGLETON']))
			{
				$GLOBALS['NUTSHELL_PLUGIN_SINGLETON']=array();
			}
			static::load(array());
		}
		
		/**
		 * This method is used internally by {@link nutshell\core\loader\Loader} to
		 * create/return instances of plugins. This almost always happens when
		 * something attempts to access the plugin via a plugin shortcut since these
		 * always point to the Loader's plugin container.  
		 * 
		 * @internal
		 * @access public
		 * @static
		 * @param Array $args - Args to be passed to the plugin constructor.
		 * @throws PluginException - If one of the base behaviours is not implemented.
		 * @return nutshell\core\plugin\Plugin
		 */
		public static function getInstance(Array $args=array())
		{
			$className=get_called_class();
			//If $instance is set, then it is definately a singleton.
			if (!isset($GLOBALS['NUTSHELL_PLUGIN_SINGLETON'][$className]))
			{
				//gets the list of implemented interfaces
				if($interfaces = class_implements($className, false))
				{
					//Is it a Factory?
					if (in_array('nutshell\behaviour\Factory', $interfaces))
					{
						$instance=new static();
						self::applyBehaviours($instance);
						call_user_func_array(array($instance,'init'),$args);
						return $instance;
					}
					//Is it a singleton?
					else if (in_array('nutshell\behaviour\Singleton', $interfaces))
					{
						$instance=new static();
						self::applyBehaviours($instance);
						$GLOBALS['NUTSHELL_PLUGIN_SINGLETON'][$className]=$instance;
						call_user_func_array(array($instance,'init'),$args);
					}
					//Is it an AbstractFactory?
					else if (in_array('nutshell\behaviour\AbstractFactory', $interfaces))
					{
						if (!count($args))$args[0]=null;
						return call_user_func_array(array($className,'runFactory'),$args);
					}
//					//Is it abstractable?
//					else if ($instance instanceof Abstractable)
//					{
//						//TODO: Deal with Abstractable interface.
//						throw new Exception('TODO: Deal with Abstractable interface.');
//					}
					else
					{
						throw new PluginException(PluginException::INVALID_PLUGIN, 'Plugin must implement one of either "Factory", "Singleton" or "AbstractFactory" interfaces.');
					}
				}
			}
			//It must be a singleton. Return the instance.
			return $GLOBALS['NUTSHELL_PLUGIN_SINGLETON'][$className];
		}
	}
}
?>