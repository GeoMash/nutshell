<?php
/**
 * @package nutshell-plugin
 * @author Guillaume Bodi <guillaume@spinifexgroup.com>
 */
namespace nutshell\plugin\formatParser\exception
{
	use nutshell\core\exception\PluginException;

	/**
	 * @author Guillaume Bodi <guillaume@spinifexgroup.com>
	 * @package nutshell-plugin
	 */
	class FormatParserException extends PluginException
	{
		const ENGINE_NOT_SUPPORTED = 1;
		const FILE_READ_ERROR = 2;
	} 
}