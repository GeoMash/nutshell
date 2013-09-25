<?php
/**
 * @package nutshell-plugin
 * @author Dean Rather
 */
namespace nutshell\plugin\direct\exception
{
	use nutshell\core\exception\PluginException;

	class DirectException extends PluginException
	{
		const INVALID_API_REFERENCE = 1;
		const INVALID_PROVIDER_TYPE = 2;
	}
}