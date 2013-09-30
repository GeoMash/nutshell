<?php
/**
 * @package nutshell-plugin
 * @author Dean Rather
 */
namespace nutshell\plugin\cache\exception 
{
	use nutshell\core\exception\PluginException;
	
	class CacheException extends PluginException
	{
		const INVALID_CACHE_TYPE = 1;
	}
}