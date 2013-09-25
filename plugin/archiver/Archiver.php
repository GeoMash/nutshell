<?php
/**
 * @package nutshell-plugin
 * @author Guillaume Bodi <guillaume@spinifexgroup.com>
 */
namespace nutshell\plugin\archiver
{
	use nutshell\core\plugin\Plugin;
	use nutshell\behaviour\Native;
	use nutshell\behaviour\AbstractFactory;
	use nutshell\plugin\archiver\exception\ArchiverException;
	
	/**
	 * 
	 * @author Guillaume Bodi <guillaume@spinifexgroup.com>
	 * @package nutshell-plugin
	 */
	class Archiver extends Plugin implements Native,AbstractFactory
	{
		public static function registerBehaviours()
		{
			
		}
		
		public static function runFactory($engine)
		{
			$className = __NAMESPACE__ . '\\engine\\' . ucfirst($engine);
			
			if (class_exists($className))
			{
				return new $className();
			}
			else
			{
				throw new ArchiverException(ArchiverException::UNSUPPORTED_ENGINE, sprintf("Archiving engine not supported or misspelt: %s", $engine));
			}
		}
	}
}
?>