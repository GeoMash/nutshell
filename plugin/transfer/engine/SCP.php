<?php
namespace nutshell\plugin\transfer\engine
{
	use nutshell\plugin\transfer\exception\TransferException;
	
	class SCP extends SSH
	{
		public function put($local, $remote)
		{
			//make sure we are connected
			$this->connect();
				
			$this->plugin->Logger('nutshell.plugin.transfer.scp')->info(sprintf('Uploading to remote file %s...', $remote));
				
			if(is_resource($local))
			{
				throw new TransferException(TransferException::SCP_NOT_IMPLEMENTED, sprintf('SCP of file handle has not been implemented.', $remote));
			}
			else if(is_string($local))
			{
				//upload using file name.
				$this->plugin->Logger('nutshell.plugin.transfer.scp')->debug('Uploading using local file path.');
		
				//check for file existence
				if(!is_readable($local))
				{
					throw new TransferException(TransferException::FILE_NOT_FOUND, sprintf('File %s not be found or not accessible', $local));
				}
		
				//transfers the file
				if(!ssh2_scp_send($this->connection, $local, $remote, 0644))
				{
					throw new TransferException(TransferException::SEND_FAILED, sprintf('Transfer of file %s to %s failed', $local, $remote));
				}
			}
			else
			{
				throw new TransferException(TransferException::INVALID_PARAMETER, sprintf('Invalid parameter %s: expected string or file handle', parse_r($local, true)));
			}
				
			$this->plugin->Logger('nutshell.plugin.transfer.scp')->info(sprintf('File transfered to %s successfully.', $remote));
		}
	}
}