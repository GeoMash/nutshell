<?php
/**
 * @package nutshell
 */
namespace nutshell\core\plugin
{
	use nutshell\Nutshell;
	use nutshell\core\exception\PluginException;
	use nutshell\helper\ObjectHelper;
	
	use \DirectoryIterator;
	
	/**
	 * @package nutshell
	 * @abstract
	 */
	abstract class LibraryPlugin extends AbstractPlugin
	{
// 		public static function register()
// 		{
// 			static::load(array());
// 		}
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
		 * @throws Exception - If one of the base behaviours is not implemented.
		 * @return nutshell\core\plugin\Plugin
		 */
		public static function getInstance(Array $args=array())
		{
			$className=get_called_class();
			//If $instance is set, then it is definately a singleton.
			if (!isset($GLOBALS['NUTSHELL_PLUGIN_SINGLETON'][$className]))
			{
				$instance=new static();
				self::applyBehaviours($instance);
				$GLOBALS['NUTSHELL_PLUGIN_SINGLETON'][$className]=$instance;
				
				$namespace=ObjectHelper::getNamespace($className);
				list(,,$plugin)	=explode('\\',$namespace);
				
				if (is_dir(NS_HOME.'plugin'._DS_.$plugin._DS_))
				{
					$dir=NS_HOME.'plugin'._DS_.$plugin._DS_;
				}
				else if (is_dir(APP_HOME.'plugin'._DS_.$plugin._DS_))
				{
					$dir=APP_HOME.'plugin'._DS_.$plugin._DS_;
				}
				else
				{
					throw new PluginException
					(
						PluginException::LIBRARY_NOT_FOUND,
						NS_HOME.'plugin'._DS_.$plugin._DS_,
						APP_HOME.'plugin'._DS_.$plugin._DS_
					);
				}
				/**
				 * Of note, the plugin which instaciates
				 */
				foreach (new DirectoryIterator($dir) as $iteration)
				{
					//We don't load folders or files from within folders.
					if ($iteration->isFile() && !(substr($iteration->getBasename(), 0, 1)=='.'))
					{
						require_once($iteration->getPathname());
					}
				}
				
				$instance->init();
			}
			//It must be a singleton. Return the instance.
			return $GLOBALS['NUTSHELL_PLUGIN_SINGLETON'][$className];
		}
	}
}
?>