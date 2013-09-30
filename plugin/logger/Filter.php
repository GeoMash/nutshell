<?php
/**
 * @package nutshell-plugin
 * @author Guillaume Bodi <guillaume@spinifexgroup.com>
 */
namespace nutshell\plugin\logger
{
	use nutshell\plugin\logger\Logger;
	use nutshell\plugin\logger\writer\Writer;
	use nutshell\plugin\logger\exception\LoggerException;
	
	/**
	 * @package nutshell-plugin
	 * @author Guillaume Bodi <guillaume@spinifexgroup.com>
	 */
	class Filter
	{
		protected $logger = null;
		
		protected $writer = null;
		
		protected $level = null;
		
		public function __construct(Logger $logger, $level, Writer $writer) 
		{
			$this->logger = $logger;
			$this->writer = $writer;
			
			if (is_int($level)) 
			{
				$this->level = $level;
			}
			else 
			{
				throw new LoggerException(LoggerException::INVALID_LEVEL, "Invalid argument for level, must be an integer", $level);
			}
		}
		
		public function isLogEnabled($level)
		{
			return $level >= $this->level;
		}
		
		public function __toString()
		{
			return sprintf("[%s] %s", Level::toString($this->level), $this->writer->__toString());
		}
		
		public function getWriter() {
			return $this->writer;
		}
	}
}