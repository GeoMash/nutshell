<?php
/**
 * @package nutshell-plugin
 * @author Dean Rather
 */
namespace nutshell\plugin\modelGenerator\exception
{
	use nutshell\core\exception\PluginException;

	class ModelGeneratorException extends PluginException
	{
		const DB_NOT_CONFIGURED = 1;
	}
}