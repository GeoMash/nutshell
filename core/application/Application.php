<?php
/**
 * @package nutshell
 */
namespace nutshell\core\application
{
	use nutshell\behaviour\Loadable;

	use nutshell\Nutshell;
	use nutshell\core\exception\ApplicationException;
	use nutshell\core\exception\NutshellException;
	use nutshell\core\Component;
	use nutshell\core\config\Config;
	use nutshell\helper\ObjectHelper;
	
	/**
	 * @package nutshell
	 * @abstract
	 */
	abstract class Application extends Component implements Loadable
	{
		/**
		 * Class Constructor. Creates plugin shortcuts to commonly
		 * used by plugins.
		 * 
		 * @access private
		 * @return nutshell\core\application\Application
		 */
		public function __construct()
		{
			parent::__construct();
			$applicationName	=ObjectHelper::getBaseClassName(get_called_class());
			$applicationRef		=lcfirst($applicationName);
			$this->nutshell		=Nutshell::getInstance();
			$this->config		=$this->nutshell->setupApplicationConfig($applicationName);
			$this->application	=Nutshell::getInstance()->application;
			$this->request		=Nutshell::getInstance()->request;
			$this->nutshell->getPluginLoader()->registerContainer($applicationRef.'_plugin',APP_HOME.$applicationRef._DS_.'plugin'._DS_,'application\\'.$applicationRef.'\plugin\\');
			
			if (method_exists($this,'init'))
			{
				$this->init();
			}
		}
		
		/**
		 * A magic method for fetching an instance of this plugin's config block.
		 * 
		 * @access public
		 * @static
		 * @return nutshell\core\config\Config
		 */
		public static function config()
		{
			return Nutshell::getInstance()->config->application->{ObjectHelper::getBaseClassName(get_called_class())};
		}
		
		static public function getInstance(Array $args=array())
		{
			$ref=strtoupper(ObjectHelper::getBaseClassName(get_called_class()));
			if (!isset($GLOBALS[$ref]))
			{
				try
				{
					$GLOBALS[$ref]=new static();
				}
				catch (NutshellException $e)
				{
					$e->treatException($e);
					exit();
				}
			}
			return $GLOBALS[$ref];
		}
		
		public function __get($key)
		{
			switch ($key)
			{
				case 'plugin':
				{
					// $currentPointer=$this->nutshell->getConfigPointer();
					$this->nutshell->setConfigPointer(ObjectHelper::getBaseClassName(get_called_class()));
					return $this->nutshell->plugin;
					// try
					// {
					// 	return $this->nutshell->plugin;
					// }
					// finally
					// {
					// 	print 'FINALLY';
					// 	$this->nutshell->setConfigPointer($currentPointer);
					// }
				}
				default:
				{
					throw new NutshellException(NutshellException::INVALID_PROPERTY, 'Attempted to get invalid property "'.$key.'" from application.');
				}
			}
		}
	}
}
?>