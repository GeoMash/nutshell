<?php
/**
 * @package nutshell-plugin
 * @author Dean Rather
 */
namespace nutshell\plugin\mvcCrud\exception
{
	use nutshell\core\exception\PluginException;

	class MvcCrudException extends PluginException
	{
		const DB_NOT_CONFIGURED = 1;
		const CRUD_MISCONFIGURED = 2;
		const PRIMARY_KEY_MISCONFIGURED = 3;
		const INVALID_WHEREKEYVALS = 4;
	}
}