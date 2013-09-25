<?php
/**
 * @package nutshell-plugin
 * @author Guillaume Bodi <guillaume@spinifexgroup.com>
 */
namespace nutshell\plugin\transfer
{
	use nutshell\behaviour\Singleton;

	use nutshell\core\plugin\Plugin;
	use nutshell\behaviour\Native;
	use nutshell\plugin\transfer\exception\TransferException;
	
	/**
	 * @package nutshell-plugin
	 * @author Guillaume Bodi <guillaume@spinifexgroup.com>
		Settings depend on protocol as follows:
		FTP: host, port (optional), user, password.
		FTPS: not yet coded.
		SCP: (same as SSH).
		SSH: host, port (optional), user, password, keyfile, pubkeyfile. You can use user/password OR keyfile/pubkeyfile.
		SFPT: (same as SSH).

		In most cases (most protocols), uploading and downloading a file is done as follows:
		->put($local, $remote)
		->fetch($remote, $local)
	 * <pre>
	 * In order to make SSH, SFTP and SCP to work, you have to install libssh2. In ubuntu, do these 3 steps: 
	   1) apt-get install libssh2-1 libssh2-1-dev libssh2-php 
	   2) pecl install ssh2 channel://pecl.php.net/ssh2-version 
	   3) /etc/init.d/apache2 restart
	   Note from Joao: in my Ubuntu 11.04 Natty Narwhal, above instructions for installing libssh2 version 0.11.3 work. You may need installing PECL before. 
	   
	   Usage example:
	        // creates an FTP object
 			$oFtp = $this->plugin->Transfer()->Ftp();

			// connection settings
			$aSettings = array
			(
				'host'     => '127.0.0.1',
				'port'     => '21',
				'user'     => 'sroot',
				'password' => 'mypassword'
			);
			
			// apply settings
			$oFtp->setOptions($aSettings);

			// local file name
			$local = "/var/tmp/blablebli";
			
			// remote file name
			$remote = "blablebli";
			
			// sends the file (connect if it's not connected).
			$oFtp->put($local, $remote);
			
			// closes the connection.
			$oFtp->close();
		</pre>
	 */
	class Transfer extends Plugin implements Native, Singleton
	{
		
		private static $engines = array(
			'ftp' => 'FTP',
			'ftps' => 'FTPS',
			'sftp' => 'SFTP',
			'scp' => 'SCP',
			'webdav' => 'WebDav',
			'web-dav' => 'WebDav',
		);
		
		public static function registerBehaviours()
		{
			
		}
		
		public function init()
		{
			
		}
		
		public function __call($engine, $args)
		{
			$params = isset($args[0]) ? $args[0] : null;
			return self::runFactory($engine, $params);
		}
		
		public static function runFactory($engine, $args = null)
		{
			$engine = strtolower($engine);
			if(isset(self::$engines[$engine]))
			{
				$className = __NAMESPACE__ . '\\engine\\' . self::$engines[$engine];
				if(class_exists($className))
				{
					return new $className($args);	
				}
			}
			throw new TransferException(TransferException::UNSUPPORTED_ENGINE, sprintf("Archiving engine not supported or misspelt: %s", $engine));
		}
	}
}
?>