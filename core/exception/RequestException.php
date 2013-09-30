<?php
/**
 * @package nutshell
 * @author Dean Rather
 */
namespace nutshell\core\exception
{
	use nutshell\core\exception\NutshellException;

	class RequestException extends NutshellException
	{
		const MUST_PROVIDE_ARGS = 1;
		const FILE_INVALID_REF	= 2;
		const FILE_INVALID_FILE	= 3;
		const FILE_NO_SUPPORT	= 4;
		const FILE_PERMISSION	= 5;
	}
}