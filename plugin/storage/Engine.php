<?php
/**
 * @package nutshell-plugin
 * @author Timothy Chandler<tim.chandler@spinifexgroup.com>
 */
namespace nutshell\plugin\storage
{
	use nutshell\plugin\storage\exception\StorageException;
	use nutshell\core\plugin\PluginExtension;
	/**
	 * @package nutshell-plugin
	 * @author Timothy Chandler<tim.chandler@spinifexgroup.com>
	 */
	abstract class Engine extends PluginExtension
	{
		abstract public function getBucketInstance($path);
		
		const DEFAULT_BUCKET='__STORE__';
		
		private $buckets		=array(); 
		
		public function bindBucket($name,$path)
		{
			if (!isset($this->buckets[$name]))
			{
				return $this->buckets[$name]=$this->getBucketInstance($path);
			}
			else
			{
				throw new StorageException(StorageException::BUCKET_ALREADY_BOUND, 'Bucket "'.$name.'" is already bound and cannot be bound twice.');
			}
		}
		
		public function getBucket($name)
		{
			if (isset($this->buckets[$name]))
			{
				return $this->buckets[$name];
			}
			else
			{
				return false;
			}
		}
	}
}
?>