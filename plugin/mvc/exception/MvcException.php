<?php
/**
 * @package nutshell-plugin
 * @author Dean Rather
 */
namespace nutshell\plugin\mvc\exception
{
	use nutshell\core\exception\PluginException;

	class MvcException extends PluginException
	{
		const DB_NOT_CONFIGURED = 1;
		const MODEL_MISCONFIGURED = 2;
		const INVALID_PRIMARY_KEY = 3;
		const INVALID_WHEREKEYVALS = 4;
		const ACTION_NOT_DEFINED = 5;
		const INVALID_FILE = 6;
	}
}