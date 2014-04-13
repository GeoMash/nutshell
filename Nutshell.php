<?php
/**
 * Nutshell
 *  - Built on Faith
 * 
 * @author Guillaume Bodi <guillaume@spinifexgroup.com>
 * @author Timothy Chandler <tim.chandler@spinifexgroup.com>
 * @author Dean Rather <dean@sudocode.com>
 * @package nutshell
 * @copyright Spinifex Group Pty. Ltd.
 */
namespace nutshell
{
	use nutshell\core\Component;
	use nutshell\core\request\Request;
	use nutshell\core\config\Config;
	use nutshell\core\config\Framework;
	use nutshell\core\loader\Loader;
	use nutshell\core\plugin\Plugin;
	use nutshell\core\application\Application;
	use nutshell\core\exception\NutshellException;
	use nutshell\helper\ObjectHelper;
	use \stdClass;
	use \DIRECTORY_SEPARATOR;
	use \DirectoryIterator;
	
	/**
	 * @author Timothy Chandler <tim.chandler@spinifexgroup.com>
	 * @package nutshell
	 */
	class Nutshell
	{
		const VERSION				='1.1.0-dev-1';
		const VERSION_MAJOR			=1;
		const VERSION_MINOR			=1;
		const VERSION_MICRO			=0;
		const VERSION_DEV			=1;
		const NUTSHELL_ENVIRONMENT	='NS_ENV';
		const DEFAULT_ENVIRONMENT	='production';
		
		const INTERFACE_CLI 		='CLI';
		const INTERFACE_CGI			='CGI';
		const INTERFACE_HTTP 		='HTTP';
		const INTERFACE_PHPUNIT		='PHPUNIT';
		
		public $applicationConfig	=null;
		public $configPointer		=null;
		public $config 				=null;
		public $request				=null;
		private $pluginLoader       =  null;
		private $applicationLoader	=null;
		private $getHooks			=null;
		
		static $defaultConfig		=null;
		static $applicationRegistry	=array();
		
		
		/**
		 * Configures all special constants and libraries linking.
		 * 
		 * Note: This function should only be run in instances where
		 * {link:init}() is not used.
		 * 
		 * @access public
		 * @return void
		 */
		public function setup()
		{
			//PHP version check.
			if (version_compare(PHP_VERSION,'5.3.3','<'))
			{
				die('Nutshell requires PHP version 5.3.3 or higher.');
			}
			
			//App path check.
			// if (!count(self::$applicationRegistry))
			// {
			// 	die("No application has been registered. Register applications your bootsrap by using Nutshell::registerApplication(\$ref,\$path).");
			// }
			
			//Define constants.
			define('_DS_',DIRECTORY_SEPARATOR);
			define('NS_HOME',__DIR__._DS_);
			
			// //Valid App oath check
			// if (substr(APP_HOME, -1) != DIRECTORY_SEPARATOR)
			// {
			// 	define('APP_HOME', APP_HOME.DIRECTORY_SEPARATOR);
			// }
			
			if (isset($_SERVER['argc']) && $_SERVER['argc'] > 0)
			{
				if(isset($_SERVER['GATEWAY_INTERFACE']))
				{
					define('NS_INTERFACE', self::INTERFACE_HTTP);
					define('NS_WEB_HOME', dirname($_SERVER['SCRIPT_FILENAME']));
					define('NS_APP_WEB_HOME', dirname($_SERVER['SCRIPT_NAME']));
					header('X-Powered-By: PHP/'.phpversion().' & Nutshell/'.self::VERSION);
					header('X-Nutshell-Version:'.self::VERSION);
				}
				else if (strstr($_SERVER['PHP_SELF'],'phpunit'))
				{
					define('NS_INTERFACE', self::INTERFACE_PHPUNIT);
				}
				else 
				{
					define('NS_INTERFACE', self::INTERFACE_CLI);
				}
			}
			else if (!isset($_SERVER['REQUEST_URI']) && stristr($_SERVER['GATEWAY_INTERFACE'],'cgi'))
			{
				define('NS_INTERFACE', self::INTERFACE_CGI);
				define('NS_WEB_HOME', dirname($_SERVER['SCRIPT_FILENAME']));
				$scriptName=$_SERVER['SCRIPT_NAME'];
				if (strstr($scriptName,'/.php'))
				{
					$scriptName=str_replace('/.php','/',$scriptName);
				}
				define('NS_APP_WEB_HOME', dirname($scriptName));
				header('X-Powered-By: PHP/'.phpversion().' & Nutshell/'.self::VERSION);
				header('X-Nutshell-Version:'.self::VERSION);
			}
			else
			{
				define('NS_INTERFACE', self::INTERFACE_HTTP);
				define('NS_WEB_HOME', dirname($_SERVER['SCRIPT_FILENAME']));
				define('NS_APP_WEB_HOME', dirname($_SERVER['SCRIPT_NAME']));
				header('X-Powered-By: PHP/'.phpversion().' & Nutshell/'.self::VERSION);
				header('X-Nutshell-Version:'.self::VERSION);
			}
			
			//Set system timezone to UTC.
			date_default_timezone_set('UTC');
			
			//Get the system environment before doing anything.
			$this->getEnvironment();
			
			//Load the behaviours.
			$this->loadBehaviours();
			
			//Load the helpers.
			$this->loadHelpers();
			
			//Load the core components.
			$this->loadCoreComponents();
			
			//Init the request object.
			$this->request=new Request;
			
			//init loaders.
			$this->initLoaders();
			
			$this->applicationConfig=new stdClass;
			
			$this->config=Framework::loadConfig(NS_HOME._DS_.Config::CONFIG_FOLDER, NS_ENV);
			
			
			
			// // define temporary handlers
			// NutshellException::setDefaultHandlers();

			// // define real handlers as defined in the config
			// NutshellException::setHandlers();
			
			// //Parse php.ini config settings.
			// foreach ($this->config->php as $key=>$val)
			// {
			// 	ini_set($key,$val);
			// }
			
			
			//Register the plugin container.
			$this->applicationLoader->registerContainer('application',APP_HOME,'application\\');
			$this->pluginLoader->registerContainer('plugin',NS_HOME.'plugin'._DS_,'nutshell\plugin\\');
			$this->pluginLoader->registerContainer('appplugin',APP_HOME.'plugin'._DS_,'application\plugin\\');
		}
		
		/**
		 * Loads core behaviours from the behaviour folder.
		 * 
		 * Behaviours are interfaces which affect the way certain objects
		 * interact with other objects.
		 * 
		 * @access private
		 * @return Nutshell
		 */
		private function loadBehaviours()
		{
			foreach (new DirectoryIterator(NS_HOME.'behaviour'._DS_) as $iteration)
			{
				if (!$iteration->isDot())
				{
					require($iteration->getPathname());
				}
			}
			return $this;
		}
		
		/**
		 * Loads core helpers from the helder folder.
		 * 
		 * Helpers are specialized static classes containing methods for
		 * dealing with specific things which PHP does not offer out of the box.
		 * 
		 * @access private
		 * @return Nutshell
		 */
		private function loadHelpers()
		{
			foreach (new DirectoryIterator(NS_HOME.'helper'._DS_) as $iteration)
			{
				if (!$iteration->isDot())
				{
					require($iteration->getPathname());
				}
			}
			return $this;
		}
		
		/**
		 * Loads core components and registers them for loading.
		 * 
		 * Loads all the required core libraries and then
		 * registers their child classes for loading with the
		 * core loader.
		 * 
		 * @access private
		 * @return Nutshell
		 */
		private function loadCoreComponents()
		{
			require(NS_HOME.'core'._DS_.'Component.php');
			require(NS_HOME.'core'._DS_.'exception'._DS_.'NutshellException.php');
			require(NS_HOME.'core'._DS_.'exception'._DS_.'ConfigException.php');
			require(NS_HOME.'core'._DS_.'request'._DS_.'Request.php');
			require(NS_HOME.'core'._DS_.'config'._DS_.'Config.php');
			require(NS_HOME.'core'._DS_.'config'._DS_.'Framework.php');
			require(NS_HOME.'core'._DS_.'loader'._DS_.'Loader.php');
			require(NS_HOME.'core'._DS_.'exception'._DS_.'LoaderException.php');
			require(NS_HOME.'core'._DS_.'application'._DS_.'Application.php');
			require(NS_HOME.'core'._DS_.'exception'._DS_.'ApplicationException.php');
			require(NS_HOME.'core'._DS_.'plugin'._DS_.'AbstractPlugin.php');
			require(NS_HOME.'core'._DS_.'plugin'._DS_.'Plugin.php');
			require(NS_HOME.'core'._DS_.'plugin'._DS_.'LibraryPlugin.php');
			require(NS_HOME.'core'._DS_.'plugin'._DS_.'PluginExtension.php');
			
			NutshellException::register();
			Request::register();
			Config::register();
			Loader::register();
			Application::register();
			Plugin::register();
			
			return $this;
		}
		
		/**
		 * Initiates the loader class.
		 * 
		 * Note that the loader class which gets initiated
		 * will change depending on the environment Nutshell
		 * is running in.
		 * 
		 * @access private
		 * @return Nutshell
		 */
		private function initLoaders() 
		{
			$this->pluginLoader = new Loader();
			$this->applicationLoader = new Loader();
			return $this;
		}
		
		/**
		 * Fetches the system envrionment.
		 * 
		 * Used to set the NS_ENV constant.
		 * 
		 * @access private
		 * @return Nutshell
		 */
		private function getEnvironment()
		{
			if (!defined(self::NUTSHELL_ENVIRONMENT))
			{
				$env=getenv(self::NUTSHELL_ENVIRONMENT);
				if (!$env && function_exists('apache_getenv'))
				{
					$env=apache_getenv(self::NUTSHELL_ENVIRONMENT);
				}
				if (!$env)
				{
					$env = self::DEFAULT_ENVIRONMENT;
				}
				if ($env=='dev' && isset($_GET[self::NUTSHELL_ENVIRONMENT]))
				{
					$env = $_GET[self::NUTSHELL_ENVIRONMENT];
				}
				define(self::NUTSHELL_ENVIRONMENT, $env);
			}
			if (NS_INTERFACE==self::INTERFACE_HTTP)
			{
				header('X-Nutshell-Environment:'.NS_ENV);
			}
			return $this;
		}
		
		/**
		 * Setup the configs for each application based on the configured environment.
		 * Defaults to production mode.
		 * 
		 * The environment can be changed by setting an
		 * environment variable named "NS_ENV" to anything else.
		 * 
		 * @access private
		 * @return Nutshell
		 */
		public function setupApplicationConfig($application)
		{
			$this->applicationConfig->{$application}=Framework::loadConfig(APP_HOME.lcfirst($application)._DS_.Config::CONFIG_FOLDER, NS_ENV);
			if (self::$defaultConfig==$application)
			{
				$this->setConfigPointer($application);
			}
			return $this->applicationConfig->{$application};
		}
		
		public static function registerDefaultConfig($application)
		{
			self::$defaultConfig=$application;
			if (isset($GLOBALS['NUTSHELL']))
			{
				$nutshell=Nutshell::getInstance();
				if ($nutshell->getApplicationLoader()->isLoaded($application))
				{
					$nutshell->setConfigPointer($application);
				}
			}
		}
		
		public function setConfigPointer($application)
		{
			$this->configPointer=$application;
			$this->config		=$this->applicationConfig->{$this->configPointer};
			return $this;
		}
		
		public function getConfigPointer()
		{
			return $this->configPointer;
		}
		
		/**
		 * Nutshell framework initialisation.
		 * Creates an instance and performs the setup.
		 * Should always be used to start the framework.
		 * 
		 * @static
		 * @access public
		 * @return void
		 */
		public static function init() 
		{
			$GLOBALS['NUTSHELL'] = new Nutshell();
			$GLOBALS['NUTSHELL']->setup();
			return $GLOBALS['NUTSHELL'];
		}
		
		/**
		 * Sets the application path.
		 * 
		 * @param String $path - The application path to be set.
		 * @static
		 * @access public
		 * @return void
		 * @deprecated - Use Nutshell::registerApplication() instead.
		 */
		public static function setAppPath($path)
		{
			$path = realpath($path);
			if(is_null($path)) 
			{
				throw new NutshellException(NutshellException::INVALID_APP_PATH, 'Application path cannot be null.');
			}
			define('APP_HOME', $path . DIRECTORY_SEPARATOR);
		}
		
		public static function setApplictionPath($path)
		{
			$path=realpath($path);
			if (is_dir($path))
			{
				define('APP_HOME', $path . DIRECTORY_SEPARATOR);
			}
			else
			{
				throw new NutshellException(NutshellException::INVALID_ROOT_PATH, 'Invalid root path.');
			}
		}
		
		/**
		 * Returns the nutshell instance.
		 * 
		 * @static
		 * @access public
		 * @return \nutshell\Nutshell
		 */
		public static function getInstance()
		{
			if(!isset($GLOBALS['NUTSHELL'])) 
			{
				return Nutshell::init();
			}
			return $GLOBALS['NUTSHELL'];
		}
		
		/**
		 * Returns the core plugin loader instance.
		 * 
		 * @access public
		 * @return nutshell\core\loader\Loader
		 */
		public function getPluginLoader()
		{
			return $this->pluginLoader;
		}
		
		/**
		 * Returns the core application loader instance.
		 * 
		 * @access public
		 * @return nutshell\core\loader\Loader
		 */
		public function getApplicationLoader()
		{
			return $this->applicationLoader;
		}

		/**
		 * Registers a hook for calls on top of $this->*.
		 * 
		 * @access public
		 * @param $ref - The reference to use to access the target. 
		 * @param $callback - A callback function
		 */
		public function registerGetHook($ref,$callback)
		{
			$this->getHooks[$ref]=$callback;
			return $this;
		}

		/**
		 * Returns a pre-registered callback for get hooks.
		 * 
		 * @param $ref - The reference of the hook to get.
		 * @return bool|\Closure
		 */
		public function getGetHook($ref)
		{
			if (isset($this->getHooks[$ref]))
			{
				return $this->getHooks[$ref];
			}
			return false;
		}
		
		/*** OVERLOADING ***/
		
		/**
		 * Overloads the loader to provide direct access to the
		 * plugin loader container.
		 * 
		 * @param String $key - The shortcut.
		 * @access public
		 * @return nutshell\core\loader\Loader
		 * @throws nutshell\core\exception\NutshellException - If $key is not "plugin".
		 */
		public function __get($key)
		{
			switch ($key)
			{
				case 'plugin':		return $this->pluginLoader;
				case 'application':	return $this->applicationLoader;
				default:
				{
					throw new NutshellException(NutshellException::INVALID_PROPERTY, 'Attempted to get invalid property "'.$key.'" from core.');
				}
			}
		}
		
		/**
		 * Overloads setting on the core and blocks it.
		 * 
		 * @param String $key
		 * @param String $val
		 * @access public
		 * @return void
		 * @throws nutshell\core\exception\NutshellException - If anything attempts to set something on the core.
		 */
		public function __set($key,$val)
		{
			throw new NutshellException(NutshellException::READ_ONLY, 'Sorry, nutshell core is read only!');
		}
		
		/**
		 * This function returns true in the case a plugin loader exists.
		 */
		public function hasPluginLoader()
		{
			return (isset($this->pluginLoader));
		}
		
		/**
		 * This function returns true in the case a application loader exists.
		 */
		public function hasApplicationLoader()
		{
			return (isset($this->applicationLoader));
		}
	}
	
	/**
	 * Nutshell default bootstrapper
	 * 
	 * @return Nutshell
	 */
	function bootstrap()
	{
		return Nutshell::init();
	}
}
namespace 
{
	//checks for overriding bootstrap method
	if(function_exists('bootstrap')) 
	{
		//trigger it
		call_user_func('bootstrap');
//		booststrap(); - not working - why???
	}
	else
	{
		//just use the default bootstrap
		nutshell\bootstrap();
	}
}
?>