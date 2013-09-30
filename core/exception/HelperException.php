<?php
/**
 * @package nutshell
 * @author Dean Rather
 */
namespace nutshell\core\exception
{
	use nutshell\core\exception\NutshellException;

	class HelperException extends NutshellException
	{
		const INVALID_DATA_TYPE = 1;
	}
}