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
			$plugin	=null;
			for ($i=0,$j=count($NSParts); $i<$j; $i++)
			{
				if ($NSParts[$i]=='plugin')
				{
					if (isset($NSParts[$i+1]))
					{
						$plugin=$NSParts[$i+1];
						break;
					}
					else
					{
						throw new PluginException(PluginException::NO_PARENT_PLUGIN, 'Unable to find parent plugin.');
					}
				}
			}
			if ($plugin)
			{
				$fullPath='';
				for ($i=0,$j=count($NSParts); $i<$j; $i++)
				{
					if ($NSParts[$i]==$plugin)
					{
						$fullPath.=ucwords($plugin);
						break;
					}
					else
					{
						$fullPath.=$NSParts[$i].'\\';
					}
				}
				return $fullPath;
			}
			else
			{
				throw new PluginException(PluginException::INCORRECT_CONTEXT, 'Attempted to use PluginExtension outside of plugin context.');
			}
		}
	}
}
?>