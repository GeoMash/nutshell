<?php
/**
 * @package nutshell-plugin
 * @author Guillaume Bodi <guillaume@spinifexgroup.com>
 */
namespace nutshell\plugin\email\exception
{
	use nutshell\core\exception\PluginException;

	/**
	 * @author Guillaume Bodi <guillaume@spinifexgroup.com>
	 * @package nutshell-plugin
	 */
	class EmailException extends PluginException
	{
		const UNSUPPORTED_SEND_MODE = 1;
		const UNDEFINED_SEND_CONFIGURATION = 2;
	} 
}