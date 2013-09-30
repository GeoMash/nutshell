<?php
namespace nutshell\plugin\transfer\engine
{
	use nutshell\plugin\transfer\exception\TransferException;
	/**
	 * FTP - SSL Class
	 */
	class FTPS extends FTP
	{
		protected function initConnect()
		{
			//different connection options based on the existence of the port parameter
			if(!is_null($this->port))
			{
				$this->plugin->Logger('nutshell.plugin.transfer.ftps')->info(sprintf('Opening FTP SSL connection to %s on port %d...', $this->host, $this->port ));
				$connection = @ftp_ssl_connect($this->host, $this->port);
			}
			else
			{
				$this->plugin->Logger('nutshell.plugin.transfer.ftps')->info(sprintf('Opening FTP SSL connection to %s...', $this->host));
				$connection = @ftp_ssl_connect($this->host);
			}
				
			//test the connection status
			if($connection === false)
			{
				throw new TransferException(TransferException::FTP_CONNECTION_FAILED, sprintf('Could not connect to the FTP SSL server on host %s', $this->host));
			}
				
			$this->plugin->Logger('nutshell.plugin.transfer.ftps')->debug('Connection succeeded.');
				
			return $connection;
		}
	}
}