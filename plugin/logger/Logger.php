<?php
/**
 * @package nutshell-plugin
 * @author Guillaume Bodi <guillaume@spinifexgroup.com>
 */
namespace nutshell\plugin\logger
{
	use nutshell\core\plugin\Plugin;
	
	use nutshell\behaviour\Native;
	use nutshell\behaviour\AbstractFactory;
	
	use nutshell\plugin\logger\Filter;
	use nutshell\plugin\logger\Level;
	use nutshell\plugin\logger\exception\LoggerException;
	
	use nutshell\plugin\logger\writer\Writer;
	
	/**
	 * @package nutshell-plugin
	 * @author Guillaume Bodi <guillaume@spinifexgroup.com>
	 */
	class Logger extends Plugin implements Native,AbstractFactory
	{
		const ROOT_LOGGER = '__ROOT__';
		
		private static $instances = array();
		
		protected $loggerName = null;
		
		protected $actualLoggerName = null;
		
		protected $filters = null;
		
		protected $bubbleUp = true;
		
		protected $parentLoggerLookedUp = false;
		
		protected $parentLogger = null;
		
		public static function registerBehaviours()
		{
			
		}
		
		public function __construct($loggerName)
		{
			parent::__construct();
			$this->configure($loggerName);
		}
		
		protected function configure($loggerName)
		{
			$this->loggerName = $loggerName;
			$this->filters = array();
			$loggerConfig = $this->resolveLoggerConfig();
			
		}
		
		protected function resolveLoggerConfig() 
		{
			$candidate = null;
			foreach($this->config->loggers as $nodeName => $config)
			{
				if(
					($candidate === null && $nodeName === self::ROOT_LOGGER) || 
					self::isMoreSpecific($candidate, $nodeName, $this->loggerName) 
				)
				{
					$candidate = $nodeName;
				}
			}
			
			//store the config logger name
			$this->actualLoggerName = $candidate;
			$loggerConfig = $this->config->loggers->{$candidate};
			
			//set the bubbling configuration
			if(!is_null($loggerConfig->bubbleUp))
			{
				$this->bubbleUp = $loggerConfig->bubbleUp;
			}
			
			$writers = $loggerConfig->writers;
			if($writers) 
			{
				foreach($writers as  $writer)
				{
					if ($writer->writer === null)
					{
						throw new LoggerException(LoggerException::INCORRECT_LOGGER_DEFINITION, sprintf("Incorrect logger definition."));
					}
					
					$writerConfig = $this->config->writers->{$writer->writer};
					if ($writerConfig === null)
					{
						throw new LoggerException(LoggerException::INCORRECT_LOGGER_DEFINITION, sprintf("Could not locate a writer definition named: %s", $writer->writer));
					}
					$this->filters[$writer->writer] = new Filter(
						$this,
						Level::fromString($writer->level),
						Writer::runFactory($writer->writer, $writerConfig))
					;
				}
			}
			else
			{
				throw new LoggerException(LoggerException::NO_WRITER_FOUND, sprintf("No writers defined for logger: %s", $this->loggerName));
			}
		}
		
		/**
		 * 
		 * @param String $currentCandidate
		 * @param String $newCandidate
		 * @param String $requestedName
		 * @return boolean true if $newCandidate is more specific logger than $currentCandidate regarding $requestedName, false otherwise
		 */
		protected static function isMoreSpecific($currentCandidate, $newCandidate, $requestedName)
		{
			return 
				//checks that the new candidate matches the requested name
				preg_match('/^' . preg_quote($newCandidate) . '(\\..+|$)/', $requestedName)
				//now checks that the new candidate is more specific than the current candidate
				&& ($currentCandidate === null || $currentCandidate === self::ROOT_LOGGER || strlen($currentCandidate) > strlen($newCandidate)) 
			;
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
				self::$instances[$loggerName] = new Logger($loggerName);
			}
			
			return self::$instances[$loggerName];
		}
		
		public function __toString()
		{
			$writers = '';
			foreach($this->filters as $name => $filter)
			{
				$writers .= sprintf("\n\t%s: %s", $name, $filter->__toString());
			}
			return sprintf("Logger[%s]%s", $this->loggerName, $writers);
		}
		
		public function debug($msg, $extraData = null) 
		{
			$this->log(Level::DEBUG, $msg, $extraData);
		}
		
		public function info($msg, $extraData = null)
		{
			$this->log(Level::INFO, $msg, $extraData);
		}
		
		public function warn($msg, $extraData = null)
		{
			$this->log(Level::WARN, $msg, $extraData);
		}
		
		public function error($msg, $extraData = null)
		{
			$this->log(Level::ERROR, $msg, $extraData);
		}
		
		public function fatal($msg, $extraData = null)
		{
			$this->log(Level::FATAL, $msg, $extraData);
		}
		
		protected function log($level, $msg, $extraData = null)
		{
			foreach($this->filters as $filter)
			{
				if($filter->isLogEnabled($level))
				{
					$filter->getWriter()->write
					(
						$msg,
						array
						(
							Writer::CTX_LOG_LEVEL => $level,
							Writer::CTX_LOGGER_NAME => $this->loggerName
						),
						$extraData
					);
				}
			}
			
			if($this->bubbleUp)
			{
				if($parent = $this->getParentLogger()) 
				{
					$parent->log($level, $msg, $extraData);
				}
			}
		}
		
		protected function getParentLogger()
		{
			if ($this->actualLoggerName == self::ROOT_LOGGER) 
			{
				//nothing above the root logger
				return null;
			}
			
			if (!$this->parentLoggerLookedUp) 
			{
				$candidate = $this->actualLoggerName;
				foreach($this->config->loggers as $nodeName => $config)
				{
					//inverts the candidate and nodeName parameters to 
					//use as isLessSpecific
					if(self::isMoreSpecific($nodeName, $candidate, $this->actualLoggerName))
					{
						$candidate = $nodeName;
					}
				}
				
				//if not better option is found and a root logger is available, use it
				if ($this->actualLoggerName == $candidate && $this->config->{self::ROOT_LOGGER})
				{
					$candidate = self::ROOT_LOGGER;
				}
				
				if ($this->actualLoggerName != $candidate) {
					$this->parentLogger = Logger::runFactory($candidate);
				}
			}
			
			return $this->parentLogger;
		} 
	}
}
?>