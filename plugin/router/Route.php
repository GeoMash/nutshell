<?php
/**
 * @package nutshell-plugin
 * @author guillaume
 */
namespace nutshell\plugin\router
{
	use nutshell\core\plugin\PluginExtension;

	/**
	 * @author guillaume
	 * @package nutshell-plugin
	 */
	class Route extends PluginExtension
	{
		private $control=null;
		private $action	=null;
		private $args	=array();
		
		public function __construct($control,$action,$args)
		{
			parent::__construct();
			$this->control	=$control;
			$this->action	=$action;
			$this->args		=$args;
		}
		
		public function setControl($control)
		{
			$this->control=$control;
			return $this;
		}
		
		public function getControl()
		{
			return $this->control;
		}
		
		public function getControlNamespace()
		{
			$pointer	=-1;
			$thisNode	=$this->plugin->Url->node($pointer);
			$namespace	='';
			$firstNode	=$this->plugin->Url->node(0);
			if (!empty($firstNode))
			{
				while (($thisNode=$this->plugin->Url->node(++$pointer))!=$this->control)
				{
					$namespace.=$thisNode.'\\';
				}
			}
			return $namespace;
		}
		
		public function setAction($action)
		{
			$this->action=$action;
			return $this;
		}
		
		public function getAction()
		{
			return $this->action;
		}
		
		public function setArgs($args)
		{
			$this->args=$args;
			return $this;
		}
		
		public function getArgs()
		{
			return $this->args;
		}
		
		public function getArg($num)
		{
			if (isset($this->args[$num]))
			{
				return $this->args[$num];
			}
			return null;
		}
	}
}
?>