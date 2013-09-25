<?php
/**
 * @package nutshell
 * @author Dean Rather
 */
namespace nutshell\core\exception
{
	use nutshell\core\exception\NutshellException;

	class LoaderException extends NutshellException
	{
		const CANNOT_AUTOLOAD_CLASS = 1;
		
		const CANNOT_LOAD_KEY = 2;
		
		const CANNOT_LOAD_CLASS = 3;
		
		
	}
}