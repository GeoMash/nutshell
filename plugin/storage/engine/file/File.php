<?php
/**
 * @package nutshell-plugin
 * @author Timothy Chandler<tim.chandler@spinifexgroup.com>
 */
namespace nutshell\plugin\storage\engine\file
{
	use nutshell\plugin\storage\Engine;
	use nutshell\plugin\storage\engine\file\Bucket;
	/**
	 * @package nutshell-plugin
	 * @author Timothy Chandler<tim.chandler@spinifexgroup.com>
	 */
	class File extends Engine
	{
		public function __construct()
		{
			require_once('Bucket.php');
			parent::__construct();
		}
		
		public function getBucketInstance($path)
		{
			return new Bucket($path);
		}
	}
}
?>