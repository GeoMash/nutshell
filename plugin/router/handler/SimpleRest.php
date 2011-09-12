<?php
/**
 * @package nutshell-plugin
 * @author Guillaume Bodi <guillaume@spinifexgroup.com>
 */
namespace nutshell\plugin\router\handler
{
	use nutshell\core\plugin\PluginExtension;
	use nutshell\plugin\router\Route;
	
	/**
	 * @author Guillaume Bodi <guillaume@spinifexgroup.com>
	 * @package nutshell-plugin
	 */
	class SimpleRest extends PluginExtension
	{
		private $route	=null;
		
		public function __construct()
		{
			if ($this->plugin->Url->nodeEmpty(0))
			{
				$this->route=new Route('index','index',array());
			}
			else
			{
				//Set the controler to node 0.
				$control=$this->plugin->Url->node(0);
				
				//Set some defaults.
				$action	='index';
				$args	=array();
				
				//Check for action.
				if ($this->plugin->Url->node(1))
				{
					//Set args to nodes 2 onwards.
					$node=1;
					while ($arg=$this->plugin->Url->node($node))
					{
						$args[]=$this->plugin->Url->node($node++);
					}
				}
				$this->route=new Route($control,$action,$args);
			}
		}
		
		public function getRoute()
		{
			return $this->route;
		}
	}
}
?>