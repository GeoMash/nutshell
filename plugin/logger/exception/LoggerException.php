<?php
/**
 * @package nutshell-plugin
 * @author Guillaume Bodi <guillaume@spinifexgroup.com>
 */
namespace nutshell\plugin\logger\exception
{
	use nutshell\core\exception\NutshellException;

	/**
	 * @author Guillaume Bodi <guillaume@spinifexgroup.com>
	 * @package nutshell-plugin
	 */
	class LoggerException extends NutshellException
	{
		const INVALID_LEVEL = 1;
		const MISSING_OPTION = 2;
		const INVALID_WRITER = 3;
		const INCORRECT_LOGGER_DEFINITION = 4;
		const NO_WRITER_FOUND = 5;
		const INVALID_TABLE_NAME = 6;
		const COULD_NOT_LOG = 7;
		const CANNOT_WRITE_LOG = 8;
	} 
}