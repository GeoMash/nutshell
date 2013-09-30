<?php
/**
 * @package nutshell-plugin
 * @author Timothy Chandler<tim.chandler@spinifexgroup.com>
 */
namespace nutshell\plugin\storage\engine\file
{
	use nutshell\plugin\storage\exception\StorageException;
	use nutshell\plugin\storage\AbstractBucket;
	
	/**
	 * @package nutshell-plugin
	 * @author Timothy Chandler<tim.chandler@spinifexgroup.com>
	 */
	class Bucket extends AbstractBucket
	{
		public function put($location=array(),$contents=null,$append=false)
		{
			$pathPart	=$this->parseStoragePattern($location);
			$file		=$this->getPath().$pathPart;
			$info		=pathinfo($file);
			if (!is_dir($info['dirname']))
			{
				mkdir($info['dirname'],0777,true);
			}
			if (!$append)
			{
				file_put_contents($file,$contents);
			}
			else
			{
				file_put_contents($file,$contents,FILE_APPEND);
			}
			return $this;
		}
		
		public function get($location=array())
		{
			$pathPart	=$this->parseStoragePattern($location);
			$file		=$this->getPath().$pathPart;
			if (is_file($file))
			{
				return file_get_contents($file);
			}
			else
			{
				return null;
// 				throw new Exception('Unable to get file from bucket. Path is "'.$file.'".');
			}
		}
		
		public function delete($location=array())
		{
			$pathPart	=$this->parseStoragePattern($location);
			$file		=$this->getPath().$pathPart;
			if (is_file($file))
			{
				unlink($file);
				return $this;
			}
			else
			{
				throw new StorageException(StorageException::CANNOT_DELETE_FILE, 'Unable to delete file from bucket. Path is "'.$file.'".');
			}
		}
		
		public function copy($fromLocation=array(),$toLocation,$autoCreateDir=false)
		{
			$fromLocation	=$this->getPath().$this->parseStoragePattern($fromLocation);
			$pathInfo		=pathinfo($toLocation);
			if (is_dir($fromLocation) && !file_exists($toLocation))
			{
				if ($autoCreateDir)
				{
					mkdir($toLocation,0777,true);
				}
				else
				{
					throw new StorageException(StorageException::INVALID_DESTINATION, 'Copy destination doesn\'t exist. Pass "true" as the last argument of this method to auto create the destination folder.');
				}
			}
			if (is_file($fromLocation))
			{
				copy($fromLocation,$toLocation);
			}
			else
			{
				$this->recursiveCopy($fromLocation,$toLocation);
			}
		}
		
		public function move($fromLocation=array(),$toLocation,$autoCreateDir=false)
		{
			$fromLocation	=$this->getPath().$this->parseStoragePattern($fromLocation);
			if (is_file($fromLocation) || file_exists($toLocation))
			{
				rename($fromLocation,$toLocation);
			}
			else
			{
				$pathInfo=pathinfo($toLocation);
				if (!file_exists($pathInfo['dirname']))
				{
					if ($autoCreateDir)
					{
						mkdir($pathInfo['dirname'],0777,true);
					}
					else
					{
						throw new StorageException(StorageException::INVALID_DESTINATION, 'Move destination doesn\'t exist. Pass "true" as the last argument of this method to auto create the destination folder.');
					}
				}
				rename($fromLocation,$toLocation);
			}
			return $this;
		}
		
		
		private function recursiveCopy($fromLocation,$destination,$diffDir='')
		{
			$handler=opendir($fromLocation);
			if (!file_exists($destination._DS_.$diffDir))mkdir($destination._DS_.$diffDir);
			while ($thisDir=readdir($handler))
			{
				if ($thisDir=='.' || $thisDir=='..')continue;
				if (is_dir($fromLocation._DS_.$thisDir))
				{
					$this->recursiveCopy($fromLocation._DS_.$thisDir,$destination,$diffDir._DS_.$thisDir);
				}
				else
				{
					copy($fromLocation._DS_.$thisDir,$destination._DS_.$diffDir._DS_.$thisDir);
				}
			}
			closedir($handler);
		}
	}
}
?>