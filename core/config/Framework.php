<?php
/**
 * 
 * @package nutshell
 * @author Guillaume Bodi <guillaume@spinifexgroup.com>
 */
namespace nutshell\core\config 
{
	use nutshell\core\exception\ConfigException;

	use nutshell\Nutshell;

	/**
	 * @author Guillaume Bodi <guillaume@spinifexgroup.com>
	 * @package nutshell
	 */
	class Framework
	{
		const CONFIG_CACHE_FOLDER = 'cache';
		
		const CONFIG_CACHE_FILE = 'cache.json';
		
		/**
		 * 15 mins
		 * 
		 * 15 * 60
		 */
		const CONFIG_EXPIRATION = 900;
		
		const CONFIG_REBUILD_KEY = 'config-reload';
		
		private static $configPath=null;
		
		public static function loadConfig($configPath, $environment) 
		{
			self::$configPath=$configPath;
			if(self::hasToRebuild())
			{
				$config = self::rebuild($configPath, $environment);
			}
			else
			{
				$config = self::loadCachedConfig();
				if($config->application->configReload)
				{
					$config = self::rebuild($configPath, $environment);
				}
			}
			
			return $config;
		}
		
		protected static function hasToRebuild()
		{
			$rebuildCache = false;
			
			$folder = self::getCachedConfigFolder();
			
			if(!file_exists($folder))
			{
				if(!@mkdir($folder, 0755, true))
				{
					throw new ConfigException(ConfigException::CANNOT_CREATE_FOLDER, sprintf("Could not create the config cache folder. Please check the write permission on %s.", dirname($folder)));
				}
			}
			else if(!is_dir($folder))
			{
				throw new ConfigException(ConfigException::FOLDER_NON_EXIST, sprintf("%s does not resolve to a directory.", $folder));
			}
			else if((PHP_OS=="linux" && !is_executable($folder)) || !is_writeable($folder))
			{
				throw new ConfigException(ConfigException::CANNOT_WRITE_FILE, sprintf("Nutshell requires write and execute permission on %s", $folder));
			}
			
			
			$file = self::getCachedConfigFile();
			
			if(!file_exists($file))
			{
				if(!is_writeable($folder))
				{
					throw new ConfigException(ConfigException::CANNOT_WRITE_FILE, sprintf("%s is not a writeable directory.", $folder));
				}
				$rebuildCache = true;
			}
			else if(!is_file($file)) 
			{
				throw new ConfigException(ConfigException::CONFIG_FILE_NOT_FOUND, sprintf("%s does not resolve to a regular file.", $file));
			}
			else if(!is_readable($file) || !is_writeable($file)) 
			{
				throw new ConfigException(ConfigException::CANNOT_WRITE_FILE, sprintf("Nutshell requires read and write permissions on %s.", $file));
			}
			else if(self::forceRebuild() || time() - filemtime($file) > self::CONFIG_EXPIRATION)
			{
				$rebuildCache = true;
			}
			
			return $rebuildCache;
		}
		
		protected static function forceRebuild()
		{
			return Nutshell::getInstance()->request->get(self::CONFIG_REBUILD_KEY);
		}
		
		protected static function rebuild($configPath, $environment)
		{
			$configFile = new Config();
			
			//loads the framework default
			$config = Config::loadConfigFile(NS_HOME . Config::CONFIG_FOLDER, Config::makeConfigFileName(Config::DEFAULT_ENVIRONMENT));
			//loads the framework default plugins config
			$config->extendWith(self::loadAllPluginConfig(NS_HOME . 'plugin', Config::DEFAULT_ENVIRONMENT));
			// $config->extendWith(self::loadAllPluginConfig(self::$configPath .'..'._DS_. 'plugin', Config::DEFAULT_ENVIRONMENT));
			//loads the application plugins config
			$config->extendWith(self::loadAllPluginConfig(self::$configPath .'..'._DS_. 'plugin', array($environment, Config::DEFAULT_ENVIRONMENT)));
			//loads the application config
			$config->extendWith(Config::loadConfigFile($configPath, Config::makeConfigFileName(array($environment, Config::DEFAULT_ENVIRONMENT))));
			
			$configFile->config = $config;
			
			if(file_put_contents(self::getCachedConfigFile(), $configFile->__toString()) === false)
			{
				throw new ConfigException(ConfigException::CANNOT_WRITE_FILE, sprintf("Failed to write to file %s.", self::getCachedConfigFile()));
			}
			
			return $config;
		}
		
		protected static function loadAllPluginConfig($location, $environment)
		{
			$pluginConfig = new Config();
			$pluginConfig->plugin = new Config();
			$files = glob(rtrim($location, _DS_) . _DS_ . '*');
			if($files)
			{
				foreach($files as $file)
				{
					$pluginConfigPath = $file . _DS_ . Config::CONFIG_FOLDER;
					if(is_dir($file) && is_dir($pluginConfigPath))
					{
						try 
						{
							$config = Config::loadConfigFile($pluginConfigPath, Config::makeConfigFileName($environment));
							$pluginName = basename($file);
							$pluginConfig->plugin->{ucfirst($pluginName)} = $config;
						}
						catch(ConfigException $e)
						{
							if(!in_array($e->getCode(), array(ConfigException::CONFIG_FILE_NOT_FOUND)))
							{
								throw $e;
							}
						}
					}
				}
			}
			
			return $pluginConfig;
		}
		
		protected static function loadCachedConfig()
		{
			$file = self::getCachedConfigFile();
			$config = Config::loadConfigFile(dirname($file), basename($file));
			return $config;
		}
		
		protected static function getCachedConfigFolder() 
		{
			return self::$configPath . _DS_ . self::CONFIG_CACHE_FOLDER;
		}
		
		protected static function getCachedConfigFile() 
		{
			return self::getCachedConfigFolder() . _DS_ . self::CONFIG_CACHE_FILE;
		}
	}
}