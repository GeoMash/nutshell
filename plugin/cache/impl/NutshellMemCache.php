<?php
namespace nutshell\plugin\cache\impl
{
	class NutshellMemCache extends NutshellCache
	{
		/**
		 * Stores data in to the cache for the $key. 
		 * @param string $cacheKey
		 * @param mixed  $data
		 * @param int    $expiryTime
		 * @param string $folder
		 * @return mixed
		 */
		public function store($cacheKey, &$data, $expiryTime, $folder='')
		{
			//TODO: code here
		}
		
		/**
		 * Retrieves data from cache. It returns FALSE when no data is found. 
		 * @param string $cacheKey
		 * @return mixed
		 */
		public function retrieve($cacheKey, $subFolder='')
		{
			//TODO: code here
		}
		
		/**
		 * Clears all expired cache.
		 * @param string $subFolder
		 */
		public function clearExpired($subFolder='')
		{
			//TODO: code here
		}
		
		/**
		 * Clears the cache
		 * @param string $subFolder
		 */
		public function clear($subFolder='')
		{
			//TODO: code here
		}
		
		/**
		 * Removes the key from the cache.
		 * @param string $key
		 * @param string $subFolder
		 */
		public function free($cacheKey, $subFolder='')
		{
			//TODO: code here
		}
	}
}