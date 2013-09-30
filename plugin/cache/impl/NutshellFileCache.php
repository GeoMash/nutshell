<?php
namespace nutshell\plugin\cache\impl
{
	use \DirectoryIterator;
	use nutshell\Nutshell;
	
	class NutshellFileCache extends NutshellCache
	{
		/**
		 * File system cache folder
		 * @var string
		 */
		protected $cacheFolder = '';
		
		const CS_FILENAME = '.cache_';
		
		/**
		 * Defines the global file system cache folder.
		 * @param unknown_type $folder
		 */
		public function setCacheFolder($folder)
		{
			// enforces slash separator at the end.
			if ( substr($folder, strlen($folder),1) != _DS_ )
			{
				$folder .= _DS_;
			}
			$this->cacheFolder = $folder;
		}

		/**
		 * Stores data in to the cache for the $key. 
		 * @param string $cacheKey
		 * @param mixed  $data
		 * @param int    $expiryTime
		 * @param string $subFolder
		 * @return boolean Returns true when storing works correctly.
		 */
		public function store($cacheKey, &$data, $expiryTime, $subFolder='')
		{
			$keyMD5 = md5($cacheKey);
			
			if (strlen($subFolder)>0)
			{
				$subFolder = $subFolder._DS_;
			}
			
			$fileName    = $this->cacheFolder.$subFolder.self::CS_FILENAME.$keyMD5;
			$tmpFileName = $this->cacheFolder.$subFolder.'tmp_'.$keyMD5;
			
			$cacheLiteral = serialize
			(
				array
				(
					'key'    => $cacheKey,
					'expiry' => $expiryTime + time(),
					'data'   => $data
				)
			);
			
			// Writes to a temp file and then renames to keep atomicity.
			if ($fp = fopen($tmpFileName, 'wb')) 
			{
				fwrite ($fp, $cacheLiteral, strlen($cacheLiteral));
				fclose ($fp);
			
				if (!rename($tmpFileName, $fileName)) 
				{
					// On some systems rename() doesn't overwrite destination
					unlink($filename);
					if (!rename($tmpFileName, $fileName)) 
					{
						// Make sure that no temporary file is left over
						// if the destination is not writable
						unlink($tmpFileName);
					}
				}
				chmod($fileName, 0777);
				return true;
			}
			return false;
		}
		
		/**
		 * Retrieves data from cache. It returns FALSE when no data is found. 
		 * @param string $cacheKey
		 * @return mixed
		 */
		public function retrieve($cacheKey, $subFolder='')
		{
			$keyMD5 = md5($cacheKey);
			$now = time();
			
			if (strlen($subFolder)>0)
			{
				$subFolder = $subFolder._DS_;
			}
			
			$fileName = $this->cacheFolder.$subFolder.self::CS_FILENAME.$keyMD5;
			
			try 
			{	
				if (file_exists($fileName))
				{	
					
					if ($handle = fopen($fileName, "rb"))
					{
						$unserializedData = fread($handle, filesize($fileName));
						$data = unserialize($unserializedData);
						
						// is it the same key?
						if ($data['key'] == $cacheKey)
						{
							// is the cached value still valid?
							if ($now < $data['expiry'])
							{
								return $data['data'];
							}
							else
							{
								// it's expired. It's better to remove it!
								unset($handle);
								unlink($fileName);
							}
						}
					}
				}
			} 
			catch (NutshellException $e) 
			{
				// nothing can be done when a cache load fails.
				// no exception should be provoked
			}
			catch (\Exception $e) 
			{
				// nothing can be done when a cache load fails.
				// no exception should be provoked
			}

			return false;
		}
		
		/**
		 * Clears all expired files.
		 * @param string $subFolder
		 */
		public function clearExpired($subFolder='')
		{
			$cntRemoved = 0;
			$now = time();
			
			$prefixLen = strlen(self::CS_FILENAME);
			
			if (strlen($subFolder))
			{
				$subFolder = $subFolder._DS_;
			}

			$folderName = $this->cacheFolder.$subFolder;
			
			foreach (new DirectoryIterator($folderName) as $iteration)
			{
				if ($iteration->isFile() && !$iteration->isDot())
				{
					$baseFileName = $iteration->getBasename();
					
					$fileName = $iteration->getPathname();
					
					if (substr($baseFileName,0,$prefixLen) == self::CS_FILENAME)
					{
						if ($handle = fopen($fileName, "rb"))
						{
							$unserializedData = fread($handle, filesize($fileName));
							$data = unserialize($unserializedData);
							unset($handle);
						
							// is the cached value expired?
							if ($now > $data['expiry'])
							{
								// deletes the expired file
								unlink($fileName);
								$cntRemoved++;
							}
						}
					}
				}
			}
			Nutshell::getInstance()->plugin->Logger->info(" $cntRemoved expired cache files have been removed.");
		}

		/**
		 * Clears all files.
		 * @param string $subFolder
		 */
		public function clear($subFolder='')
		{
			$prefixLen = strlen(self::CS_FILENAME);
			
			if (strlen($subFolder))
			{
				$subFolder = $subFolder._DS_;
			}

			$folderName = $this->cacheFolder.$subFolder;
			
			foreach (new DirectoryIterator($folderName) as $iteration)
			{
				if ($iteration->isFile() && !$iteration->isDot())
				{
					$baseFileName = $iteration->getBasename();
					
					$fileName = $iteration->getPathname();
					
					if (substr($baseFileName,0,$prefixLen) == self::CS_FILENAME)
					{
						try 
						{
							unlink($fileName);	
						} 
						catch (NutshellException $e)
						{
							// it's a minor problem a cache clear fault. But we log just for safety.
							Nutshell::getInstance()->plugin->Logger->fatal("Failed to delete cache file '$fileName' ($baseFileName).");
						}
					}
				}
			}
		}
		
		/**
		 * Removes the key from the cache.
		 * @param string $key
		 * @param string $subFolder
		 */
		public function free($cacheKey, $subFolder='')
		{
			$keyMD5 = md5($cacheKey);
			
			if (strlen($subFolder)>0)
			{
				$subFolder = $subFolder._DS_;
			}
			
			$fileName = $this->cacheFolder.$subFolder.self::CS_FILENAME.$keyMD5;
			
			try 
			{	
				if (file_exists($fileName))
				{	
					unlink($fileName);					
				}
			} 
			catch (\Exception $e) 
			{
				Nutshell::getInstance()->plugin->Logger->fatal('Error while removing cache file: $fileName.');
				// nothing can be done when a cache free fails.
				// no exception should be provoked
			}
		}
	}
}