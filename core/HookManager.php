<?php
/**
* @package nutshell
*/
namespace nutshell\core
{
	/**
	 * 
	 * @package nutshell
	 */
	class HookManager
	{
		public static function execute($category,$hook,$args=array())
		{
			$hookName='nutshell\hook\\'.$category.'\\'.$hook;
			if (function_exists($hookName))
			{
				return call_user_func_array($hookName,$args);
			}
			return null;
		}
	}
}
?>