<?php
/**
 * @package nutshell-plugin
 * @author Guillaume Bodi <guillaume@spinifexgroup.com>
 */
namespace nutshell\plugin\session\exception
{
	use nutshell\core\exception\PluginException;

	/**
	 * @author Guillaume Bodi <guillaume@spinifexgroup.com>
	 * @package nutshell-plugin
	 */
	class SessionException extends PluginException
	{
		const CONNECTOR_FAILED = 1;
		const CONNECTOR_DEFINITION_MISSING = 2;
		const HANDLER_MISCONFIGURED = 3;
		CONST UNKNOWN_STORAGE_ENGINE = 4;
	}
}