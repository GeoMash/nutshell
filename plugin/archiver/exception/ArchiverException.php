<?php
/**
 * @package nutshell-plugin
 * @author Guillaume Bodi <guillaume@spinifexgroup.com>
 */
namespace nutshell\plugin\archiver\exception 
{
	use nutshell\core\exception\PluginException;

	/**
	 * 
	 * @author Guillaume Bodi <guillaume@spinifexgroup.com>
	 * @package nutshell-plugin
	 */
	class ArchiverException extends PluginException
	{
		const UNSUPPORTED_ENGINE = 1;
		const CANNOT_OPEN_ARCHIVE = 2;
		const CANNOT_WRITE_FILE = 3;
		const CANNOT_ADD_ENTRY = 4;
		const CANNOT_REMOVE_ENTRY = 5;
		const CANNOT_RENAME_ENTRY = 6;
		const EXTRACTION_FAILED = 7;
	}
}