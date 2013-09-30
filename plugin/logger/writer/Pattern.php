<?php
/**
 * @package nutshell-plugin
 * @author Guillaume Bodi <guillaume@spinifexgroup.com>
 */
namespace nutshell\plugin\logger\writer
{
	use nutshell\plugin\logger\Level;
	
	/**
	 * @package nutshell-plugin
	 * @author Guillaume Bodi <guillaume@spinifexgroup.com>
	 */
	class Pattern
	{
		const LOG_LEVEL = '%P';
		
		const LOGGER_NAME = '%l';
		
		const MESSAGE = '%m';
		
		const DATE = '%d';
		
		const NEW_LINE = '%n';
		
		const PID = '%t';
		
		const PERCENT = '%%';
		
		const EXTRA_BLOCK_START = '-- DATA START --';
		
		const EXTRA_BLOCK_END = '-- DATA END --';
		
		private static $pid = null;
		
		private static $contextTopatterns = array(
			Writer::CTX_LOG_LEVEL => self::LOG_LEVEL,
			Writer::CTX_LOGGER_NAME => self::LOGGER_NAME,
		);
		
		/**
		 * 
		 * @param String $out
		 */
		protected static function conditionalNewLine($out)
		{
			//inserts a new line if none is present at the end
			if (substr($out, -1, 1) != "\n") 
			{
				return "\n";
			}
			
			return '';
		}
		
		/**
		 * 
		 * Enter description here ...
		 * @param String $pattern
		 * @param mixed $msg
		 * @param array $context
		 * @return String the computed string
		 */
		public static function resolve($pattern, $msg, $context, $extraData)
		{
			$context = self::filterContext($context);
			$context = self::extendContext(self::convertToString($msg), $context);
			$out = str_replace(array_keys($context), array_values($context), $pattern);
			
			//processes extra data for debug
			if(!is_null($extraData)) 
			{
				$out .= self::conditionalNewLine($out) . self::EXTRA_BLOCK_START . "\n";
				
				if(is_object($extraData) && $extraData instanceof \Exception) 
				{
					$out .= $extraData->__toString();
				}
				else {
					$out .= print_r($extraData, true);
				}
				
				$out .= self::conditionalNewLine($out) . self::EXTRA_BLOCK_END . "\n";
			}
			return $out;
		}
		
		/**
		 * Convert the argument to a safe string representation
		 * 
		 * @param mixed $msg
		 * @return String 
		 */
		protected static function convertToString($msg)
		{
			if(is_null($msg))
			{
				return '';
			}
			elseif(is_array($msg)) 
			{
				return print_r($msg, true);
			}
			
			return $msg;
		}
		
		/**
		 * 
		 * @param String $msg
		 * @param array $context
		 */
		protected static function extendContext($msg, array $context)
		{
			$context[self::MESSAGE] = $msg;
			$context[self::NEW_LINE] = "\n";
			$context[self::DATE] = date('c');
			$context[self::PID] = self::getPid();
			$context[self::PERCENT] = '%';
			if(array_key_exists(self::LOG_LEVEL, $context))
			{
				$context[self::LOG_LEVEL] = Level::toString($context[self::LOG_LEVEL]);
			}
			return $context;
		}
		
		protected static function getPid() {
			if(is_null(self::$pid)) {
				self::$pid = getmypid();
			}
			
			return self::$pid;
		}
	
		
		/**
		 * 
		 * 
		 * @param array $context
		 */
		protected static function filterContext(array $context)
		{
			foreach($context as $key => $value)
			{
				if(array_key_exists($key, self::$contextTopatterns))
				{
					$final[self::$contextTopatterns[$key]] = $value;
				}
			}
			
			return $final;
		}
	}
}