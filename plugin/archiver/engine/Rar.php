<?php
/**
 * @package nutshell-plugin
 * @author Guillaume Bodi <guillaume@spinifexgroup.com>
 */
namespace nutshell\plugin\archiver\engine
{
	/**
	 * 
	 * @author Guillaume Bodi <guillaume@spinifexgroup.com>
	 * @package nutshell-plugin
	 */
	class Rar extends Base
	{
		protected $archive = null;
		
		/**
		 * (non-PHPdoc)
		 * @see nutshell\plugin\archiver\engine.Base::loadArchive()
		 */
		protected function loadArchive()
		{
		}
		
		/**
		 * (non-PHPdoc)
		 * @see nutshell\plugin\archiver\engine.Base::save()
		 */
		public function save($saveCopyAsFileName = null){
			
		}
		
		/**
		 * (non-PHPdoc)
		 * @see nutshell\plugin\archiver\engine.Base::addEntry()
		 */
		public function addEntry($realPath, $archivePath = null)
		{
			
		}
		
		/**
		 * (non-PHPdoc)
		 * @see nutshell\plugin\archiver\engine.Base::removeEntry()
		 */
		public function removeEntry($archivePath)
		{
			
		}
		
		/**
		 * (non-PHPdoc)
		 * @see nutshell\plugin\archiver\engine.Base::renameEntry()
		 */
		public function renameEntry($archivePath, $newArchivePath)
		{
			
		}
		
		/**
		 * (non-PHPdoc)
		 * @see nutshell\plugin\archiver\engine.Base::extractTo()
		 */
		public function extractTo($destination, $entries = null)
		{
		}
	}
}