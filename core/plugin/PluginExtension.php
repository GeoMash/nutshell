<?php
/**
 * @package nutshell
 */
namespace nutshell\core\plugin
{
	use nutshell\Nutshell;
	use nutshell\core\exception\PluginException;
	use nutshell\core\Component;
	use nutshell\helper\ObjectHelper;
	
	/**
	 * @package nutshell
	 */
	abstract class PluginExtension extends Component
	{
		public static function register(){}
		
		public $config	=null;
		
		public function __construct()
		{
			parent::__construct();
			$this->config=Nutshell::getInstance()->config->plugin->{ObjectHelper::getBaseClassName($this->getParentPlugin())};

			if (method_exists($this,'init'))
			{
				$this->init();
			}
		}
		
		private function getParentPlugin()
		{
			$NS		=ObjectHelper::getNamespace($this);
			$NSParts=explode('\\',$NS);
			if ($NSParts[1]=='plugin' || $NSParts[2]=='plugin')
			{
				if (isset($NSParts[2]))
				{
					$NSParts[2]=ucwords($NSParts[2]);
				}
				else
				{
					throw new PluginException(PluginException::NO_PARENT_PLUGIN, 'Unable to find parent plugin.');
				}
				return $NSParts[0].'\\'.$NSParts[1].'\\'.$NSParts[2];
			}
			else
			{
				throw new PluginException(PluginException::INCORRECT_CONTEXT, 'Attempted to use PluginExtension outside of plugin context.');
			}
		}
	}
}
?>