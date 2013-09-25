<?php
/**
 * @package nutshell-plugin
 * @author guillaume
 */
namespace nutshell\plugin\router\handler
{
	use nutshell\core\plugin\PluginExtension;

	/**
	 * 
	 * @author guillaume
	 * @package nutshell-plugin
	 */
	class Advanced extends PluginExtension
	{
		public function __construct($modeConfig)
		{
			parent::__construct($modeConfig);
			$this->calculateRoute();
		}
		
		public function init()
		{
			
		}
		
		public function getRoute()
		{
			
		}
	}
}
?>