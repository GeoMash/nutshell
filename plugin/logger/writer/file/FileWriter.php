<?php
/**
 * @package nutshell-plugin
 * @author Guillaume Bodi <guillaume@spinifexgroup.com>
 */
namespace nutshell\plugin\logger\writer\file
{
	use nutshell\core\config\Config;
	use nutshell\plugin\logger\writer\Writer;
	use nutshell\plugin\logger\exception\LoggerException;
	
	/**
	 * @author Guillaume Bodi <guillaume@spinifexgroup.com>
	 * @package nutshell-plugin
	 */
	class FileWriter extends Writer
	{
		const DEFAULT_MODE = 644;
		
		protected $output = null;
		
		protected $mode = null;
		
		protected function parseConfig(Config $config)
		{
			parent::parseConfig($config);
			
			$this->parseConfigOption($config, 'output');
			$this->parseConfigOption($config, 'mode', false);
			$this->validateOutput();
		}
		
		/**
		 * 
		 * @param unknown_type $path
		 */
		protected function resolveOutputPlaceHolders($path)
		{
			return str_replace(
				array('{NS_HOME}', '{APP_HOME}'),
				array(NS_HOME, APP_HOME),
				$path
			);
		}
		
		/**
		 * Validate and flatten the path to the log file 
		 * 
		 * @throws LoggerException if the log file could not be located or created
		 */
		protected function validateOutput()
		{
			$realPath = $this->resolveOutputPlaceHolders($this->output);
			
			if(!file_exists($realPath))
			{
				if(!@touch($realPath))
				{
					throw new LoggerException(LoggerException::CANNOT_WRITE_LOG, sprintf('Could not create log file at: %s', $realPath));
				}
				
				if($this->mode === null)
				{
					$this->mode = self::DEFAULT_MODE;
				}
				
				if(!preg_match('/^[0-7]{3}$/', $this->mode))
				{
					throw new LoggerException(LoggerException::CANNOT_WRITE_LOG, sprintf('Could not apply invalid permissions set (%s) to log file at: %s', $this->mode, $realPath));
				}
				
				@chmod($realPath, octdec($this->mode));
			}
			else
			{
				if(!is_writable($realPath)) 
			  	{
					throw new LoggerException(LoggerException::CANNOT_WRITE_LOG, sprintf('Could not access log file at : %s for writing', $realPath));
			  	}
			}
			
			$this->output = realpath($realPath);
		}
		
		/**
		 * (non-PHPdoc)
		 * @see nutshell\plugin\logger\writer.Writer::doWrite()
		 */
		protected function doWrite($msg, $context)
		{
			file_put_contents($this->output, $msg, FILE_APPEND);
		}
	}
}