<?php
/**
 * @package nutshell-plugin
 * @author Guillaume Bodi <guillaume@spinifexgroup.com>
 */
namespace nutshell\plugin\logger\writer\console
{
	use nutshell\core\config\Config;
	
	use nutshell\plugin\logger\writer\Writer;
	
	/**
	 * @author Guillaume Bodi <guillaume@spinifexgroup.com>
	 * @package nutshell-plugin
	 */
	class ConsoleWriter extends Writer
	{
		/**
		 * (non-PHPdoc)
		 * @see nutshell\plugin\logger\writer.Writer::doWrite()
		 */
		protected function doWrite($msg, $context)
		{
			echo $msg;
		}
	}
}