<?php
/**
 * @package nutshell-plugin
 * @author guillaume
 */
namespace nutshell\plugin\cache
{
	use nutshell\core\plugin\Plugin;
	use nutshell\behaviour\Native;
	use nutshell\behaviour\Singleton;
	use nutshell\Nutshell;
	use nutshell\plugin\cache\exception\CacheException;
	
	use nutshell\plugin\cache\impl\NutshellCache;
	use nutshell\plugin\cache\impl\NutshellFileCache;
	use nutshell\plugin\cache\impl\NutshellMemCache;
	
	/**
	 * @package nutshell-plugin
	 * @author guillaume
	 */
	class Cache extends Plugin implements Native,Singleton
	{
		/**
		 * This property stores an object that manages the cache.
		 * @var NutshellCache
		 */
		protected $oCacheManager = null;
		
		public static function registerBehaviours()
		{
			
		}
		
		public function init($type='file')
		{
			if ($type=='file')
			{
				$this->oCacheManager = new NutshellFileCache();
				$configCacheFolder = '';
				
				// in the case that no folder has been provided, give a default
				if (strlen($configCacheFolder)==0)
				{
					$configCacheFolder = APP_HOME.'cache'._DS_;
				}
				
				$this->oCacheManager->setCacheFolder($configCacheFolder);
			}	
			else if ($type=='memory')
			{
				$this->oCacheManager = new NutshellMemCache();
			}
			else
			{
				throw new CacheException(CacheException::INVALID_CACHE_TYPE, "'$type' is not a valid cache type.");
			}
		}
		
		/**
		 * Stores data in to the cache for the $key.
		 * @param string $cacheKey
		 * @param mixed  $data
		 * @param int    $expiryTime
		 * @param string $folder
		 * @return mixed
		 */
		public function store($cacheKey, &$data, $expiryTime = 1200, $folder='')
		{
			$this->oCacheManager->store($cacheKey, $data, $expiryTime, $folder);
		}
		
		/**
		 * Retrieves data from cache. It returns NULL when no data is found.
		 * @param string $cacheKey
		 * @return mixed
		 */
		public function retrieve($cacheKey, $subFolder='')
		{
			return $this->oCacheManager->retrieve($cacheKey, $subFolder);
		}
		
		/**
		 * Clears all expired cache.
		 * @param string $subFolder
		 */
		public function clearExpired($subFolder='')
		{
			return $this->oCacheManager->clearExpired($subFolder);
		}
		
		/**
		 * Clears the cache. In the case of a file system cache, clears all cache files.
		 * @param string $subFolder
		 */
		public function clear($subFolder='')
		{
			return $this->oCacheManager->clear($subFolder);
		}
		
		/**
		* Removes the key from the cache.
		* @param string $key
		* @param string $subFolder
		*/
		public function free($cacheKey, $subFolder='')
		{
			return $this->oCacheManager->free($cacheKey, $subFolder);
		}
		
		/**
		 * Builds a cacke key based in an unlimited amount of parameters.
		 */
		public function getCacheKey()
		{
			$args = func_get_args();
			return join('-', $args);
		}
	}
}
?>