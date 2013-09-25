<?php
namespace nutshell\plugin\cache\impl
{
	abstract class NutshellCache
	{
		/**
		 * Stores data in to the cache for the $key. 
		 * @param string $cacheKey
		 * @param mixed  $data Any kind of data including arrays.
		 * @param int    $expiryTime Given in seconds.
		 * @param string $subFolder
		 * @return mixed
		 */
		abstract public function store($cacheKey, &$data, $expiryTime, $subFolder='');
		
		/**
		 * Retrieves data from cache. It returns FALSE when no data is found. 
		 * @param string $cacheKey
		 * @param string $subFolder
		 * @return mixed
		 */
		abstract public function retrieve($cacheKey, $subFolder='');
		
		/**
		 * Clears all expired cache.
		 * @param string $subFolder
		 */
		abstract public function clearExpired($subFolder='');
		
		/**
		 * Clears the cache. In the case of a file system cache, clears all cache files.
		 * @param string $subFolder
		 */
		abstract public function clear($subFolder='');
		
		/**
		 * Removes the key from the cache.
		 * @param string $cacheKey
		 * @param string $subFolder
		 */
		abstract public function free($cacheKey, $subFolder='');
	}
}