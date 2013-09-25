<?php
/**
 * @package nutshell-plugin
 * @author Guillaume Bodi <guillaume@spinifexgroup.com>
 */
namespace nutshell\plugin\transfer\exception 
{
	use nutshell\core\exception\PluginException;

	/**
	 * 
	 * @author Guillaume Bodi <guillaume@spinifexgroup.com>
	 * @package nutshell-plugin
	 */
	class TransferException extends PluginException
	{
		const NOT_YET_IMPLEMENTED = 1;
		const UNSUPPORTED_ENGINE = 2;
		const UNSUPPORTED_METHOD = 3;
		const SEND_FAILED = 4;
		const FTP_CONNECTION_FAILED = 5;
		const UNKNOWN_CONNECTION_MODE = 6;
		const CANNOT_SET_CONNECTION_MODE = 7;
		const FILE_NOT_FOUND = 8;
		const INVALID_PARAMETER = 9;
		const FILE_NOT_WRITABLE = 10;
		const CANNOT_DELETE = 11;
		const SCP_NOT_IMPLEMENTED = 12;
		const CANNOT_START_CONTEXT = 13;
		const CANNOT_READ_FILE = 14;
		const SSH_CANNOT_CONNECT = ;
		const UNKNOWN_AUTHENTIDATION_MODE = 15;
		const PUBLIC_KEY_NOT_FOUND = 16;
		const PRIVATE_KEY_NOT_FOUND = 17;
	}
}