<?php
/**
 * @package nutshell
 * @author Guillaume Bodi <guillaume@spinifexgroup.com>
 */
namespace nutshell\core\exception
{
	use nutshell\core\exception\NutshellException;

	class ConfigException extends NutshellException
	{
		const INVALID_JSON = 1;
		
		const INVALID_CONFIG_FILE = 2;
		
		const CONFIG_FILE_NOT_FOUND = 3;
		
		const INVALID_NODE_EXTENSION = 4;
		
		const NON_PARSEABLE_STRUCTURE = 5;
		
		const CYCLIC_CONFIG_ERROR = 6;
		
		const CANNOT_CREATE_FOLDER = 7;
		
		const CANNOT_WRITE_FILE = 8;
		
		const FOLDER_NON_EXIST = 9;
	}
}