<?php
namespace nutshell\plugin\transfer\engine
{
	use nutshell\core\plugin\PluginExtension;

	use nutshell\plugin\transfer\exception\TransferException;

	abstract class Base extends PluginExtension
	{
		const DEFAULT_RETRY_COUNT = 3;
		
		protected $host;
		
		protected $port;
		
		public function __construct($arg = null)
		{
			if(is_null($arg))
			{
				//do nothing
			}
			else if(is_array($arg)) 
			{
				//set all the options
				$this->setOptions($arg);
			}
			else if(is_string($arg))
			{
				//TODO: read from the config file
				throw new TransferException(TransferException::NOT_YET_IMPLEMENTED, 'Not yet implemented');
			}
			else
			{
				throw new TransferException(TransferException::UNSUPPORTED_ENGINE, sprintf('Unsupported engine constructor argument %s', print_r($arg, true)));
			}
		}
		
		public function setOptions(array $options)
		{
			foreach($options as $key => $value)
			{
				if(property_exists($this, $key))
				{
					$this->{$key} = $value;
				}
			}
		}
		
		/**
		 * Copy a file to the server
		 * 
		 * @param mixed $local local file path or file handle to the source file (the file handle argument might not be supported by all engines) 
		 * @param string $remote remote file path
		 * @throws TransferException
		 */
		public function put($local, $remote)
		{
			throw new TransferException(TransferException::UNSUPPORTED_METHOD, 'The %s engine does not implement the put method.', get_class($this));
		}
		
		/**
		 * Copy a file to the server retrying $maxAttemptCount times.
		 * @param string $local
		 * @param string $remote
		 * @param int $maxAttemptCount
		 * @throws TransferException
		 */
		public function putRetrying($local, $remote, $maxAttemptCount = self::DEFAULT_RETRY_COUNT)
		{
			$error_message = '';
			
			$notSent = true;
			
			$attemptCnt = 0;
			
			while( $notSent && ($attemptCnt<$maxAttemptCount) )
			{
				try 
				{
					$attemptCnt++;
					$this->put($local, $remote);
					$notSent = false;
				} catch (TransferException $e) 
				{
					$error_message = $error_message. " Attempt $attemptCnt:".$e->getMessage();
					// sleeps 2 seconds after a failed attempt
					sleep(2);
				}
			}
			if ($notSent)
			{
				throw new TransferException(TransferException::SEND_FAILED, $error_message);
			}
		}
		
		/**
		 * This method sends multiple files to the remote folder.
		 * @param array	 $aFiles Array with full path.
		 * @param string $remoteFolder
		 * @param int    $maxAttemptCount
		 */
		public function putRetryingMultipleFiles($aFiles, $remoteFolder='', $maxAttemptCount = self::DEFAULT_RETRY_COUNT)
		{
			foreach($aFiles as $localfile)
			{
				if (strlen($remoteFolder>0))
				{
					$remoteFile = "$remoteFolder/".basename($localfile);
				}
				else
				{
					$remoteFile = basename($localfile);
				}
					
				$this->plugin->Logger()->info("Sending $localfile ($remoteFile).");
				$this->putRetrying($localfile, $remoteFile);
				$this->plugin->Logger()->info("Sent $localfile ($remoteFile).");
			}
		}
		
		/**
		 * Get a file to the server
		 *
		 * @param string $remote remote file path
		 * @param string $local local file path
		 * @throws TransferException
		 */
		public function fetch($remote, $local)
		{
			throw new TransferException(TransferException::UNSUPPORTED_METHOD, 'The %s engine does not implement the fetch method.', get_class($this));
		}
		
		/**
		 * Delete a file/directory from the server. A non empty directory will not be deleted unless the $recursive flag is raised.
		 * 
		 * @param string $remote remote file/directory path
		 * @param boolean $recursive [optional] whether to propagate the deletion case of a directory. Defaults to false.
		 * @throws TransferException
		 */		
		public function delete($remote, $recursive = false)
		{
			throw new TransferException(TransferException::UNSUPPORTED_METHOD, 'The %s engine does not implement the delete method.', get_class($this));
		}
		
		/**
		 * Rename/move a file/directory
		 * 
		 * @param string $remotePath the file/directory to rename
		 * @param string $newRemotePath the new name
		 * @throws TransferException
		 */
		public function rename($remotePath, $newRemotePath)
		{
			throw new TransferException(TransferException::UNSUPPORTED_METHOD, 'The %s engine does not implement the rename method.', get_class($this));
		}
		
		/**
		 * Changes the permissions on a file/directory
		 * 
		 * @param string $path the file/directory whose permissions need to be set
		 * @param string $permissions octal 3bit notation for the permissions
		 * @param boolean $recursive whether to apply the permissions on the children in case of a directory. Defaults to false
		 * @throws TransferException
		 */
		public function chmod($remote, $permissions, $recursive = false)
		{
			throw new TransferException(TransferException::UNSUPPORTED_METHOD, 'The %s engine does not implement the chmod method.', get_class($this));
		}
		
		/**
		 * Retrieves a directory listing.
		 * 
		 * @param string $remote the directory to list
		 * @throws TransferException
		 */
		public function dirlist($remote)
		{
			throw new TransferException(TransferException::UNSUPPORTED_METHOD, 'The %s engine does not implement the dirlist method.', get_class($this));
		}
		
		/**
		 * Returns TRUE if the file exists.
		 * SCP Doesn't implement fileExists.
		 * @param string $remote
		 * @throws TransferException
		 */
		public function fileExists($remote)
		{
			throw new TransferException(TransferException::UNSUPPORTED_METHOD, 'The %s engine does not implement the fileExists method.', get_class($this));
		}
		
		/**
		 * Close any active connection.
		 *
		 * @throws TransferException
		 */
		public function close()
		{
			throw new TransferException(TransferException::UNSUPPORTED_METHOD, 'The %s engine does not implement the close method.', get_class($this));
		}
		
		public static function doNothingErrorHandler($no,$str,$file,$line) 
		{
			// do nothing
		}
	}
}