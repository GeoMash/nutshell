<?php
/**
* @package nutshell
*/
namespace nutshell\core
{
	use nutshell\Nutshell;
	use nutshell\helper\ObjectHelper;
	
	/**
	 * This is the base component class which all core
	 * components extend from.
	 * 
	 * @package nutshell
	 * @abstract
	 */
	abstract class Component
	{
// 		public $config	=null;
// 		public $core	=null;
// 		public $plugin	=null;
// 		public $request	=null;
		
		/**
		 * Class Constructor. 
		 */
		public function __construct()
		{
// 			$this->config		=Nutshell::getInstance()->config;
// 			$this->core			=Nutshell::getInstance();
// 			$this->plugin		=Nutshell::getInstance()->plugin;
// 			$this->request		=Nutshell::getInstance()->request;
			HookManager::execute('core\plugin',ObjectHelper::getBaseClassName($this).'OnConstruct');
		}
		
		/**
		 * Registers child classes to be loaded by the core loader.
		 * 
		 * @abstract
		 * @static
		 * @access public
		 * @return void
		 */
		public static function register() {}
		
		/**
		 * This is a helper method for loading a child class.
		 * 
		 * This method is usually used when a component implements
		 * the {link:register}() method.
		 * 
		 * @param Array $files - An array of file paths to load.
		 * @static
		 * @access public
		 * @return void;
		 */
		public static function load($files)
		{
			$directory = ObjectHelper::getClassPath(get_called_class());
			
			if (!is_array($files))$files=array($files);
			for ($i=0,$j=count($files); $i<$j; $i++)
			{
				$file= $directory . $files[$i];
				require($file);
			}
		}
		
		/**
		 * This object overloader is responsible for providing
		 * shortcuts to commonly accessed objects within the
		 * framework.
		 * 
		 * @param String $key - The shortcut.
		 * @deprecated
		 */
		public function __get($key)
		{
			switch ($key)
			{
				case 'config':	return Nutshell::getInstance()->config;
				case 'core':	return Nutshell::getInstance();
				case 'plugin':	return Nutshell::getInstance()->plugin;
				case 'request':	return Nutshell::getInstance()->request;
			}
		}
	}
}
?>