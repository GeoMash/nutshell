<?php
/**
 * @package nutshell-plugin
 * @author Guillaume Bodi <guillaume@spinifexgroup.com>
 */
namespace nutshell\plugin\router\exception 
{
	use nutshell\core\exception\PluginException;

	/**
	 * 
	 * @author Guillaume Bodi <guillaume@spinifexgroup.com>
	 * @package nutshell-plugin
	 */
	class RouterException extends PluginException
	{
		const CANNOT_RE_REGISTER_HANDLER = 1;
		const CONFIGURATION_MISSING = 2;
		const MISCONFIGURED = 3;
		const HANDLER_MISSING = 4;
	}
}