<?php
/**
 * @package nutshell-plugin
 * @author Timothy Chandler <tim.chandler@spinifexgroup.com>
 */
namespace nutshell\plugin\mvcCrud
{
	use nutshell\core\plugin\LibraryPlugin;
	
	/**
	 * @author Timothy Chandler <tim.chandler@spinifexgroup.com>
	 * @package nutshell-plugin
	 */
	class MvcCrud extends LibraryPlugin
	{
		public function init()
		{
			$this->plugin->Mvc;
		}
	}
}
?>