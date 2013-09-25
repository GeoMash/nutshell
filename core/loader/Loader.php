<?php
/**
 * @package nutshell
 */
namespace nutshell\core\loader
{
	use nutshell\Nutshell;

	use nutshell\helper\ObjectHelper;

	use nutshell\core\exception\LoaderException;

	use nutshell\core\Component;
	use nutshell\plugin;
	
	/**
	 * Configuration node instance
	 * 
	 * @author guillaume
	 * @package nutshell
	 */
	class Loader extends Component
	{
		/**
		 * A list of valid containers and their paths.
		 * 
		 * @access private
		 * @var Array
		 */
		private $containers=array();
		
		/**
		 * Class name of a given key.
		 * @var Array
		 */
		private $classNames=array();
		
		/**
		 * Interface of a given key.
		 */
		private $interfaces=array();
		
		/**
		 * A container of loaded classes.
		 * 
		 * @var Array
		 */
		private $loaded		=array
		(
			'plugin'		=>array()
		);
		
		public static function autoload($className)
		{
			$namespace=ObjectHelper::getNamespace($className);
			$className=ObjectHelper::getBaseClassName($className);
			if (strstr($namespace,'behaviour\\'))
			{
				list(,,$plugin)	=explode('\\',$namespace);
				$pathSuffix		='plugin'._DS_.$plugin._DS_.'behaviour'._DS_.$className.'.php';
				if (is_file($file=NS_HOME.$pathSuffix))
				{
					//Invoke the plugin.
					Nutshell::getInstance()->plugin->{ucfirst($plugin)};
				}
				else if (is_file($file=APP_HOME.$pathSuffix))
				{
					Nutshell::getInstance()->plugin->{ucfirst($plugin)};
				}
				else
				{
					throw new LoaderException(LoaderException::CANNOT_AUTOLOAD_CLASS, 'Unable to autoload class "'.$namespace.$className.'".');
				}
			}
			//When all else fails...
			else
			{
				$namespaceParts	=explode('\\',$namespace);
				$where			=array_shift($namespaceParts);
				$filePath		=false;
				if ($where==='nutshell')
				{
					$filePath=NS_HOME.implode(_DS_,$namespaceParts)._DS_.$className.'.php';
				}
				else if ($where==='application')
				{
					$filePath=APP_HOME.implode(_DS_,$namespaceParts)._DS_.$className.'.php';
				}
				
				if (is_file($filePath))
				{
					require_once($filePath);
				}
				else
				{
					throw new LoaderException(LoaderException::CANNOT_AUTOLOAD_CLASS, 'Unable to autoload class "'.$namespace.'\\'.$className.'".');
				}
			}
		}
		
		/**
		 * 
		 */
		public static function register()
		{
			static::load(array());
		}
		
		public function __construct()
		{
			parent::__construct();
			spl_autoload_register(__NAMESPACE__ .'\Loader::autoload');
		}
		
		public function registerContainer($name,$path,$namespace)
		{
			$this->containers[$name]=array
			(
				'path'		=>$path,
				'namespace'	=>$namespace
			);
		}
		
		private function loadClassDependencies($classname)
		{
			if($interfaces = class_implements($classname, false))
			{
				//Load class dependencies
				if (in_array('nutshell\behaviour\Native', $interfaces))
				{
					$classname::registerBehaviours();
				}
			}
			return $interfaces;
		}
		
		private function doLoad($key,Array $args=array())
		{
			$dirBaseFolderFile	=null;
			$dirBaseFile		=null;
			$dir				=null;
			//Is the object not loaded?
			if (!isset($this->loaded[$key]))
			{
				foreach($this->containers as $containerKey => &$container)
				{
					//No, so we need to load all of it's dependancies and initiate it.
					$dirBase=$container['path'];
					$namespaceBase=$container['namespace'];
					if (is_file($dirBaseFolderFile=$dirBase.lcfirst($key)._DS_.$key.'.php'))
					{
						$className = $namespaceBase.lcfirst($key).'\\'.$key;
						if(!class_exists($className))
						{
							require_once($dirBaseFolderFile);
						}
						$this->classNames[$key] = $className;
						$this->interfaces[$key] = $this->loadClassDependencies($this->classNames[$key]);
						break;
					}
					else if (is_file($dirBaseFile=$dirBase.$key.'.php'))
					{
						$className = $namespaceBase.$key;
						if(!class_exists($className)) {
							require($dirBaseFile);
						}
						$this->classNames[$key] = $className;
						$this->interfaces[$key] = $this->loadClassDependencies($this->classNames[$key]);
						break;
					}
					// is it a folder with other components?
					else if (is_dir($dir=$dirBase.$key))
					{
						$localInstance = new Loader;
						$localInstance->registerContainer($key,$dir._DS_,$namespaceBase.$key.'\\');
						$this->classNames[$key] = get_class($this);
						$this->loaded[$key]=$localInstance;
						break;
					}
				}// end of foreach
				if(!isset($this->classNames[$key]))
				{
					throw new LoaderException
					(
						LoaderException::CANNOT_LOAD_KEY, 
						"Loader can't load key {$key}.",
						$this->classNames,
						$this->containers,
						$dirBaseFolderFile,
						$dirBaseFile,
						$dir
					);
				}
			}

			// is it a loader?
			if ( $this->classNames[$key] == get_class($this) )
			{
				//returns the loader
				return $this->loaded[$key];
			}
						
			$className  = $this->classNames[$key];
			$interfaces = $this->interfaces[$key];
			
			if (in_array('nutshell\behaviour\Loadable',(array)$interfaces))
			{
				// Initiate
				$this->loaded[$key] = 'Loading';
				$localInstance      = $className::getInstance($args);
				$this->loaded[$key] = $localInstance;
				return $localInstance;
			}
			else
			{
				throw new LoaderException
				(
					LoaderException::CANNOT_LOAD_CLASS, 
					'Loader failed to load class "'.$className.'". This is likely because the container '
					.'handle you\'re using to handle the loading with doesn\'t impement the "Loadable" behaviour.'
				);
			}
		}
		
		public function isLoaded($key)
		{
			return isset($this->loaded[$key]);
		}

		public function __get($key)
		{
			return $this->doLoad($key);
		}
		
		public function __call($key,$args)
		{
			return $this->doLoad($key,$args);
		}
	}
}
