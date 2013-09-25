<?php
namespace nutshell\plugin\transfer\engine
{
	use nutshell\plugin\transfer\exception\TransferException;

	class FTP extends Base
	{
		const TRANSFER_MODE_ACTIVE = 'active';
		
		const TRANSFER_MODE_PASSIVE = 'passive';
		
		protected $connection = null;
		
		protected $user;
		
		protected $password;
		
		/**
		 * The file transfer mode (active/passive). Defaults to passive mode.
		 * @var string the transfer mode
		 */
		protected $mode = self::TRANSFER_MODE_PASSIVE;
		
		/**
		 * This method sets the transfer mode.
		 * @param string $str
		 */
		public function setMode($str)
		{
			$this->mode = $str;
		}
		
		protected function connect()
		{
			if(is_null($this->connection))
			{
				$connection = $this->initConnect();
				$this->authenticate($connection);
				$this->configureMode($connection);
				
				//we're all set
				$this->connection = $connection;
			}
			
			return $this->connection;
		}
		
		/**
		 * 
		 * Enter description here ...
		 * @throws TransferException
		 */
		protected function initConnect()
		{
			//different connection options based on the existence of the port parameter
			if(!is_null($this->port))
			{
				$this->plugin->Logger('nutshell.plugin.transfer.ftp')->info(sprintf('Opening FTP connection to %s on port %d...', $this->host, $this->port ));
				$connection = @ftp_connect($this->host, $this->port);
			}
			else
			{
				$this->plugin->Logger('nutshell.plugin.transfer.ftp')->info(sprintf('Opening FTP connection to %s...', $this->host));
				$connection = @ftp_connect($this->host);
			}
			
			//test the connection status
			if($connection === false)
			{
				throw new TransferException(TransferException::FTP_CONNECTION_FAILED, sprintf('Could not connect to the FTP server on host %s', $this->host));
			}
			
			$this->plugin->Logger('nutshell.plugin.transfer.ftp')->debug('Connection succeeded.');
			
			return $connection;
		}
		
		/**
		 * 
		 * Enter description here ...
		 * @param unknown_type $connection
		 * @throws TransferException
		 */
		protected function authenticate($connection)
		{
			$this->plugin->Logger('nutshell.plugin.transfer.ftp')->info(sprintf('Authenticating on host %s as user %s...', $this->host, $this->user));
			
			if(!@ftp_login($connection, $this->user, $this->password)) {
				throw new TransferException(TransferException::FTP_CONNECTION_FAILED, sprintf('Could not connect to the FTP server on host %s as user %s', $this->host, $this->user));
			}
			
			$this->plugin->Logger('nutshell.plugin.transfer.ftp')->debug('Authentication succeeded.');
		}
		
		/**
		 * 
		 * Enter description here ...
		 * @param unknown_type $connection
		 */
		protected function configureMode($connection)
		{
			$this->plugin->Logger('nutshell.plugin.transfer.ftp')->info(sprintf('Setting connection mode as %s...', $this->mode));
			
			if(!in_array($this->mode, array(self::TRANSFER_MODE_ACTIVE, self::TRANSFER_MODE_PASSIVE)))
			{
				throw new TransferException(TransferException::UNKNOWN_CONNECTION_MODE, sprintf('Unknown connection mode %s', $this->mode));
			}
			
			if(!@ftp_pasv($connection, $this->mode == self::TRANSFER_MODE_PASSIVE))
			{
				throw new TransferException(TransferException::CANNOT_SET_CONNECTION_MODE, sprintf('Failed to set the connection mode to %s', $this->mode));
			}
			
			$this->plugin->Logger('nutshell.plugin.transfer.ftp')->debug('Connection mode configured.');
		}
		
		/*
		 * *********************************** PUBLIC API ***********************************
		 */
		
		public function put($local, $remote)
		{
			//make sure we are connected
			$this->connect();
			
			$this->plugin->Logger('nutshell.plugin.transfer.ftp')->info(sprintf('Uploading to remote file %s...', $remote));
			
			if(is_resource($local))
			{
				//upload with file handle
				$this->plugin->Logger('nutshell.plugin.transfer.ftp')->debug('Uploading using file handle.');
				
				//transfers the file in binary mode to avoid ASCII mode corruption
				if(!@ftp_fput($this->connection, $remote, $local, FTP_BINARY))
				{
					throw new TransferException(TransferException::SEND_FAILED, sprintf('Transfer of file handle to %s failed', $remote));
				}
			}
			else if(is_string($local))
			{
				//upload using file name.
				$this->plugin->Logger('nutshell.plugin.transfer.ftp')->debug('Uploading using local file path.');
				
				//check for file existence
				if(!is_readable($local))
				{
					throw new TransferException(TransferException::FILE_NOT_FOUND, sprintf('File %s not be found or not accessible', $local));
				}
				
				//transfers the file in binary mode to avoid ASCII mode corruption
				if(!@ftp_put($this->connection, $remote, $local, FTP_BINARY))
				{
					throw new TransferException(TransferException::SEND_FAILED, sprintf('Transfer of file %s to %s failed', $local, $remote));
				}
			}
			else
			{
				throw new TransferException(TransferException::INVALID_PARAMETER, sprintf('Invalid parameter %s: expected string or file handle', parse_r($local, true)));
			}
			
			$this->plugin->Logger('nutshell.plugin.transfer.ftp')->info(sprintf('File transfered to %s successfully.', $remote));
		}
		
		public function fetch($remote, $local)
		{
			//make sure we are connected
			$this->connect();
			
			$this->plugin->Logger('nutshell.plugin.transfer.ftp')->info(sprintf('Fetching remote file %s...', $remote));
			
			if(is_resource($local))
			{
				//upload with file handle
				$this->plugin->Logger('nutshell.plugin.transfer.ftp')->debug('Fetching using file handle.');
			
				//transfers the file in binary mode to avoid ASCII mode corruption
				if(!@ftp_fget($this->connection, $local, $remote, FTP_BINARY))
				{
					throw new TransferException(TransferException::SEND_FAILED, sprintf('Transfer of  %s to file handle failed', $remote));
				}
			}
			else if(is_string($local))
			{
				//upload using file name.
				$this->plugin->Logger('nutshell.plugin.transfer.ftp')->debug('Fetching using local file path.');
			
				//check for file existence
				$dir = dirname($local);
				if(is_file($local) && is_writable($local) || is_dir($dir) && is_writable($dir))
				{
					//transfers the file in binary mode to avoid ASCII mode corruption
					if(!@ftp_get($this->connection, $local, $remote, FTP_BINARY))
					{
						throw new TransferException(TransferException::SEND_FAILED, sprintf('Transfer of file %s to %s failed', $local, $remote));
					}
				}
				else 
				{
					throw new TransferException(TransferException::FILE_NOT_WRITABLE, sprintf('File %s not be found or not writable', $local));
				}
			}
			else
			{
				throw new TransferException(TransferException::INVALID_PARAMETER, sprintf('Invalid parameter %s: expected string or file handle', parse_r($local, true)));
			}
			
			$this->plugin->Logger('nutshell.plugin.transfer.ftp')->info(sprintf('File %s fetched successfully.', $remote));
		}
		
		public function delete($remote, $recursive = false)
		{
			//make sure we are connected
			$this->connect();
			
			$this->plugin->Logger('nutshell.plugin.transfer.ftp')->info(sprintf('Deleting remote file %s...', $remote));
			
			if($recursive)
			{
				throw new TransferException(TransferException::NOT_YET_IMPLEMENTED, 'Not yet implemented');
			}
			
			if(!@ftp_delete($this->connection, $remote))
			{
				throw new TransferException(TransferException::CANNOT_DELETE, sprintf('Deletion of file %s failed', $remote));
			}
			
			$this->plugin->Logger('nutshell.plugin.transfer.ftp')->info(sprintf('File %s deleted successfully.', $remote));
		}
		
		public function close()
		{
			$this->plugin->Logger('nutshell.plugin.transfer.ftp')->info('Closing connection...');
			
			if($this->connection)
			{
				ftp_close($this->connection);
				$this->connection = null;
			}
			
			$this->plugin->Logger('nutshell.plugin.transfer.ftp')->info('Connection closed.');
		}
		
		public function fileExists($remote)
		{
			//make sure we are connected
			$this->connect();
			
			$oldErrorHandler = set_error_handler('nutshell\plugin\transfer\engine\FTP::doNothingErrorHandler');
				
			try
			{
				$size = ftp_size ($this->connection, $remote);
				
				$size = (!isset($size)) ? -1 : (int) $size;
				
				$result = ($size >= 0);
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