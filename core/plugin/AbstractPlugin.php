<?php
/**
 * @package nutshell
 */
namespace nutshell\core\plugin
{
	use nutshell\behaviour\Singleton;
	use nutshell\behaviour\AbstractFactory;
	use nutshell\behaviour\Loadable;

	use nutshell\Nutshell;
	use nutshell\core\exception\PluginException;
	use nutshell\core\Component;
	use nutshell\core\config\Config;
	use nutshell\helper\ObjectHelper;
	
	/**
	 * @package nutshell
	 * @abstract
	 */
	abstract class AbstractPlugin extends Component implements Loadable
	{
// 		/**
// 		 * @access public
// 		 * @static
// 		 * @var Array
// 		 */
// 		private static $PLUGIN_CONFIG_LOADED	= array();
// 		/**
// 		 * @access protected
// 		 * @static
// 		 * @var Object
// 		 */
// 		protected static $instance				= null;
		/**
		 * @access protected
		 * @static
		 * @var Array
		 */
		protected static $behaviours			= array();
		
		/**
		 * @access public
		 * @var nutshell\core\config\Config
		 */
		// public $config							= null;
		/**
		 * @access public
		 * @var nutshell\Nutshell
		 */
		public $core							= null;
		/**
		 * @access public
		 * @var nutshell\core\plugin\Plugin
		 */
		public $plugin							= null;
		
		private $baseClassName					= null;
		
// 		abstract public static function getInstance(Array $args=array());
		
		/**
		 * Class Constructor. Creates plugin shortcuts to commonly
		 * used by plugins.
		 * 
		 * @access private
		 * @return nutshell\core\plugin\Plugin
		 */
		public function __construct()
		{
			parent::__construct();
			$this->baseClassName=ObjectHelper::getBaseClassName($this);
			$this->nutshell	=Nutshell::getInstance();
			// $this->config	=$this->nutshell->config->plugin->{ObjectHelper::getBaseClassName($this)};
			$this->plugin	=$this->nutshell->plugin;
			$this->request	=$this->nutshell->request;
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
			return Nutshell::getInstance()->config->plugin->{ObjectHelper::getBaseClassName(get_called_class())};
		}
		
		/**
		 * Register's a custom behavior with attached functionality.
		 * 
		 * You would usually attach a closure to this method as the callback and embed
		 * functionality which performs the specific "behaviour".
		 * 
		 * The closure must accept a single parameter "$classInstance" which is the instance
		 * of the class which has implemented the custom behaviour.
		 * 
		 * @access protected
		 * @static
		 * @param Mixed $plugin - Either the plugin name or an instance of the plugin.
		 * @param String $behaviourName - The name of the behaviour to register.
		 * @param Mixed $callback - A function reference or Closure.
		 * @return Void
		 * @throws Exception
		 */
		protected static function registerBehaviour($plugin,$behaviourName,$callback)
		{
			$pluginName	=lcfirst(ObjectHelper::getBaseClassName($plugin));
			$behaviour	='nutshell\behaviour\\'.$pluginName.'\\'.$behaviourName;
			if (!isset(self::$behaviours[$behaviour]))
			{
				self::$behaviours[$behaviour]=$callback;
			}
			else
			{
				throw new PluginException(PluginException::CANNOT_REDEFINE_BEHAVIOR, 'Plugin ".$pluginName." tried to define the behaviour "'.$behaviourName.'". But this behaviour has already been defined.');
			}
		}
		
		/**
		 * This is an internal method which is responsible for applying a custom behaviour
		 * to a plugin which has implemented said behaviour by executing the registered 
		 * behaviour's callback method and passing through the instance of the plugin.
		 * 
		 * @internal
		 * @access public
		 * @static
		 * @param nutshell\core\plugin\Plugin $classInstance - The instance of the class which implemented the behavior.
		 * @return Void
		 */
		public static function applyBehaviours($classInstance)
		{
			$interfaces=class_implements($classInstance,false);
			//Apply behaviours.
			foreach (self::$behaviours as $behaviour=>$callback)
			{
				if (in_array($behaviour,$interfaces))
				{
					$callback($classInstance);
				}
			}
		}
		
		/**
		 * This overloader is responsible for executing {@link nutshell\behaviour\AbstractFactory::runFactory()}
		 * on plugins which implement a behaviour combination of {@link nutshell\behaviour\Singleton}
		 * and {@link nutshell\behaviour\AbstractFactory}
		 * 
		 * This magic basically allows the logic of creating instances of 
		 * plugins to be offloaded to the plugin's implementation of runFactory().
		 * 
		 * @param String $key
		 */
		public function __get($key)
		{
			if ($key=='config')
			{
				return $this->nutshell->config->plugin->{$this->baseClassName};
			}
			if (($this instanceof Singleton && $this instanceof AbstractFactory)
			&& $result=static::runFactory($key))
			{
				return $result;
			}
			return null;
		}
	}
}
?>