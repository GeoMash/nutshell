<?php
namespace nutshell\plugin\formatParser
{
	use nutshell\behaviour\AbstractFactory;
	use nutshell\core\plugin\Plugin;
	use nutshell\plugin\formatParser\exception\FormatParserException;

	use nutshell\Nutshell;

	class FormatParser extends Plugin implements AbstractFactory
	{
	
		/**
		 * This function returns all available formats.
		 */
		public static function getAvailableFormats()
		{
			return array
			(
				// engine name (or format name) => class name
				'csv' 			=> 'Csv',
				'tsv' 			=> 'Tsv',
				'psv' 			=> 'Psv',
				'json'			=> 'Json',
				'dsv' 			=> 'Dsv',
			);
		}
		
		public static function registerBehaviours()
		{
				
		}
	
		public function init()
		{
				
		}
	
		public static function runFactory($engine, $args = null)
		{
			$engine = strtolower($engine);
			$engines = self::getAvailableFormats();
			if(isset($engines[$engine]))
			{
				$className = __NAMESPACE__ . '\\' . $engines[$engine];
				return new $className($args);
			}
			else
			{
				$error_msg = "Engine $engine isn't supported.";
				Nutshell::getInstance()->plugin->Logger->fatal($error_msg); // just to be sure that the error message will be in the log.
				throw new FormatParserException(FormatParserException::ENGINE_NOT_SUPPORTED, $error_msg);
			}
		}
	}	
}
