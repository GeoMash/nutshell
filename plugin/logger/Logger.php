<?php
namespace nutshell\plugin\logger
{
	use nutshell\core\plugin\Plugin;
	use nutshell\behaviour\Native;
	use nutshell\behaviour\AbstractFactory;
	
	class Logger extends Plugin implements Native,AbstractFactory
	{
		const ROOT_LOGGER = '__ROOT__';
		
		private static $instances = array();
		
		protected $loggerName = null;
		
		protected $writers = null;
		
		/**
		 * 
		 * 
		 */
		public static function loadDependencies()
		{
			include_once(__DIR__.'/writer/Writer.php');
			
			include_once(__DIR__.'/writer/db/DbWriter.php');
			
			include_once(__DIR__.'/writer/file/FileWriter.php');
			include_once(__DIR__.'/writer/file/DailyFileWriter.php');
		}
		
		public function __construct($loggerName)
		{
			$this->configure();
		}
		
		protected function configure($loggerName)
		{
			$this->loggerName = $loggerName;
			$this->writers = array();
			$loggerConfig = $this->resolveLoggerConfig();
			
		}
		
		protected function resolveLoggerConfig() 
		{
			$candidate = null;
			
			foreach($this->config->loggers as $nodeName => $config)
			{
				if(($candidate === null && $nodeName === self::ROOT_LOGGER) || strstr($this->loggerName, $nodeName) )
				{
					$candidate = $nodeName;
				}
			}
		}
		
		/**
		 * 
		 * 
		 */
		public function init()
		{
			
		}
		
		/**
		 * 
		 * 
		 * @param String $loggerName
		 */
		public static function runFactory($loggerName = null)
		{
			if($loggerName === null)
			{
				$loggerName = self::ROOT_LOGGER;
			}
			
			if(!array_key_exists($loggerName, self::$instances))
			{
				self::$instances = new Logger($loggerName);
			}
			
			return self::$instances[$loggerName];
		}
	}
}
?>