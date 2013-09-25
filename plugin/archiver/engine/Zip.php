<?php
/**
 * @package nutshell-plugin
 * @author Guillaume Bodi <guillaume@spinifexgroup.com>
 */
namespace nutshell\plugin\archiver\engine 
{
	use ZipArchive;
	use nutshell\plugin\archiver\exception\ArchiverException;

	/**
	 * @author Guillaume Bodi <guillaume@spinifexgroup.com>
	 * @package nutshell-plugin
	 */
	class Zip extends Base
	{
		protected $archive = null;
		
		/**
		 * (non-PHPdoc)
		 * @see nutshell\plugin\archiver\engine.Base::loadArchive()
		 */
		protected function loadArchive()
		{
			$this->archive = new ZipArchive();
			if($this->archive->open($this->fileName, ZIPARCHIVE::CREATE) !== true)
			{
				throw new ArchiverException(ArchiverException::CANNOT_OPEN_ARCHIVE, sprintf("Failed to open archive located at: %s", $this->fileName));
			}
		}
		
		/**
		 * (non-PHPdoc)
		 * @see nutshell\plugin\archiver\engine.Base::save()
		 */
		public function save($saveCopyAsFileName = null){
			$saveName = is_null($saveCopyAsFileName) ? $this->fileName : $saveCopyAsFileName;
			if(!$this->archive->close())
			{
				throw new ArchiverException(ArchiverException::CANNOT_WRITE_FILE, sprintf("Failed to write archive %s to disk", $saveName));
			}
		}
		
		/**
		 * Corrects the file path inside the archive.
		 * @access protected
		 * @param String $archivePath
		 * @return String the corrected path
		 */
		protected static function pathFilter($archivePath){
			return preg_replace('@^(?:/{2,})?([^/])@', '\\1', $archivePath);
		}
		
		/**
		 * (non-PHPdoc)
		 * @see nutshell\plugin\archiver\engine.Base::addEntry()
		 */
		public function addEntry($realPath, $archivePath = null)
		{
			if (is_null($archivePath))
			{
				$archivePath = basename($realPath);
			}
			
			$archivePath = self::pathFilter($archivePath);
			if(!file_exists($realPath) ||!$this->archive->addFile($realPath, $archivePath))
			{
				throw new ArchiverException(ArchiverException::CANNOT_ADD_ENTRY, sprintf("Failed to add entry: %s", $realPath));
			}
		}
		
		/**
		 * (non-PHPdoc)
		 * @see nutshell\plugin\archiver\engine.Base::removeEntry()
		 */
		public function removeEntry($archivePath)
		{
			$archivePath = self::pathFilter($archivePath);
			if(!$this->archive->deleteName($archivePath))
			{
				throw new ArchiverException(ArchiverException::CANNOT_REMOVE_ENTRY, sprintf("Failed to remove entry: %s", $archivePath));
			}
		}
		
		/**
		 * (non-PHPdoc)
		 * @see nutshell\plugin\archiver\engine.Base::renameEntry()
		 */
		public function renameEntry($archivePath, $newArchivePath)
		{
			$archivePath = self::pathFilter($archivePath);
			$newArchivePath = self::pathFilter($newArchivePath);
			if($this->archive->locateName($newArchivePath) !== false)
			{
				$this->removeEntry($newArchivePath);
			}
			if(!$this->archive->renameName($archivePath, $newArchivePath))
			{
				throw new ArchiverException(ArchiverException::CANNOT_RENAME_ENTRY, sprintf("Failed to rename entry %s to %s", $archivePath, $newArchivePath));
			}
		}
		
		/**
		 * (non-PHPdoc)
		 * @see nutshell\plugin\archiver\engine.Base::extractTo()
		 */
		public function extractTo($destination, $entries = null)
		{
			$res = false;
			if(!is_null($entries))
			{
				if(!is_array($entries))
				{
					$entries = array($entries);
				}
				//clean the paths
				foreach($entries as $i => $v)
				{
					$entries[$i] = self::pathFilter($v);
				}
				$res = $this->archive->extractTo($destination, $entries);
			}
			else 
			{
				$res = $this->archive->extractTo($destination);
			}
			
			if(!$res)
			{
				throw new ArchiverException(ArchiverException::EXTRACTION_FAILED, sprintf("Extraction to %s failed", $destination));
			}
		}
	}
}