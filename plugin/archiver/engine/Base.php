<?php
/**
 * @package nutshell-plugin
 * @author Guillaume Bodi <guillaume@spinifexgroup.com>
 */
namespace nutshell\plugin\archiver\engine
{
	use nutshell\plugin\archiver\exception\ArchiverException;
	
	use nutshell\core\plugin\PluginExtension;

	/**
	 * 
	 * Enter description here ...
	 * @author Guillaume Bodi <guillaume@spinifexgroup.com>
	 * @package nutshell-plugin
	 * @abstract
	 */
	abstract class Base extends PluginExtension
	{
		/**
		 * Currently opened archive
		 * 
		 * @access protected
		 * @var String
		 */
		protected $fileName;
		
		/**
		 * Opens an archive at the specified location.
		 * If the file already exists, load the archive content for update
		 * 
		 * @access public
		 * @param String $fileName location of the archive
		 * @throws ArchiverException if the archive exists, but cannot be read/is corrupted.
		 */
		public function open($fileName)
		{
			$this->fileName = $fileName;
			$this->loadArchive();
		}

		/**
		 * Loads the content of the archive (if found) at the location specified by the $fileName member.
		 * @access protected
		 * @throws ArchiverException if the archive exists, but cannot be read/is corrupted.
		 */
		protected abstract function loadArchive();
		
		/**
		 * Save the currently loaded archive.
		 * 
		 * @access public
		 * @param String $saveCopyAsFileName optional file name to use to write to disk. 
		 * If left empty, will overwrite (or create if didn't exist previously) the opened location
		 * @throws ArchiverException if could not write the file to disk
		 */
		public abstract function save($saveCopyAsFileName = null);
		
		/**
		 * Adds specified path to the current archive.
		 * Adding a directory will add all its content, recursively.
		 * 
		 * @access public
		 * @param String $realPath path to the entry to add
		 * @param String $archivePath local path inside the archive to save the entry as. Defaults to the real path's basename.
		 * @throws ArchiverException if could not add the entry to archive
		 */
		public abstract function addEntry($realPath, $archivePath = null);
		
		/**
		 * Remove specified entry from the current archive.
		 * Removing a directory entry will remove all its content, recursively.
		 * 
		 * @access public
		 * @param String $archivePath local path inside the archive to remove
		 * @throws ArchiverException if could not remove the entry from the archive
		 */
		public abstract function removeEntry($archivePath);
		
		/**
		 * Rename specified entry in the current archive.
		 * Equivalent to the mv linux command, it can also be used to move entries.
		 * 
		 * @access public
		 * @param String $archivePath path to the entry to rename
		 * @param String $newArchivePath new name for the entry
		 * @throws ArchiverException if could not rename the entry
		 */
		public abstract function renameEntry($archivePath, $newArchivePath);
		
		/**
		 * Extract the complete archive or the given files to the specified destination.
		 * 
		 * @access public
		 * @param String $destination Location where to extract the files.
		 * @param mixed $entries The entries to extract. It accepts either a single entry name or an array of names.
		 * @throws ArchiverException if the decompression fails
		 */
		public abstract function extractTo($destination, $entries = null);
	}
}