<?php
/**
 * @package nutshell
 * @author Dean Rather
 */
namespace nutshell\core\exception
{
	use nutshell\core\exception\NutshellException;

	class PluginException extends NutshellException
	{
		/** The library pluin could not be found in either Nutshell or Application levels. */
		const LIBRARY_NOT_FOUND = 1;
		
		const INVALID_PLUGIN = 2;
		
		const NO_PARENT_PLUGIN = 3;
		
		const INCORRECT_CONTEXT = 4;
		
		const CANNOT_REDEFINE_BEHAVIOR = 5;
	}
}