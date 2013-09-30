<?php
namespace nutshell\plugin\format
{
	use nutshell\behaviour\AbstractFactory;
	use nutshell\core\plugin\Plugin;
	use nutshell\plugin\format\exception\FormatException;

	/**
	 * This plugins has been ported from the "format" model of leadgen 1.
	 * This plugin is a factory of formating classes. Please have a look at getAvailableFormats() method for a complete list.
	 * Usage Example:

			$a = array
			(
				0 => array ('a'=> 'a1', 'b'=>'b1', 'c'=>'c1'),
				1 => array ('a'=> 'a2', 'b'=>'b2', 'c'=>'c2'),
				2 => array ('a'=> 'a3', 'b'=>'b3', 'c'=>'c3')
			);
			
			$oFormat = $this->plugin->Format('CSV');

			$oFormat->new_file();
			$oFormat->process($a);
			$oFormat->close_file();
			echo "The file is here:". $oFormat->getWork_file_name();
			var_dump($oFormat->read());
			$oFormat->delete();
	 */
	class Format extends Plugin implements AbstractFactory
	{
	
		/**
		 * This function returns all available formats.
		 */
		public static function getAvailableFormats() {
			return array
			(
				// egine name (or format name) => class name
				'csv' 			=> 'Csv',
				'tsv' 			=> 'Tsv',
				'psv' 			=> 'Psv',
				'xml' 			=> 'Xml',
				'json'			=> 'Json',
				'adf' 			=> 'adf',
				'dsv' 			=> 'Dsv',
				'phptemplate'	=> 'PHPTemplate'
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
				$this->plugin->Logger->fatal($error_msg); // just to be sure that the error message will be in the log.
				throw new FormatException(FormatException::UNSUPPORTED_ENGINE, $error_msg);
			}
		}
	}	
}
