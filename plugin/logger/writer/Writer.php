<?php
/**
 * @package nutshell-plugin
 * @author Guillaume Bodi <guillaume@spinifexgroup.com>
 */
namespace nutshell\plugin\logger\writer
{
	use nutshell\core\config\Config; 
	use nutshell\helper\Object;
	use nutshell\plugin\logger\exception\LoggerException;
	
	/**
	 * 
	 * Enter description here ...
	 * @author Guillaume Bodi <guillaume@spinifexgroup.com>
	 * @package nutshell-plugin
	 */
	abstract class Writer
	{
		const CTX_LOG_LEVEL = 'level';

		const CTX_LOGGER_NAME = 'logger';
		
		const DEFAULT_MESSAGE_PATTERN = '%d [%P] - %m%n';
		
		/**
		 * Pattern (using placeholders) for the message output
		 * 
		 * @var String
		 */
		protected $messagePattern = null;
		
		protected static $config = null;
		
		private static $instances = array();
		
		private static $implementations = array(
			'ConsoleWriter' => 'nutshell\plugin\logger\writer\console\ConsoleWriter',
			'FileWriter' => 'nutshell\plugin\logger\writer\file\FileWriter',
			'DailyFileWriter' => 'nutshell\plugin\logger\writer\file\DailyFileWriter',
			'DbWriter' => 'nutshell\plugin\logger\writer\db\DbWriter',
		);
		
		protected function __construct(Config $config)
		{
			$this->parseConfig($config);
		}
		
		/**
		 * Extracts valuable configuration options from the config node
		 * 
		 * @param Config $config
		 * @throws LoggerException LoggerException::, if a mandatory parameter is missing
		 */
		protected function parseConfig(Config $config)
		{
			$this->parseConfigOption($config, 'messagePattern', false);
			if (is_null($this->messagePattern))
			{
				$this->messagePattern = static::DEFAULT_MESSAGE_PATTERN;
			}
		}
		
		/**
		 * Reads the config for a specified option and loads it into the appropriate member
		 * 
		 * @param Config $config
		 * @param String $optionName
		 * @param boolean $mandatory
		 * @param String $memberName 
		 */
		protected function parseConfigOption(Config $config, $optionName, $mandatory = null, $memberName = null)
		{
			//default override
			if ($memberName === null) 
			{
				$memberName = $optionName;
			}
			if($mandatory === null) 
			{
				$mandatory = true;
			}
			
			//reading and assignment
			if($config->{$optionName})
			{
				$this->{$memberName} = $config->{$optionName};  
			}
			else if($mandatory)
			{
				throw new LoggerException(LoggerException::MISSING_OPTION, "Missing mandatory '%s' config option for writer", $optionName);
			}
		}
		
		/**
		 * Obtain an instance corresponding to the name and configuration
		 * 
		 * @param String $writerName
		 * @param nutshell\core\config\Config $writerConfig instance configuration details
		 */
		public static function runFactory($writerName, Config $writerConfig)
		{
			if(!array_key_exists($writerName, self::$instances))
			{
				self::$instances[$writerName] = self::createNewInstance($writerConfig);
			}
			
			return self::$instances[$writerName];
		}
		
		/**
		 * Creates and configure a new instance of a Writer based on the specified configuration
		 * 
		 * @param nutshell\core\config\Config $config instance configuration details
		 * @return nutshell\plugin\logger\writer\Writer a new instance
		 */
		private static function createNewInstance(Config $config)
		{
			if(!array_key_exists($config->class, self::$implementations))
			{
				throw new LoggerException(LoggerException::INVALID_WRITER, sprintf('Cannot resolve requested writer implementation: %s', $config->class));
			}
			
			return new self::$implementations[$config->class]($config);
		} 
		
		/**
		 * Write a log message
		 * 
		 * @param mixed $msg base message provided to the writer
		 * @param array $context contextual information for the pattern resolver
		 * @param mixed $extraData additional debug information
		 */
		public function write($msg, array $context, $extraData = null)
		{
			$this->doWrite(Pattern::resolve($this->messagePattern, $msg, $context, $extraData), $context);
		}
		
		/**
		 * Write the message to the supported media
		 * 
		 * @param String $msg
		 */
		protected abstract function doWrite($msg, $context);
		
		public function __toString()
		{
			return sprintf("%s", Object::getBaseClassName(get_class($this)));
		}
	}
}