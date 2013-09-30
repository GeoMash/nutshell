<?php
namespace nutshell\plugin\transfer\engine
{
	use nutshell\plugin\transfer\exception\TransferException;
	
	class SFTP extends SSH
	{
		protected $sftpHandle = null;
		
		/**
		 * Transfer block size: 4KB.
		 * @var integer size of the transfer blocks for read/write action
		 */
		const TRANSFER_CHUNK = 4096;
		
		protected function connect()
		{
			$connection = parent::connect();
			
			if(is_null($this->sftpHandle)){
				$this->initSFTPHandle();
			}
			
			return $connection;
		}
		
		protected function initSFTPHandle()
		{
			if(!$this->sftpHandle = ssh2_sftp($this->connection))
			{
				throw new TransferException(TransferException::CANNOT_START_CONTEXT, 'Unable to start a SFTP context');
			}
		}
		
		public function put($local, $remote)
		{
			//make sure we are connected
			$this->connect();
			
			$this->plugin->Logger('nutshell.plugin.transfer.sftp')->debug(sprintf('Uploading to remote file %s...', $remote));
			
			//holder for the local file handle
			$flocal = null;
			$localClose = true;
			
			if(is_string($local))
			{
				//upload using file name.
				$this->plugin->Logger('nutshell.plugin.transfer.sftp')->debug('Uploading using local file path.');
				
				//check that the file is readable
				if(!is_readable($local))
				{
					throw new TransferException(TransferException::FILE_NOT_FOUND, sprintf('File %s cannot be found of is not readable', $local));
				}
				
				//create the local handle
				if(!$flocal = fopen($local, 'rb'))
				{
					throw new TransferException(TransferException::CANNOT_READ_FILE, sprintf('Failed to open local file %s in reading', $local));
				}
			}
			else if (is_resource($local))
			{
				//upload with file handle
				$this->plugin->Logger('nutshell.plugin.transfer.sftp')->debug('Uploading using file handle.');
				
				//nothing to do, just set the handle
				$flocal = $local;
				
				//don't close the handle if it was provided as an argument
				$localClose = false; 
			}
			else
			{
				throw new TransferException(TransferException::INVALID_PARAMETER, sprintf('Invalid parameter %s: expected string or file handle', parse_r($local, true)));
			}
			
			//open the remote file descriptor
			$target = sprintf("ssh2.sftp://%s%s", $this->sftpHandle, $remote);
			
			if($fremote = fopen($target, 'wb'))
			{
				$written = 0;
				
				//write chunks of data to the remote system
				while($data = fread($flocal, self::TRANSFER_CHUNK)) {
					$written += fwrite($fremote, $data);
				}
				
				$this->plugin->Logger('nutshell.plugin.transfer.sftp')->debug(sprintf('Transfered %dB', $written));
				
				//close the remote handle
				fclose($fremote);
				if($localClose)
				{
					//close the local handle
					fclose($flocal);
				}
			}
			else
			{
				throw new TransferException(TransferException::FILE_NOT_WRITABLE, sprintf('Failed to open remote file %s in writing', $remote));
			}
			
			$this->plugin->Logger('nutshell.plugin.transfer.sftp')->debug(sprintf('File transfered to %s successfully.', $remote));
		}
		
		public function fetch($remote, $local)
		{
			//make sure we are connected
			$this->connect();
			
			$this->plugin->Logger('nutshell.plugin.transfer.sftp')->debug(sprintf('Fetching remote file %s...', $remote));
			
			//holder for the local file handle
			$flocal = null;
			$localClose = true;
			
			if(is_string($local))
			{
				//upload using file name.
				$this->plugin->Logger('nutshell.plugin.transfer.sftp')->debug('Fetching using local file path.');
				
				//check that the file is readable
				$dir = dirname($local);
				if(!(is_writable($local) || is_dir($dir) && is_writable($dir)))
				{
					throw new TransferException(TransferException::FILE_NOT_WRITABLE, sprintf('File %s cannot be created or is not writable', $local));
				}
				
				//create the local handle
				if(!$flocal = fopen($local, 'wb'))
				{
					throw new TransferException(TransferException::FILE_NOT_WRITABLE, sprintf('Failed to open local file %s in writing', $local));
				}
			}
			else if (is_resource($local))
			{
				//upload with file handle
				$this->plugin->Logger('nutshell.plugin.transfer.sftp')->debug('Fetching using file handle.');
				
				//nothing to do, just set the handle
				$flocal = $local;
				
				//don't close the handle if it was provided as an argument
				$localClose = false; 
			}
			else
			{
				throw new TransferException(TransferException::INVALID_PARAMETER, sprintf('Invalid parameter %s: expected string or file handle', parse_r($local, true)));
			}
			
			//open the remote file descriptor
			$target = sprintf("ssh2.sftp://%s%s", $this->sftpHandle, $remote);
			
			if($fremote = fopen($target, 'rb'))
			{
				$written = 0;
				
				//write chunks of data to the remote system
				while($data = fread($fremote, self::TRANSFER_CHUNK)) {
					$written += fwrite($flocal, $data);
				}
				
				$this->plugin->Logger('nutshell.plugin.transfer.sftp')->debug(sprintf('Transfered %dB', $written));
				
				//close the remote handle
				fclose($fremote);
				if($localClose)
				{
					//close the local handle
					fclose($flocal);
				}
			}
			else
			{
				throw new TransferException(TransferException::FILE_NOT_FOUND, sprintf('Failed to open remote file %s in reading', $remote));
			}
			
			$this->plugin->Logger('nutshell.plugin.transfer.sftp')->debug(sprintf('File transfered from %s successfully.', $remote));
		}
		
		public function delete($remote, $recursive = false)
		{
			//make sure we are connected
			$this->connect();
			
			$this->plugin->Logger('nutshell.plugin.transfer.sftp')->debug(sprintf('Deleting remote file %s...', $remote));
			
			if($recursive)
			{
				throw new TransferException(TransferException::NOT_YET_IMPLEMENTED, 'Not yet implemented');
			}
			
			if(!ssh2_sftp_unlink($this->sftpHandle, $remote))
			{
				throw new TransferException(TransferException::CANNOT_DELETE, sprintf('Deletion of file %s failed', $remote));
			}
			
			$this->plugin->Logger('nutshell.plugin.transfer.sftp')->debug(sprintf('File %s deleted successfully.', $remote));
		}
		
		public function fileExists($remote)
		{
			//make sure we are connected
			$this->connect();
			
			$target = sprintf("ssh2.sftp://%s%s", $this->sftpHandle, $remote);

			$oldErrorHandler = set_error_handler('nutshell\plugin\transfer\engine\SFTP::doNothingErrorHandler');
			
			try
			{
				$handle = fopen($target, 'rb');
				$result = ($handle) ? true : false; 
				unset ($handle);
			}
			catch (\Exception $e) 
			{
				$result = $false;
			}
			
			set_error_handler($oldErrorHandler);
			
			return $result;
		}
	}
}