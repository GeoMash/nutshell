<?php
namespace nutshell\plugin\transfer\engine
{
	use nutshell\plugin\transfer\exception\TransferException;

	class SSH extends Base
	{
		const AUTH_MODE_PWD = 'pwd';
		
		const AUTH_MODE_PUBKEY = 'pubkey';
		
		protected $connection = null;
		
		protected $user;
		
		protected $keyfile;
		
		protected $pubkeyfile;
		
		/**
		 * Password, for either login or public key based auth 
		 * 
		 * @var string
		 */
		protected $password; 
		
		protected $authmode = self::AUTH_MODE_PWD;
		
		protected function connect()
		{
			if(is_null($this->connection))
			{
				$connection = $this->initConnect();
				$this->authenticate($connection);
			
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
				$this->plugin->Logger('nutshell.plugin.transfer.ssh')->info(sprintf('Opening SSH connection to %s on port %d...', $this->host, $this->port));
				$connection = ssh2_connect($this->host, $this->port);
			}
			else
			{
				$this->plugin->Logger('nutshell.plugin.transfer.ssh')->info(sprintf('Opening SSH connection to %s...', $this->host));
				$connection = ssh2_connect($this->host);
			}
				
			//test the connection status
			if($connection === false)
			{
				throw new TransferException(TransferException::SSH_CANNOT_CONNECT, sprintf('Could not connect to the SSH server on host %s', $this->host));
			}
				
			$this->plugin->Logger('nutshell.plugin.transfer.ssh')->debug('Connection succeeded.');
				
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
			$this->authmode = strtolower($this->authmode);
			
			switch($this->authmode)
			{
				case self::AUTH_MODE_PUBKEY:
					$this->authenticatePubKey($connection);
					break;
				case self::AUTH_MODE_PWD:
					$this->authenticatePwd($connection);
					break;
				default:
					throw new TransferException(TransferException::UNKNOWN_AUTHENTIDATION_MODE, 'Unknown authentication mode %s', $this->authmode);
					break;
			}
		}
		
		protected function authenticatePubKey($connection)
		{
			$this->plugin->Logger('nutshell.plugin.transfer.ssh')->info(sprintf('Authenticating on host %s with key pair...', $this->host));
			
			if(!is_readable($this->pubkeyfile))
			{
				throw new TransferException(TransferException::PUBLIC_KEY_NOT_FOUND, sprintf('Public key file %s does not exist or is not readable', $this->pubkeyfile));
			}
			
			if(!is_readable($this->keyfile))
			{
				throw new TransferException(TransferException::PRIVATE_KEY_NOT_FOUND, sprintf('Private key file %s does not exist or is not readable', $this->keyfile));
			}
			
			if(!is_null($this->password))
			{
				//authenticate with password protected key
				if(!@ssh2_auth_pubkey_file($connection, $this->user, $this->pubkeyfile, $this->keyfile, $this->password)) {
					throw new TransferException(TransferException::SSH_CANNOT_CONNECT, sprintf('Could not connect to the SSH server on host %s as user %s', $this->host, $this->user));
				}
			}
			else
			{
				//authenticate with unprotected key
				if(!@ssh2_auth_pubkey_file($connection, $this->user, $this->pubkeyfile, $this->keyfile)) {
					throw new TransferException(TransferException::SSH_CANNOT_CONNECT, sprintf('Could not connect to the SSH server on host %s as user %s', $this->host, $this->user));
				}
			}
			
			$this->plugin->Logger('nutshell.plugin.transfer.ssh')->debug('Authentication succeeded.');
		}
		
		protected function authenticatePwd($connection)
		{
			$this->plugin->Logger('nutshell.plugin.transfer.ssh')->info(sprintf('Authenticating on host %s as user %s...', $this->host, $this->user));
			
			if(!@ssh2_auth_password($connection, $this->user, $this->password)) {
				throw new TransferException(TransferException::SSH_CANNOT_CONNECT, sprintf('Could not connect to the SSH server on host %s as user %s', $this->host, $this->user));
			}
			
			$this->plugin->Logger('nutshell.plugin.transfer.ssh')->debug('Authentication succeeded.');
		}
		
		public function close()
		{
			$this->plugin->Logger('nutshell.plugin.transfer.ssh')->info('Closing connection...');
			
			if($this->connection)
			{
				//there is no ssh2 close function, so just reset the connection and hope it gets collected
				$this->connection = null;
			}
			
			$this->plugin->Logger('nutshell.plugin.transfer.ssh')->info('Connection closed.');
		}
	}
}