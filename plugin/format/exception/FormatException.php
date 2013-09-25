<?php
/**
 * @package nutshell-plugin
 * @author Dean Rather
 */
namespace nutshell\plugin\format\exception
{
	use nutshell\core\exception\PluginException;

	class FormatException extends PluginException
	{
		const UNSUPPORTED_ENGINE = 1;
		const INVALID_ENCODING = 2;
		const INVALID_PARAMETER = 3;
		const CANNOT_OPEN_FILE = 4;
		const ENCODE_NOT_IMPLEMENTED = 5;
	}
}