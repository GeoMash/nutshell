<?php
/**
 * @package nutshell-plugin
 * @author Dean Rather
 */
namespace nutshell\plugin\template\exception
{
	use nutshell\core\exception\PluginException;

	class TemplateException extends PluginException
	{
		const INVALID_KEYVAL_LENGTHS = 1;
		const INVALID_FUNCTION = 2;
		const CANNOT_READ_FILE = 3;
		const FILE_MISSING = 4;
	}
}