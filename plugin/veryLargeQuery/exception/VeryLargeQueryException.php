<?php
/**
 * @package nutshell-plugin
 * @author Dean Rather
 */
namespace nutshell\plugin\veryLargeQuery\exception
{
	use nutshell\core\exception\PluginException;

	class VeryLargeQueryException extends PluginException
	{
		const PDO_EXCEPTION = 1;
		const CANNOT_CONNECT_TO_DB = 2;
	}
}