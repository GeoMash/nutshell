<?php
/**
 * @package nutshell-plugin
 * @author Dean Rather
 */
namespace nutshell\plugin\storage\exception
{
	use nutshell\core\exception\PluginException;

	class StorageException extends PluginException
	{
		const PATH_NOT_SET = 1;
		const BUCKET_ALREADY_BOUND = 2;
		const CANNOT_DELETE_FILE = 3;
		const INVALID_DESTINATION = 4;
		const INVALID_HANDLER = 5;
	}
}