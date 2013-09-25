<?php
/**
 * @package nutshell-plugin
 * @author Guillaume Bodi <guillaume@spinifexgroup.com>
 */
namespace nutshell\plugin\logger
{
	use nutshell\plugin\logger\exception\LoggerException;
	
	/**
	 * @package nutshell-plugin
	 * @author Guillaume Bodi <guillaume@spinifexgroup.com>
	 */
	class Level
	{
		const DEBUG = 1;
		
		const INFO = 2;
		
		const WARN = 3;
		
		const ERROR = 4;
		
		const FATAL = 5;
		
		protected static $codesToString = array(
			self::DEBUG => 'DEBUG',
			self::INFO => 'INFO',
			self::WARN => 'WARN',
			self::ERROR => 'ERROR',
			self::FATAL => 'FATAL',
		);
		
		protected static $stringToCodes = null;
		
		public static function staticInit()
		{
			self::$stringToCodes = array_flip(self::$codesToString);
		}
		
		/**
		 * Convert a log level code to its corresponding string identifier
		 * 
		 * @param int $code
		 * @return String the log level identifier corresponding to the code  
		 * @throws LoggerException if the code is not a valid level
		 */
		public static function toString($code)
		{
			if (array_key_exists($code, self::$codesToString)) 
			{
				return self::$codesToString[$code];
			}
			throw new LoggerException(LoggerException::INVALID_LEVEL, sprintf("Invalid log level code: %d", $code));
		}
		
		/**
		 * Convert a log level identifier to its corresponding code
		 * 
		 * @param String $string
		 * @return int the code corresponding to the log level identifier
		 * @throws LoggerException if the string is not a valid level identifier
		 */
		public static function fromString($string)
		{
			if (array_key_exists($string, self::$stringToCodes)) 
			{
				return self::$stringToCodes[$string];
			}
			throw new LoggerException(LoggerException::INVALID_LEVEL, sprintf("Invalid log level string: %s", $string));
		}
	}
	
	//force a static init
	Level::staticInit();
}